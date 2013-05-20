<?php

$settings['lingua.get.key'] = $modx->newObject('modSystemSetting');
$settings['lingua.get.key']->fromArray(array(
    'key' => 'lingua.get.key',
    'value' => 'lang',
    'xtype' => 'textfield',
    'namespace' => 'lingua',
    'area' => 'URL',
        ), '', true, true);

$settings['lingua.code.field'] = $modx->newObject('modSystemSetting');
$settings['lingua.code.field']->fromArray(array(
    'key' => 'lingua.code.field',
    'value' => 'lang_code',
    'xtype' => 'textfield',
    'namespace' => 'lingua',
    'area' => 'URL',
        ), '', true, true);

return $settings;