<?php

if (!defined('MODX_CORE_PATH')) {
    $path = __DIR__;
    while (!file_exists($path . '/core/config/config.inc.php') && (strlen($path) > 1)) {
        $path = dirname($path);
    }
    define('MODX_CORE_PATH', $path . '/core/');
}

return [
    'name' => 'Lingua',
    'name_lower' => 'lingua',
    'version' => '3.0.3',
    'release' => 'beta',
    // Install package to site right after build
    'install' => true,
    // Which elements should be updated on package upgrade
    'update' => [
        'chunks' => true,
        'menus' => true,
        'permission' => true,
        'plugins' => true,
        'policies' => true,
        'policy_templates' => true,
        'resources' => false,
        'settings' => false,
        'snippets' => true,
        'templates' => false,
        'widgets' => false,
    ],
    // Which elements should be static by default
    'static' => [
        'plugins' => false,
        'snippets' => false,
        'chunks' => false,
    ],
    // Log settings
    'log_level' => !empty($_REQUEST['download']) ? 0 : 3,
    'log_target' => PHP_SAPI === 'cli' ? 'ECHO' : 'HTML',
    // Download transport.zip after build
    'download' => !empty($_REQUEST['download']),
];