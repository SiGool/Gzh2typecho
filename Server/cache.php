<?php
/**
 *  @ SiGool
 *  2024/03/09
 *
 * 插件使用的缓存驱动
 */
require_once __DIR__ . '/../../../../vendor/autoload.php';

return new \Symfony\Component\Cache\Adapter\FilesystemAdapter('', 0, __DIR__ . '/cache');