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

/**
 * @param   string  $filename   filename
 * @return  string  file content
 */
if (!function_exists('getSnippetContent')) {
    function getSnippetContent($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<?php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }
}

$plugins = array();

$plugins[0] = $modx->newObject('modPlugin');
$plugins[0]->fromArray(array(
    'id' => 0,
    'property_preprocess' => 1,
    'name' => 'Lingua',
    'description' => '',
    'plugincode' => getSnippetContent($sources['source_core'] . '/elements/plugins/lingua.plugin.php'),
        ), '', true, true);
$properties = include $sources['properties'] . 'lingua.plugin.properties.php';
$plugins[0]->setProperties($properties);
unset($properties);

/* add plugin events */
$events = include $sources['data'] . 'transport.plugin.events.php';
if (is_array($events) && !empty($events)) {
    $plugins[0]->addMany($events);
    $modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in ' . count($events) . ' Plugin Events.');
    flush();
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find plugin events!');
}

return $plugins;