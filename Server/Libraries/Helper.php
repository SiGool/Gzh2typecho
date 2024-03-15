<?php
/**
 *  @ SiGool
 *  2024/03/09
 */
namespace Gzh2typechoServer\Libraries;

/**
 * 助手类
 * Class Helper
 * @package TypechoPlugin\Gzh2typecho\Libraries
 */
class Helper
{
    public static function currentBaseUrl() {
        $protocol = 'http://';

        if ((!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'http') === 'https') {
            $protocol = 'https://';
        }

        return $protocol . $_SERVER['HTTP_HOST'];
    }
}