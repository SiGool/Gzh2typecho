<?php
/**
 *  @ SiGool
 *  2024/02/27
 */
namespace Gzh2typechoServer\Libraries;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

if (!defined('DEV_MODE'))
    exit;

/**
 * 菜单源处理，当后台更新了菜单源后，需要重新上传到微信公众号素材库
 * Class MenuSrcHandler
 * @package Gzh2typechoServer\Libraries
 */
class MenuSrcHandler implements EventHandlerInterface
{
    const MENU_SRC_MID_CACHE_KEY = 'menu_src.mid';

    const MAX_HANDLE_TRY_TIMES = 3;

    // 获取 或
    // 设置handle方法的执行状态 （给handle方法加锁 避免handle还没执行完，其他请求又进入重复执行）
    private function handleStatus() {
        $status = \App::$cache->getItem('menu_src.handle_status');
        if (func_num_args() === 0)
            // 获取handle执行状态
            return $status->isHit()? $status->get() : 0;
        else {
            // 设置执行状态
            if (($stat = func_get_arg(0)) !== 0 && $stat !== 1)
                // 没有提供要设置的目标状态，就切换状态
                $stat = $status->isHit()? ($status->get() ^ 1) : 1;
            $status->set($stat);
            \App::$cache->save($status);
        }
    }

    // 获取 或 设置
    // 菜单图片处理已尝试的次数
    private function handleTryTimes($act) {

        $times = \App::$cache->getItem('menu_src.handle_try_times');

        if ($times->isHit()) {
            list($a, $t) = $times->get();
            if ($a !== $act) {
                $a = $act;
                $t = 0;
            }
        } else {
            $a = $act;
            $t = 0;
        }

        // 获取次数
        if (func_num_args() === 1)
            return $t;

        // 更新次数
        if (func_get_arg(1) === null)
            // 没有提供要设置的目标次数，就自动递增次数
            $t++;
        else
            // 提供了要设置的目标次数
            $t = func_get_arg(1);

        $times->set([$a, $t]);
        \App::$cache->save($times);
    }

    public function handle($payload = null) {

        // 避免重复执行改方法
        if ($this->handleStatus())
            return;

        // 加锁
        $this->handleStatus(1);

        try {
            // 处理
            $this->handleCore();
        } catch (\Exception $e) {
            \App::errorLog($e, '菜单图片处理异常');
        }

        // 解锁
        $this->handleStatus(0);
    }

    public function handleCore() {

        // 是否有菜单图片存在
        $exitMenuSrcN = null;
        foreach ((array)scandir($menuSrcDir = __DIR__ . '/../cache') as $n) {
            if (strpos($n, 'tmp-menu-src-') === 0) {
                $exitMenuSrcN = $n; // 存在菜单图片
                break;
            }
        }

        $menuSrcMid = \App::$cache->getItem(self::MENU_SRC_MID_CACHE_KEY);
        $menuSrcN = \App::$cache->getItem('menu_src.n');

        if ($exitMenuSrcN === null) { // 菜单图片不存在
            if ($menuSrcMid->isHit()) // 素材库中又有菜单图片了
                $act = 0; // 要删除素材库中的菜单图片
            else
                return;   // 素材库中也没有，什么都不用做
        } else {
            if ($menuSrcN->isHit()) {
                if ($menuSrcN->get() === $exitMenuSrcN)
                    return; // 素材库中也是这个菜单图片， 什么都不用做
                else {
                    // 修改菜单图片， 要上传到素材库中
                    $act = $exitMenuSrcN;

                    // 标记一会上传成功后要移除旧的菜单图片其在素材库中的mediaID
                    $menuSrcMid->isHit()
                        && $delMid = $menuSrcMid->get();
                }
            } else
                $act = $exitMenuSrcN; // 新增菜单图片，要上传到素材库中
        }

        // 超过了最大尝试次数
        if ($this->handleTryTimes($act) >= self::MAX_HANDLE_TRY_TIMES)
            return;

        // 尝试次数+1
        $this->handleTryTimes($act, null);
        if ($act === 0) {

            // 删除素材库中的图片
            try {
                \App::$instance->material
                            ->delete($menuSrcMid->get());
            } catch (\Exception $e) {
                \App::errorLog($e, '移除公众号素材库中的菜单图片异常');
            }

            // 删除记录缓存
            \App::$cache->deleteItems(['menu_src.n', self::MENU_SRC_MID_CACHE_KEY]);
        } else {

            // 上传到素材库中
            try {

                $mId = \App::$instance->material
                                    ->uploadImage($menuSrcDir . '/' . $exitMenuSrcN)
                                    ->media_id;
            } catch (\Exception $e) {
                \App::errorLog($e, '上传菜单图片到公众号素材库中异常');
                return;
            }

            // 更新记录缓存
            $menuSrcN->set($exitMenuSrcN);
            $menuSrcMid->set($mId);
            \App::$cache->save($menuSrcN);
            \App::$cache->save($menuSrcMid);

            if (isset($delMid)) { // 是修改，不是新增，还有旧的要从公众号素材库中删除
                try {
                    \App::$instance->material
                                ->delete($delMid);
                } catch (\Exception $e) {
                    \App::errorLog($e, '移除公众号素材库中的菜单图片异常');
                }
            }
        }
    }
}