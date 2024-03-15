<?php
/**
 *  @ SiGool
 *  2024/02/28
 */

namespace Gzh2typechoServer\Libraries;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

if (!defined('DEV_MODE'))
    exit;

/**
 * 用户消息处理中拦截器
 * Class WaitingHandler
 * @package Gzh2typechoServer\Libraries
 */
class WaitingHandler implements EventHandlerInterface
{
    public function handle($payload = null) {
        // 处理中，拦截
        if (($status = self::dealStatus($payload->FromUserName)) !== 0)
            return is_string($status)? $status : false;
    }

    // 进入用户消息处理中拦截模式
    public static function deal($usr, \Closure $do, string $tip = null) {
        // 标记正在处理中
        self::dealStatus($usr, $tip);
        try {
            return $do($usr);
        } catch (\Exception $e) {
            // 程序异常，必须解锁状态
            self::dealStatus($usr, 0);
            throw $e;
        }
    }

    // 获取 或
    // 设置处理状态
    public static function dealStatus($usr) {

        $status = \App::$cache->getItem($usr . '.wait');

        if (func_num_args() === 1)
            // 获取状态
            return $status->isHit()? $status->get() : 0;

        // 更新状态
        if (($stat = func_get_arg(1)) !== 0 && $stat !== 1 && !is_string($stat))
            // 没有提供要设置的目标状态就切换状态
            $stat = $status->isHit()? (is_string($status->get())? 0 : ($status->get() ^ 1)) : 1;

        $status->set($stat);
        \App::$cache->save($status);
    }
}