<?php
/**
 *  @ SiGool
 *  2024/03/07
 */

use EasyWeChat\Kernel\Http\StreamResponse;

require __DIR__ . '/../../../../vendor/autoload.php';

$loader = new \Composer\Autoload\ClassLoader();
$loader->addPsr4('Gzh2typechoServer\\', __DIR__);
$loader->addPsr4('Typecho\\', __DIR__ . '/../../../../var/Typecho');
$loader->register();

set_time_limit(0);

## 页面是否显示错误 ##
ini_set('display_errors', 0);
######

define('ERROR_LOG_PATH', __DIR__ . '/logs/post.log');
define('TOKEN_QY_STR_NAME_ON_PUB_LINK', 'token');

/** 【注！】如果修改了Typecho系统内置常量 __TYPECHO_ADMIN_DIR__ 的默认值，也需要同步到此 **/
define('__TYPECHO_ADMIN_DIR__', '/admin/');
/**/

/** 提取 Typecho后台登录脚本里表单提交的目标URL 需要用到的正则表达式  **/
define('PARSE_REAL_LOGIN_URL_BY_PATTERN', '/<form\s+?action\s*?=\s*?"(.+?)"/');
/**/

/** 提取 Typecho后台写文章脚本里新增文章提交的目标URL 需要用到的正则表达式  **/
define('PARSE_ADD_POST_URL_BY_PATTERN', '/<form\s+?action\s*?=\s*?"(.+?)"/');
/**/

/** 提取 Typecho后台写文章脚本里附件上传提交的目标URL 需要用到的正则表达式  **/
define('PARSE_UPLOAD_ATTACHMENT_URL_BY_PATTERN', "/browse_button[\s\S]+?url[^']+'([^']+)'/");
/**/

/** 提取 Typecho后台写文章脚本里出现的所有分类 需要用到的正则表达式  **/
define('PARSE_ALL_CATEGORIES_BY_PATTERN', '/<label[\s\S]+?for\s*?=\s*?"category-(\d+)"[\s\S]*?>(.+?)</');
/**/

/** 公开度选项 **/
define('SECRET_LV_OPTIONS', [
    '公开' => 'publish',
    '隐藏' => 'hidden',
    '私密' => 'private',
    '待审核' => 'waiting'
]);
/**/


$logger = new \Monolog\Logger('post-server');
$logger->pushHandler(new \Monolog\Handler\StreamHandler(ERROR_LOG_PATH, \Monolog\Logger::ERROR));
function errorLog(Throwable $e, $descript = '') {
    global $logger;
    $errorMsg = '【异常：' . $e->getCode() . '】' . ($descript === ''? '' : ($descript . '：')) . $e->getMessage() . '<' . $e->getFile() . ',' . $e->getLine() . '>';
    $logger->error($errorMsg);
}

function run() {
    $postData = null;
    $errExit = function ($tip, $delTmpMediaFiles = false) use(&$postData) {
        if ($delTmpMediaFiles) {
            array_map(function($filePath) {
                @unlink($filePath);
            }, $postData['__extends']['mediaIDs']);
        }
        exit('[📢 Gzh2typecho]：' . $tip);
    };

    if (!isset($_GET[TOKEN_QY_STR_NAME_ON_PUB_LINK]))
        $errExit('发布地址不存在或已失效！');

    $cacheSys = require __DIR__ . '/cache.php';
    $cache = $cacheSys->getItem($_GET[TOKEN_QY_STR_NAME_ON_PUB_LINK]);
    if (!$cache->isHit())
        $errExit('发布地址不存在或已失效！');
    $postData = $cache->get();

    // 内容媒体文件先下载到本地先
    $cookies = new \GuzzleHttp\Cookie\CookieJar();
    $client = new \GuzzleHttp\Client([
        \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => [
            'track_redirects' => true
        ],
        \GuzzleHttp\RequestOptions::COOKIES => $cookies
    ]);
    foreach ($postData['__extends']['mediaIDs'] as $mID => &$mIDReqUrl) {

        if (($response = $client->get($mIDReqUrl))->getStatusCode() !== 200 || stripos($response->getHeaderLine('Content-disposition'), 'attachment') === false)
            $errExit('从微信服务器获取文章内容中的图片/视频失败！', true);
        $response = StreamResponse::buildFromPsrResponse($response);

        // 临时下载到本地先，后面在上传到附件
        $mIDReqUrl = substr_replace(md5($mIDReqUrl), date('-YmdHis-'), 9, 14);
        $mIDReqUrl = sys_get_temp_dir() . '/' . $response->save(sys_get_temp_dir(), $mIDReqUrl);
    }

    $baseUrl = \Gzh2typechoServer\Libraries\Helper::currentBaseUrl();
    $autoLogin = function() use ($errExit, $baseUrl, $client) {
        /*
        $loginUrl = $baseUrl . '/' . trim(__TYPECHO_ADMIN_DIR__, '/') . '/welcome.php';
        $response = $client->get($loginUrl); // 通过请求未登录页面会自动跳转到登录页面以得到登录页面URL
        $loginUrl = $response->getHeaderLine('X-Guzzle-Redirect-History');
        $loginUrl = explode('?', $loginUrl)[0];
        */
        $loginUrl = $baseUrl . '/' . trim(__TYPECHO_ADMIN_DIR__, '/') . '/login.php';

        preg_match( // 从登录页面获取真实的登录表单提交URL
            PARSE_REAL_LOGIN_URL_BY_PATTERN,
            $client->get($loginReferer = $loginUrl)
                ->getBody()
                ->getContents(),
            $loginUrl
        );
        $loginUrl = $loginUrl[1]; // 真正的登录表单POST URL 上面带有后端验证的token 很重要

        // 发起真正的登录请求
        $response = $client->post($loginUrl, [
            \GuzzleHttp\RequestOptions::HEADERS => [
                // 这里是重点，typecho很多action都是要验证url上 _=xxxx 的xxx这串东西的
                // 跟header上Referer进行一系列算法比对
                /**
                 * 参见： \Widget\Security::protect()
                 */
                'Referer' => $loginReferer
            ],
            \GuzzleHttp\RequestOptions::FORM_PARAMS => [
                'name' => 'Gzh2typecho',
                'password' => $_GET[TOKEN_QY_STR_NAME_ON_PUB_LINK],
                'referer' => ''
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);
        if ($response['err'] !== 0)
            $errExit('登录失败：' . $response['msg'] . '）', true);
    };

    $ifNeedLogin = function($response) use ($errExit) {
        // typecho的很多action都有protect，如果校验不通过会302到登录页面

        /**
         * @var \GuzzleHttp\Psr7\Response $response
         */
        if ($response->getStatusCode() !== 200)
            return false;

        if (stripos($response->getHeaderLine('Content-Type'), 'text/html') === false)
            return false;

        if ($response->getHeaderLine('X-Guzzle-Redirect-Status-History') !== '302')
            return false;

        if (stripos($response->getHeaderLine('X-Guzzle-Redirect-History'), '/' . trim(__TYPECHO_ADMIN_DIR__, '/') . '/login.php?referer=') === false)
            return false;

        return true;
    };

    $req = function($reqSomethings, $isUpload = false) use ($ifNeedLogin, $autoLogin, $errExit) {

        !defined('REQ_MAX_TRY_TIMES')
            && define('REQ_MAX_TRY_TIMES', 3);

        $times = 0;
        while ($times++ < REQ_MAX_TRY_TIMES) {
            // 可能发布用户在后台登录了，顶掉了（更新了登录凭证），所以我们要重试登录

            /**
             * @var \GuzzleHttp\Psr7\Response $resp
             */
            $resp = $reqSomethings();
            if ($isUpload) {
                // 上传附件action
                if ($resp->getStatusCode() === 403)
                    $errExit('用户发布文章的用户账号无上传附件的权限', true);
                if (stripos($resp->getHeaderLine('Content-Type'), 'application/json') === false) {
                    // 不是JSON响应输出，登录验证没通过，被后台登录顶掉了
                    $autoLogin();
                    continue;
                }
            } else if ($ifNeedLogin($resp)) // 被后台登录顶掉了
                $autoLogin();

            // 没被拦截去登录，直接返回响应结果
            return $resp;
        }

        // 已尝试过最大限定次数了，依然没有过登录验证
        $errExit('多次登录失败！', true);
    };

    // 登录
    $autoLogin();

    // 分别获取新文章和附件上传提交的目标URL
    $writePostUrl = $baseUrl . '/' . trim(__TYPECHO_ADMIN_DIR__, '/') . '/write-post.php';
    $response = $req(function() use ($client, $writePostUrl) {
        return $client->get($writePostUrl);
    })->getBody()->getContents();

    preg_match(PARSE_ADD_POST_URL_BY_PATTERN, $response, $addPostUrl);
    $addPostUrl = $addPostUrl[1]; // 新增文章提交URL

    preg_match(PARSE_UPLOAD_ATTACHMENT_URL_BY_PATTERN, $response, $uploadAttachmentUrl);
    $uploadAttachmentUrl = $uploadAttachmentUrl[1]; // 新增附件提交URL

    if (preg_match_all(PARSE_ALL_CATEGORIES_BY_PATTERN, $response, $categories) > 0)
        $categories = array_combine($categories[2], $categories[1]);
    else
        $categories = [];        // 所有文章分类

    // 校验分类
    if ($postData['categeory'] === '')
        $postData['categeory'] = [];
    else {
        $postData['categeory'] = explode("\n", $postData['categeory']);
        foreach ($postData['categeory'] as &$category) {
            if (!isset($categories[$category]))
                $errExit('分类“' . $category . '”不存在！', true);
            $category = $categories[$category];
        }
        $postData['categeory'] = array_unique($postData['categeory']);
    }

    // 标签
    if ($postData['label'] !== '') {
        $postData['label'] = explode("\n", $postData['label']);
        $postData['label'] = array_unique($postData['label']);
        $postData['label'] = implode(',', $postData['label']);
    }

    // 公开度
    if (isset(SECRET_LV_OPTIONS[$postData['secretLv']])) {
        $postData['secretLvPwd'] = '';
        $postData['secretLv'] = SECRET_LV_OPTIONS[$postData['secretLv']];
    } else { // 密码保护选项
        $postData['secretLvPwd'] = $postData['secretLv'];
        $postData['secretLv'] = 'password';
    }

    // 权限控制
    $postData['privCtrl'] = explode(',', $postData['privCtrl']);
    $postData['privCtrl'] = [
        'allowComment' => $postData['privCtrl'][0],
        'allowPing' => $postData['privCtrl'][1],
        'allowFeed' => $postData['privCtrl'][2]
    ];

    // 附件挨个上传
    $postData['contentAttachments'] = [];
    foreach ($postData['__extends']['mediaIDs'] as $mID => $mIDPath) {
        $response = $req(function() use ($client, $uploadAttachmentUrl, $mID, $mIDPath, $writePostUrl, &$postData) {
            return $client->post($uploadAttachmentUrl, [
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Referer' => $writePostUrl
                ],
                \GuzzleHttp\RequestOptions::MULTIPART => [
                    [
                        'name'     => 'name',
                        'contents' => basename($mIDPath)
                    ],
                    [
                        'name'     => 'file',
                        'contents' => fopen($mIDPath, 'r'),
                        'filename' => basename($mIDPath)
                    ]
                ]
            ]);
        }, true)->getBody()->getContents();
        @unlink($mIDPath); // 删除本地的临时内容媒体文件

        if (!is_array($response = json_decode($response, true)))
            $errExit('文章内容中的图片/视频上传到附件失败', true);

        // $response[0] 真实的媒体文件URL
        // 现在替换文章内容中出现的
        $postData['content'] = str_replace($mID, $response[0], $postData['content']);

        // 记录已上传附件的ID，后面新增文章，要同时作为数据提交以关联文章的
        $postData['contentAttachments'][$mID] = $response[1]['cid'];
    }

    // 发布文章
    try {
        $response = $req(function() use ($client, $addPostUrl, $writePostUrl, $postData) {
            return $client->post($addPostUrl, [
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Referer' => $writePostUrl
                ],
                \GuzzleHttp\RequestOptions::FORM_PARAMS => [
                    'password' => $postData['secretLvPwd'],
                    'allowComment' => $postData['privCtrl']['allowComment'],
                    'allowPing' => $postData['privCtrl']['allowPing'],
                    'allowFeed' => $postData['privCtrl']['allowFeed'],
                    'tags' => $postData['label'],
                    'text' => $postData['content'],
                    'visibility' => $postData['secretLv'],
                    'category' => $postData['categeory'],
                    'title' => $postData['title'],
                    'markdown' => 1,
                    'do' => 'publish',
                    'date' => $postData['pubDate']? : date('Y-m-d H:i'),
                    'cid' => '',
                    'trackback' => $postData['usingNotice'],
                    'attachment' => array_values($postData['contentAttachments'])
                ]
            ]);
        });

        // 删除文章草稿重置设置操作标识
        $cacheSys->deleteItem($postData['__extends']['uid'] . '.draft');
        $action = $cacheSys->getItem($postData['__extends']['uid'] . '.action');
        $action->set(-1);
        $cacheSys->save($action);

        // 响应发布成功了
        foreach ($cookies as $cookie) {
            /**
             * @var \GuzzleHttp\Cookie\SetCookie $cookie
             */
            if (substr($cookie->getName(), -strlen('__typecho_notice')) === '__typecho_notice')
                exit('[📢 Gzh2typecho 🎉]：' . json_decode(rawurldecode($cookie->getValue()), true)[0]);
        }

        exit('[📢 Gzh2typecho 🎉]：发布成功 o(^▽^)o');
    } catch (Throwable $e) {
        errorLog($e);
        $errExit('发布失败！', true);
    }
}

try {
    run();
} catch (Throwable $e) {
    errorLog($e);
    echo '[📢 Gzh2typecho内部服务]：发布失败！服务异常！';
}
