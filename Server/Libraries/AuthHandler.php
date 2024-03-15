<?php
/**
 *  @ SiGool
 *  2024/02/26
 */

namespace Gzh2typechoServer\Libraries;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

if (!defined('DEV_MODE'))
    exit;

/**
 * 用户拦截(OpenID白名单)
 * Class AuthHandler
 * @package Gzh2typechoServer\Libraries
 */
class AuthHandler implements EventHandlerInterface
{
    public function handle($payload = null) {
        
        $openids = trim(\App::getRawCfg('commcAllowOpenid'));
        foreach (explode(',', $openids) as $openid) {
            if (($openid = trim($openid)) === '*' || $openid = $payload->FromUserName)
                return;
        }

        return false; // 没权限，不能往下执行别的handle了
    }
}