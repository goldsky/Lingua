<?php

/**
 * Lingua
 *
 * Copyright 2013 by goldsky <goldsky@virtudraft.com>
 *
 * This file is part of Lingua
 *
 * Lingua build script
 *
 * @package lingua
 * @subpackage build
 */

$action = $modx->newObject('modAction');
$action->fromArray(array(
    'id' => 1,
    'namespace' => 'lingua',
    'parent' => 0,
    'controller' => 'index',
    'haslayout' => true,
    'lang_topics' => 'lingua:default',
    'assets' => '',
        ), '', true, true);

$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'lingua',
    'parent' => 'components',
    'description' => 'lingua_desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => 0,
    'params' => '',
    'handler' => '',
        ), '', true, true);
$menu->addOne($action);
unset($menus);

return $menu;