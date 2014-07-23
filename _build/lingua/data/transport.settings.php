<?php

/**
 * Lingua
 *
 * Copyright 2013-2014 by goldsky <goldsky@virtudraft.com>
 *
 * This file is part of Lingua, a MODX's Lexicon switcher for front-end interface
 *
 * Lingua is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation version 3.
 *
 * Lingua is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Lingua; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * Lingua build script
 *
 * @package lingua
 * @subpackage build
 */

$settings['lingua.debug'] = $modx->newObject('modSystemSetting');
$settings['lingua.debug']->fromArray(array(
    'key' => 'lingua.debug',
    'value' => '0',
    'xtype' => 'combo-boolean',
    'namespace' => 'lingua',
    'area' => 'general',
        ), '', true, true);

$settings['lingua.get_key'] = $modx->newObject('modSystemSetting');
$settings['lingua.get_key']->fromArray(array(
    'key' => 'lingua.get_key',
    'value' => 'lang',
    'xtype' => 'textfield',
    'namespace' => 'lingua',
    'area' => 'URL',
        ), '', true, true);

$settings['lingua.code_field'] = $modx->newObject('modSystemSetting');
$settings['lingua.code_field']->fromArray(array(
    'key' => 'lingua.code_field',
    'value' => 'lang_code',
    'xtype' => 'textfield',
    'namespace' => 'lingua',
    'area' => 'URL',
        ), '', true, true);

$settings['lingua.contexts'] = $modx->newObject('modSystemSetting');
$settings['lingua.contexts']->fromArray(array(
    'key' => 'lingua.contexts',
    'value' => 'web',
    'xtype' => 'textfield',
    'namespace' => 'lingua',
    'area' => 'general',
        ), '', true, true);

$settings['lingua.parents'] = $modx->newObject('modSystemSetting');
$settings['lingua.parents']->fromArray(array(
    'key' => 'lingua.parents',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'lingua',
    'area' => 'general',
        ), '', true, true);

$settings['lingua.ids'] = $modx->newObject('modSystemSetting');
$settings['lingua.ids']->fromArray(array(
    'key' => 'lingua.ids',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'lingua',
    'area' => 'general',
        ), '', true, true);

return $settings;