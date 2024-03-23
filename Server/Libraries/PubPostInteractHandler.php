<?php
/**
 *  @ SiGool
 *  2024/02/25
 */

namespace Gzh2typechoServer\Libraries;

use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use EasyWeChat\Kernel\Messages\Image;
use function EasyWeChat\Kernel\Support\str_random;

if (!defined('DEV_MODE'))
    exit;

/**
 * 微信公众号对话发布typecho文章
 * Class PubPostInteractHandler
 * @package Gzh2typechoServer\Libraries
 */
class PubPostInteractHandler implements EventHandlerInterface
{
    /**
     * 所有设置操作代号
     */
    const SET_TITLE_ACTION = 0;
    const SET_CONTENT_ACTION = 1;
    const SET_PUB_DATE_ACTION = 2;
    const SET_CATEGEORY_ACTION = 3;
    const SET_LABEL_ACTION = 4;
    const SET_SECRET_LV_ACTION = 5;
    const SET_PRIV_CTRL_ACTION = 6;
    const SET_USING_NOTICE_ACTION = 7;
    const NOT_ACTION = -1;

    private $actionContentCacheKeys = [
        self::SET_TITLE_ACTION => 'title',
        self::SET_CONTENT_ACTION => 'content',
        self::SET_PUB_DATE_ACTION => 'pubDate',
        self::SET_CATEGEORY_ACTION => 'categeory',
        self::SET_LABEL_ACTION => 'label',
        self::SET_SECRET_LV_ACTION => 'secretLv',
        self::SET_PRIV_CTRL_ACTION => 'privCtrl',
        self::SET_USING_NOTICE_ACTION => 'usingNotice'
    ];

    const DRAFT_STORE_SECONDS = 2 * 60 * 60;

    const SECRET_LV_OPTIONS = ['公开', '隐藏', '私密', '待审核'];

    const PWD_FORMAT_PATTERN = "/\w{6-18}/";

    const PRIV_CTRL_OPTIONS = ['0,0,0', '0,0,1', '0,1,0', '0,1,1', '1,0,0', '1,0,1', '1,1,0', '1,1,1'];

    const MEDIA_CONTENT_REPLACE_WHO = 'MEDIA_URL_REPLACE_FLAG';

    const PUB_LINK_VALID_SECONDS = 10 * 60;

    const TOKEN_QY_STR_NAME_ON_PUB_LINK = 'token';

    const WX_TEXT_EOL = "\n";

    /**
     * @var string
     * 该次请求的用户（openid）
     */
    private $usr;

    /**
     * @var int
     * 当前的设置操作代号，如果不是设置操作就为-1
     */
    private $action;

    private $imgContentTpl;

    private $videoContentTpl;

    public function handle($payload = null)
    {
        // 改次请求的用户（openid）
        $this->usr = $payload->FromUserName;

        // 获取当前的设置操作代号，如果不是设置操作则为-1
        $this->action = $this->action();

        // 处理
        switch ($payload->MsgType) {
            case 'text':
                // 发来文字
                return $this->textHandle($payload->Content);
            case 'image':
                // 发来图片
                return $this->imageHandle($payload->MediaId, $payload->PicUrl);
            case 'video':
                // 发来视频
                return $this->videoHandle($payload->MediaId, $payload->ThumbMediaId);
            default:
                // 发来短视频
                return $this->shortvideoHandle($payload->MediaId, $payload->ThumbMediaId);
        }
    }

    private function action(int $act = null)
    {
        $action = \App::$cache->getItem($this->usr . '.action');

        if ($act === null)
            // 获取设置操作代号，如果不是设置操作就返回-1
            return $action->isHit() ? $action->get() : self::NOT_ACTION;

        // 缓存设置操作代号
        $action->set($act);
        \App::$cache->save($action);
    }

    // 文字处理
    private function textHandle($content)
    {
        switch ($content) {

            case \App::getRawCfg('getMenuKeyWord'):
                // 获取菜单图片
                $menuSrcMid = \App::$cache->getItem(MenuSrcHandler::MENU_SRC_MID_CACHE_KEY);
                return $this->recAction(self::NOT_ACTION, $menuSrcMid->isHit() ? (new Image($menuSrcMid->get())) : '未设置菜单图片~', false);
            case \App::getRawCfg('newArticleKeyWord'):
                // 新建文章
                return $this->recAction(self::NOT_ACTION, $this->createNewPost());
            case \App::getRawCfg('getPreviewKeyWord'):
                // 预览
                return $this->recAction(self::NOT_ACTION, $this->previewPost());
            case \App::getRawCfg('PubPostKeyWord'):
                // 发布文章
                return $this->recAction(self::NOT_ACTION, $this->pubPost());
            /**
             * 进入设置xxx操作内容监听
             */
            case \App::getRawCfg('setTitleKeyWord'):
                // 设置标题
                return $this->recAction(self::SET_TITLE_ACTION, '格式：文字/[null]，[null]是重置标题为空，请输入~');
            case \App::getRawCfg('setContentKeyWord'):
                // 设置内容
                return $this->recAction(self::SET_CONTENT_ACTION, '格式：文字/图片/视频/[null]，[null]是重置内容为空，请输入~');
            case \App::getRawCfg('setPubDateKeyWord'):
                // 设发布日期
                return $this->recAction(self::SET_PUB_DATE_ACTION, '格式：xxxx-xx-xx xx:xx/[null]，[null]是默认值为发布那刻的日期，请输入~');
            case \App::getRawCfg('setCategeoryKeyWord'):
                // 设分类
                if (trim(\App::getRawCfg('categeoryOptions')) === '')
                    return '还未有任何分类，请先到后台添加分类！';
                return $this->recAction(self::SET_CATEGEORY_ACTION, '格式：一行一个分类名/[null]，[null]是重置分类为不选择任何分类，已有分类：' . \App::getRawCfg('categeoryOptions') . '，请输入~');
            case \App::getRawCfg('setLabelKeyWord'):
                // 设标签
                return $this->recAction(self::SET_LABEL_ACTION, '格式：一行一个标签名/[null]，[null]是重置标签为没有标任何标签，请输入~');
            case \App::getRawCfg('setSecretLvKeyWord'):
                // 设公开度
                return $this->recAction(self::SET_SECRET_LV_ACTION, '格式：公开/隐藏/密码保护/私密/待审核/[null]，如果是要密码保护，就直接是对应想要的密码(由 数字/字母/_ 构成，共6-18位)，[null]则重置公开度为设定的默认值(' . $this->getDefaultSecretLv() . ')，请输入~');
            case \App::getRawCfg('setPrivCtrlKeyWord'):
                // 设权限控制
                return $this->recAction(self::SET_PRIV_CTRL_ACTION, '格式：[null]/允许评论则1否则0,允许被引用则1否则0,允许在聚合中出现则1否则0，如：0,1,0 就代表 不允许评论和允许被引用和不允许在聚合中出现，[null]则重置权限控制为设定的默认值(' . $this->getDefaultPrivCtrl() . ')，请输入~');
            case \App::getRawCfg('setUsingNoticeKeyWord'):
                // 设引用通告
                return $this->recAction(self::SET_USING_NOTICE_ACTION, '格式：文字/[null]，[null]是重置引用通告为空，请输入~');
            default:
                // 确保是设置xxx操作内容
                return $this->action === self::NOT_ACTION ? null : $this->setActionContent($content);
        }
    }

    private function mediaBeforeHandle($mediaId)
    {
        if ($this->action === self::NOT_ACTION)
            // 不是设置xxx操作内容  后面结束但不回复啥
            return '';

        if ($this->action !== self::SET_CONTENT_ACTION)
            // 是设置xxx操作内容，但不是设置content操作内容 后面结束回复错误提示
            return '输入格式有误！';

        // 准备 图片或视频 内容模板
        $this->imgContentTpl = \App::getRawCfg('imgContentTpl');
        strpos($this->imgContentTpl, self::MEDIA_CONTENT_REPLACE_WHO) === false
            && $this->imgContentTpl = '![](' . self::MEDIA_CONTENT_REPLACE_WHO . ')';

        $this->videoContentTpl = \App::getRawCfg('videoContentTpl');
        strpos($this->videoContentTpl, self::MEDIA_CONTENT_REPLACE_WHO) === false
            && $this->videoContentTpl = '<video src="' . self::MEDIA_CONTENT_REPLACE_WHO . '" controls></video>';

        // 缓存内容MediaID
        $this->mediaContentUsingMediaIDs($mediaId);
        return true;
    }

    // 获取 或 设置
    // 图片或视频文章内容使用的 mediaID
    private function mediaContentUsingMediaIDs() {
        $mIds = \App::$cache->getItem($this->usr . '.draft_content.media_ids');
        $list = $mIds->isHit()? $mIds->get() : [];
        if (func_num_args() === 0)
            // 获取
            return $list;

        // 设置
        if (func_get_arg(0) === false)
            $list = []; // 清空
        else
            $list[] = func_get_arg(0); // 追加

        $mIds->set($list);
        \App::$cache->save($mIds);
    }

    // 图片处理
    private function imageHandle($mId, $imgUrl)
    {
        if (is_string($prepared = $this->mediaBeforeHandle($mId)))
            return $prepared;

        return $this->setActionContent(str_replace(self::MEDIA_CONTENT_REPLACE_WHO, $mId, $this->imgContentTpl));
    }

    // 视频处理
    private function videoHandle($mId, $tMId)
    {
        if (is_string($prepared = $this->mediaBeforeHandle($mId)))
            return $prepared;

        return $this->setActionContent(str_replace(self::MEDIA_CONTENT_REPLACE_WHO, $mId, $this->videoContentTpl));
    }

    // 短视频处理
    private function shortvideoHandle($mId, $tMId)
    {
        return $this->videoHandle($mId, $tMId);
    }

    private function createNewPost()
    {
        $draft = \App::$cache->getItem($this->usr . '.draft');
        $draft->set([
            $this->actionContentCacheKeys[self::SET_TITLE_ACTION] => '',
            $this->actionContentCacheKeys[self::SET_CONTENT_ACTION] => '',
            $this->actionContentCacheKeys[self::SET_PUB_DATE_ACTION] => '[null]',
            $this->actionContentCacheKeys[self::SET_CATEGEORY_ACTION] => '',
            $this->actionContentCacheKeys[self::SET_LABEL_ACTION] => '',
            $this->actionContentCacheKeys[self::SET_SECRET_LV_ACTION] => '[null]',
            $this->actionContentCacheKeys[self::SET_PRIV_CTRL_ACTION] => '[null]',
            $this->actionContentCacheKeys[self::SET_USING_NOTICE_ACTION] => ''
        ]);
        $draft->expiresAfter(self::DRAFT_STORE_SECONDS);
        \App::$cache->save($draft);
        $this->mediaContentUsingMediaIDs(false); // 文章内容 引用到的媒体文件记录 要重置为空
        return '文章已新建，请尽快完成发布~';
    }

    private function getPost($item = false)
    {
        $post = \App::$cache->getItem($this->usr . '.draft');
        if (!$post->isHit())
            return -1;
        return $item === false ? $post : ($post->get()[$item]);
    }

    private function previewPost()
    {
        if (is_int($d = $this->getPost()))
            return '请先新建文章!';

        $draft = $d->get();
        $draft[$this->actionContentCacheKeys[self::SET_PUB_DATE_ACTION]] === '[null]'
            && $draft[$this->actionContentCacheKeys[self::SET_PUB_DATE_ACTION]] .= '(发布那刻的日期)';

        $draft[$this->actionContentCacheKeys[self::SET_SECRET_LV_ACTION]] === '[null]'
            && $draft[$this->actionContentCacheKeys[self::SET_SECRET_LV_ACTION]] .= '(' . $this->getDefaultSecretLv() . ')';

        $draft[$this->actionContentCacheKeys[self::SET_PRIV_CTRL_ACTION]] === '[null]'
            && $draft[$this->actionContentCacheKeys[self::SET_PRIV_CTRL_ACTION]] .= '(' . $this->getDefaultPrivCtrl() . ')';

        return <<<TXT
【文章标题】：
{$draft[$this->actionContentCacheKeys[self::SET_TITLE_ACTION]]}

【文章内容】：
{$draft[$this->actionContentCacheKeys[self::SET_CONTENT_ACTION]]}

【发布日期】：
{$draft[$this->actionContentCacheKeys[self::SET_PUB_DATE_ACTION]]}

【文章分类】：
{$draft[$this->actionContentCacheKeys[self::SET_CATEGEORY_ACTION]]}

【文章标签】：
{$draft[$this->actionContentCacheKeys[self::SET_LABEL_ACTION]]}

【文章公开度】：
{$draft[$this->actionContentCacheKeys[self::SET_SECRET_LV_ACTION]]}

【文章权限控制】：
{$draft[$this->actionContentCacheKeys[self::SET_PRIV_CTRL_ACTION]]}

【文章引用通告】：
{$draft[$this->actionContentCacheKeys[self::SET_USING_NOTICE_ACTION]]}
TXT;
    }

    private function updatePost($content)
    {
        if (is_int($d = $this->getPost()))
            return $d;
        $draft = $d->get();
        $draft[$this->actionContentCacheKeys[$this->action]] = $content;
        $d->set($draft);
        \App::$cache->save($d);
        return true;
    }

    private function pubPost()
    {
        if (is_int($d = $this->getPost()))
            return '请先新建文章!';

        $draft = $d->get();

        $draft[$this->actionContentCacheKeys[self::SET_PUB_DATE_ACTION]] === '[null]'
            && $draft[$this->actionContentCacheKeys[self::SET_PUB_DATE_ACTION]] = '';

        $draft[$this->actionContentCacheKeys[self::SET_SECRET_LV_ACTION]] === '[null]'
            && $draft[$this->actionContentCacheKeys[self::SET_SECRET_LV_ACTION]] = $this->getDefaultSecretLv();

        $draft[$this->actionContentCacheKeys[self::SET_PRIV_CTRL_ACTION]] === '[null]'
            && $draft[$this->actionContentCacheKeys[self::SET_PRIV_CTRL_ACTION]] = $this->getDefaultPrivCtrl();

        $mediaIDs = [];
        foreach ($this->mediaContentUsingMediaIDs() as $mID)
            $mediaIDs[$mID] = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=' . \App::$instance->access_token->getToken()['access_token'] . '&media_id=' . $mID;

        $draft['__extends'] = [
            'uid' => $this->usr,
            'mediaIDs' => $mediaIDs
        ];

        // 临时发布链接

        // 删除旧的发布token
        $pubToken = \App::$cache->getItem($this->usr . '.draft.pub_token');
        if ($pubToken->isHit())
            \App::$cache->deleteItem($pubToken->get());

        // 重新生成新的发布token
        $token = str_random(32);
        $pubToken->set($token);
        \App::$cache->save($pubToken);

        $t = \App::$cache->getItem($token);
        $t->set($draft);
        $t->expiresAfter(self::PUB_LINK_VALID_SECONDS);
        \App::$cache->save($t);

        // 返回临时链接
        $url = str_replace('\\', '/', str_replace(BASE_PATH, Helper::currentBaseUrl(), realpath(__DIR__ . '/../post.php')));
        $url .= '?' . self::TOKEN_QY_STR_NAME_ON_PUB_LINK . '=' . $token;

        return '临时发布地址：' . $url . ' ☜点击马上发布~';
    }

    private function recAction($action, $resp = '请输入~', $checkIfPostExist = true)
    {
        if ($checkIfPostExist && is_int($this->getPost())) {
            $this->action(self::NOT_ACTION);
            return '请先新建文章!';
        }
        $this->action($action); // 更新设置操作代号
        return $resp;
    }

    private function setActionContent($content)
    {
        switch ($this->action) { // 是什么设置操作的回复
            case self::SET_TITLE_ACTION: // 标题
                $content === '[null]'
                && $content = ''; // 重置为空
                break;
            case self::SET_CONTENT_ACTION: // 内容
                if ($content === '[null]') {
                    $content = ''; // 清空
                    $this->mediaContentUsingMediaIDs(false); // 使用的媒体文件（图片 或 视频）记录也清空
                } else {
                    $keepActionFlag = 1;
                    // 追加文章内容
                    $content = $this->getPost($this->actionContentCacheKeys[self::SET_CONTENT_ACTION]) . PHP_EOL . $content;
                }
                break;
            case self::SET_PUB_DATE_ACTION: // 发布日期
                // 发布日期
                if ($content !== '[null]' && (strtotime($content) === false || date('Y-m-d H:i', strtotime($content)) !== $content))
                    return '输入格式有误！';
                break;
            case self::SET_CATEGEORY_ACTION: // 分类
                if ($content === '[null]') {
                    $content = ''; // 重置为空
                    break;
                }

                if (($opts = trim(\App::getRawCfg('categeoryOptions'))) === '') {
                    $this->action(self::NOT_ACTION); // 自动退出设置分类操作内容
                    return '还未有任何分类，无法给文章设置分类！';
                } else
                    $opts = explode('/', $opts);

                foreach (explode(self::WX_TEXT_EOL, $content) as $ite) {
                    if (!in_array($ite, $opts, true))
                        // 存在不是已有分类
                        return '输入格式有误！';
                }
                break;
            case self::SET_LABEL_ACTION: // 标签
                $content === '[null]'
                    && $content = '';
                break;
            case self::SET_SECRET_LV_ACTION: // 公开度
                if ($content !== '[null]') {
                    if (!in_array($content, self::SECRET_LV_OPTIONS) && preg_match(self::PWD_FORMAT_PATTERN, $content) === false)
                        return '输入格式有误！';
                }
                break;
            case self::SET_PRIV_CTRL_ACTION: // 权限控制
                if ($content !== '[null]' && in_array($content, self::PRIV_CTRL_OPTIONS) === false)
                    return '输入格式有误！';
                break;
            case self::SET_USING_NOTICE_ACTION: // 引用通告
                $content === '[null]'
                    && $content = ''; // 重置为空
                break;
        }

        if (is_int($this->updatePost($content))) {
            $this->action(self::NOT_ACTION);
            return '请先新建文章!';
        }

        if (!isset($keepActionFlag))
            // 退出设置xxx操作内容
            $this->action(self::NOT_ACTION);

        return 'ok~';
    }

    private function getDefaultSecretLv()
    {
        return in_array($v = \App::getRawCfg('defaultSecretLv'), self::SECRET_LV_OPTIONS) ? $v : (
        preg_match(self::PWD_FORMAT_PATTERN, $v) ? $v : self::SECRET_LV_OPTIONS[0]
        );
    }

    private function getDefaultPrivCtrl()
    {
        return in_array($v = \App::getRawCfg('defaultPrivCtrl'), self::PRIV_CTRL_OPTIONS) ? $v : self::PRIV_CTRL_OPTIONS[0];
    }
}