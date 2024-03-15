<?php
namespace TypechoPlugin\Gzh2typecho;
use Typecho\Common;
use Typecho\Cookie;
use Typecho\Db;
use Typecho\Plugin\PluginInterface;
use Typecho\Response;
use Typecho\Widget\Helper\Form;
use TypechoPlugin\Gzh2typecho\Libraries\TypechoWidgetHelperFormElement_Head;
use Widget\Metas\Category\Admin;

if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

/**
 * 微信公众号对话发布typecho文章
 * @package Gzh2typecho
 * @author SiGool
 * @version 1.0.0
 * @link https://lead2mind.com
 */
class Plugin implements PluginInterface
{
    /**
     * 用哪个后台账号发布文章
     */
    const PUBLISH_POST_BY_UID = 1;

    /**
     * 标题组件
     */
    const HEAD_COMPONENT = [
        'Gzh2typecho-head-commc',
        'Gzh2typecho-head-gzh'
    ];

    const CREDENTIALS_CACHE_KEY = 'usr.logged-in';

    const CREDENTIALS_CACHE_VALID_SECS = 2 * 60 * 60;

    private static $cache;

    public static function cache() {
        if (self::$cache === null)
            self::$cache = require __DIR__ . '/Server/cache.php';
        return self::$cache;
    }

    public static function config(Form $form)
    {
        $form->addInput(
            new TypechoWidgetHelperFormElement_Head(self::HEAD_COMPONENT[0], '对话发布', [
                'font-size' => '22px'
            ])
        );

        $form->addInput(new Form\Element\Textarea('commcAllowOpenid', null, '*', '生效用户', '填写用户对应公众号的openid，多个用,分割，都允许则填写*'));

        $form->addInput(
            (new Form\Element\Text('getMenuKeyWord', null, '@菜单|>', '获取菜单口令'))
                ->addRule('required', '获取菜单口令')
        );

        $form->addInput(new Form\Element\Text('menuSrc', null, '', '菜单来源', '一个有效的图片（bmp/png/jpeg/jpg 10M内）URL地址，发送获取菜单口令后公众号会自动回复该菜单图片'));

        $form->addInput(
            (new Form\Element\Text('newArticleKeyWord', null, '@新文章|>', '开篇口令'))
                ->addRule('required', '开篇口令不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('setTitleKeyWord', null, '@设标题|>', '设标题口令', '回复格式：文字/[null]，[null]是重置标题为空'))
                ->addRule('required', '设标题口令不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('setContentKeyWord', null, '@设内容|>', '设内容口令', '回复格式：文字/图片/视频/[null]，[null]是重置内容为空'))
                ->addRule('required', '设内容口令不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('imgContentTpl', null, '![](MEDIA_URL_REPLACE_FLAG)', '图片内容模板', '文章内容输入图片时，图片自动转换成Markdown代码使用的模板，模板中出现的“MEDIA_URL_REPLACE_FLAG”这串英文将被替换成图片的URL而且必须存在于模板中'))
                ->addRule('required', '图片内容模板不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('videoContentTpl', null, '<video src="MEDIA_URL_REPLACE_FLAG" controls></video>', '视频内容模板', '文章内容输入视频时，视频自动转换成Markdown代码使用的模板，模板中出现的“MEDIA_URL_REPLACE_FLAG”这串英文将被替换成视频的URL而且必须存在于模板中'))
                ->addRule('required', '图片内容模板不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('setPubDateKeyWord', null, '@设发布日期|>', '设发布日期口令', '回复格式：xxxx-xx-xx xx:xx/[null]，[null]是默认值为发布那刻的日期'))
                ->addRule('required', '设发布日期口令不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('setCategeoryKeyWord', null, '@设分类|>', '设分类口令', '回复格式：一行一个分类名/[null]，[null]是重置分类为不选择任何分类'))
                ->addRule('required', '设分类口令不能为空')
        );

        /**
         * @var Admin $categories
         */
        Admin::alloc()->to($categories);
        $ctgs = [];
        while($categories->next())
            $ctgs[] = $categories->name;
        $ctgs = implode('/', $ctgs);

        $form->addInput(
            new Form\Element\Text(
                'categeoryOptions', null, '', '现有分类',
                '输入分类口令后公众号会自动回复现有分类供参考。该处无需填写由程序自动获取【注：修改分类后，请到当前页面点击“保存设置”同步配置】' .
<<<SCRIPT
<script type="text/javascript">
let e = document.querySelector('input[name="categeoryOptions"]');
e.setAttribute('readonly', 'readonly');
e.value = '$ctgs';
</script>
SCRIPT
            )
        );

        $form->addInput(
            (new Form\Element\Text('setLabelKeyWord', null, '@设标签|>', '设标签口令', '回复格式：一行一个标签名/[null]，[null]是重置标签为没有标任何标签'))
                ->addRule('required', '设标签口令不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('setSecretLvKeyWord', null, '@设公开度|>', '设公开度口令', '回复格式：公开/隐藏/密码保护/私密/待审核/[null]，如果是要密码保护，就直接是对应想要的密码(由 数字/字母/_ 构成，共6-18位)，[null]则重置公开度为设定的默认值'))
                ->addRule('required', '设公开度口令不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('defaultSecretLv', null, '公开', '默认公开度', '可设定：公开/隐藏/密码保护/私密/待审核，如果是要密码保护，就直接是对应想要的密码(由 数字/字母/_ 构成，共6-18位)'))
                ->addRule('required', '默认公开度不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('setPrivCtrlKeyWord', null, '@设权限控制|>', '设权限控制口令', '回复格式：[null]/允许评论则1否则0,允许被引用则1否则0,允许在聚合中出现则1否则0，如：0,1,0 就代表 不允许评论和允许被引用和不允许在聚合中出现，[null]则重置权限控制为设定的默认值'))
                ->addRule('required', '设权限控制口令不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('defaultPrivCtrl', null, '1,1,1', '默认权限控制', '可设定：0,0,0/0,0,1/0,1,0/0,1,1/1,0,0/1,0,1/1,1,0/1,1,1'))
                ->addRule('required', '默认权限控制不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('setUsingNoticeKeyWord', null, '@设引用通告|>', '设引用通告口令', '回复格式：文字/[null]，[null]是重置引用通告为空'))
                ->addRule('required', '设引用通告口令不能为空')
        );


        $form->addInput(
            (new Form\Element\Text('getPreviewKeyWord', null, '@预览|>', '预览口令'))
                ->addRule('required', '预览口令不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('PubPostKeyWord', null, '@发布|>', '发布口令'))
                ->addRule('required', '发布口令不能为空')
        );

        $form->addInput(
            new TypechoWidgetHelperFormElement_Head(self::HEAD_COMPONENT[1], '公众号', [
                'font-size' => '22px'
            ])
        );

        $form->addInput(
            (new Form\Element\Text('mpDevAppID', null, '', '开发者ID'))
                ->addRule('required', '开发者ID不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('mpDevAppSecret', null, '', '开发者密码'))
                ->addRule('required', '开发者密码不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('mpDevToken', null, '', 'Token'))
                ->addRule('required', 'Token不能为空')
        );

        $form->addInput(
            (new Form\Element\Text('mpDevEncodingAESKey', null, '', 'EncodingAESKey'))
                ->addRule('required', 'EncodingAESKey不能为空')
        );

    }

    public static function configCheck($settings) {

        // 保存插件设置时写出文件
        foreach (self::HEAD_COMPONENT as $settingKey)
            unset($settings[$settingKey]);

        // 菜单图片处理
        !defined('MENU_SRC_ALLOW_EXT')
            && define('MENU_SRC_ALLOW_EXT', ['.jpeg', '.jpg', '.png', '.bmp']);

        !defined('MENU_SRC_ALLOW_MAX_SIZE')
            && define('MENU_SRC_ALLOW_MAX_SIZE', 10 * 1024 * 1024);

        !is_dir($tmpMenuSrcDir = __DIR__ . '/Server/cache')
            && mkdir($tmpMenuSrcDir);

        $oldMenuSrcN = null;
        foreach ((array)scandir($tmpMenuSrcDir) as $n) {
            if (strpos($n, $menuSrcNPrefix = 'tmp-menu-src-') === 0) {
                $oldMenuSrcN = $n; // 已设置有菜单图片了
                break;
            }
        }

        $menuSrc = trim($settings['menuSrc']);
        if ($menuSrc === '') { // 提交为空，之前已经设置有的就要移除
            if ($oldMenuSrcN !== null)
                // 之前设置有了，移除掉
                unlink($tmpMenuSrcDir . '/' . $oldMenuSrcN);
        } else {

            try {

                if (($menuSrcExt = '.' . pathinfo($menuSrc, PATHINFO_EXTENSION)) === '.') {
                    // url上没有后缀，我们要取真实的后缀
                    if (($menuSrcResHeader = get_headers($menuSrc, true)) === false)
                        throw new \Exception();
                    $menuSrcExt = '.' . explode('/', $menuSrcResHeader['Content-Type'])[1];
                }

                $menuSrcN = $menuSrcNPrefix . md5($menuSrc) . $menuSrcExt;
                if ($oldMenuSrcN === $menuSrcN)
                    // 比对，发现和现有图片一样，什么都不做
                    goto w;

                // 后缀和类型是否符合要求
                if (!in_array($menuSrcExt, MENU_SRC_ALLOW_EXT))
                    throw new \Exception();  // 不允许类型

                if (!isset($menuSrcResHeader)) {
                    if (($menuSrcResHeader = get_headers($menuSrc, true)) === false)
                        throw new \Exception();
                }

                if ($menuSrcResHeader['Content-Length'] > MENU_SRC_ALLOW_MAX_SIZE)
                    throw new \Exception(); // 超过允许最大体积

                if ($oldMenuSrcN !== null)
                    // 旧的存在，说明是修改，旧的要移除掉
                    unlink($tmpMenuSrcDir . '/' . $oldMenuSrcN);

                // 写出菜单图片
                file_put_contents($tmpMenuSrcDir . '/' . $menuSrcN, file_get_contents($menuSrc));

            } catch (\Exception $e) {}
        }

w:
        // 写出配置
        $settings = var_export($settings, true);
        file_put_contents(
            __DIR__ . '/Server/cfg.php',
<<<CONTENT
<?php
/**由SiGool创建的程序生成*/
return {$settings};
CONTENT
        );
    }

    public static function loginLoginFail($User, $name, $password, $remember) {
        // 登录表单提交的账号密码验证登录失败

        // 我们看看是否意图执行 本插件的功能（公众号发布文章）
        if ($name !== 'Gzh2typecho')
            return; // 不是本次意图标识

        if (self::cache()->getItem($password)->isHit() === false)
            return; // 不是公众号签发过来的口令

        // 获取发布用户
        function resp($err = 0, $msg = 'ok', $data = []) {
            Response::getInstance()->setContentType('application/json')
                                ->addResponder(function() use ($err, $msg, $data) {
                                    echo json_encode(compact('err', 'msg', 'data'));
                                })
                                ->respond();
        }

        $pubUser = Db::get()->fetchRow(Db::get()->select()->from('table.users')
                            ->where('uid = ?', self::PUBLISH_POST_BY_UID)
                            ->limit(1));
        if ($pubUser === null)
            resp(40, '发布文章的用户不存在！');

        // 存在发布用户就自动登录下发cookie
        Cookie::set('__typecho_uid', $pubUser['uid']);
        Cookie::set('__typecho_authCode', Common::hash($pubUser['authCode']));
        resp();
    }

    public static function activate()
    {
        \Typecho\Plugin::factory('Widget_Login')->loginFail = __CLASS__ . '::loginLoginFail';
    }

    public static function deactivate()
    {
        // TODO: Implement deactivate() method.
    }

    public static function personalConfig(Form $form)
    {
        // TODO: Implement personalConfig() method.
    }
}