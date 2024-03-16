<?php
/**
 *  @ SiGool
 *  2024/02/25
 *
 * 微信公众号开发服务器配置 服务器地址(URL) 入口
 */
require __DIR__ . '/../../../../vendor/autoload.php';

use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Message;
use Gzh2typechoServer\Libraries\AuthHandler;
use Gzh2typechoServer\Libraries\MenuSrcHandler;
use Gzh2typechoServer\Libraries\PubPostInteractHandler;
use Gzh2typechoServer\Libraries\WaitingHandler;

define('BASE_PATH', dirname((new ReflectionClass(\Composer\Autoload\ClassLoader::class))->getFileName(), 3));
define('DEV_MODE', true);

class App {

    const RAW_CONFIG_KEY = '__raw_cfg';

    /**
     * @var \EasyWeChat\OfficialAccount\Application
     */
    public static $instance;

    /**
     * @var \Symfony\Component\Cache\Adapter\AbstractAdapter
     * 对话缓存使用的Adapter
     */
    public static $cache;

    public function __construct() {

        // 注册类自动加载时可以查找的目录
        ($loader = new \Composer\Autoload\ClassLoader())
            ->addPsr4('Gzh2typechoServer\\', __DIR__);
        $loader->register();

        // !! 对话缓存，默认使用文件缓存系统，如需使用redis或者memcached之类，请在下方自行修改 !!
        self::$cache = require __DIR__ . '/cache.php';
        // ======== 对话缓存 ========

        if (!DEV_MODE)
            ini_set('display_errors', '0');
    }

    public function run() {

        $cfg = require __DIR__ . '/cfg.php';

        // 公众号实例
        self::$instance = Factory::officialAccount([
            'app_id' => $cfg['mpDevAppID'],
            'secret' => $cfg['mpDevAppSecret'],
            'aes_key' => $cfg['mpDevEncodingAESKey'],
            'token' => $cfg['mpDevToken'],
            'response_type' => 'collection',
            /**
             * 日志配置
             *
             * level: 日志级别, 可选为：
             *         debug/info/notice/warning/error/critical/alert/emergency
             * path：日志文件位置(绝对路径!!!)，要求可写权限
             */
            'log' => [
                'default' => DEV_MODE? 'dev' : 'prod', // 默认使用的 channel，生产环境可以改为下面的 prod
                'channels' => [
                    // 测试环境
                    'dev' => [
                        'driver' => 'single',
                        'path' => __DIR__ . '/logs/easywechat.log',
                        'level' => 'debug'
                    ],
                    // 生产环境
                    'prod' => [
                        'driver' => 'daily',
                        'path' => __DIR__ . '/logs/easywechat.log',
                        'level' => 'info'
                    ]
                ]
            ],
            self::RAW_CONFIG_KEY => $cfg
        ]);

        self::$instance->server
                    ->push(MenuSrcHandler::class);

        self::$instance->server
                    ->push(AuthHandler::class);

        self::$instance->server
                    ->push(WaitingHandler::class);

        self::$instance->server
                    ->push(PubPostInteractHandler::class, Message::TEXT|Message::IMAGE|Message::VIDEO|Message::SHORT_VIDEO);

        try {
            self::$instance->server
                        ->serve()
                        ->send();
        } catch (Throwable $e) {
            self::errorLog($e, '服务异常');
        }
    }

    public static function errorLog(Throwable $e, $descript = '') {
        $errorMsg = '【异常：' . $e->getCode() . '】' . ($descript === ''? '' : ($descript . '：')) . $e->getMessage() . '<' . $e->getFile() . ',' . $e->getLine() . '>';
        self::$instance->logger
                    ->error($errorMsg);
    }

    public static function getRawCfg($key) {
        $params = func_get_args();
        $rawCfg = self::$instance->getConfig()[self::RAW_CONFIG_KEY];
        if (isset($rawCfg[$params[0]]))
            return $rawCfg[$params[0]];
        if (isset($params[1])) // 有默认值
            return $params[1];
        throw new InvalidArgumentException('配置项“' . $key . '”不存在');
    }
}

(new App())->run();