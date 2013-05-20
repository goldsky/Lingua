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
function getSnippetContent($filename) {
    $o = file_get_contents($filename);
    $o = str_replace('<?php', '', $o);
    $o = str_replace('?>', '', $o);
    $o = trim($o);
    return $o;
}

$snippets = array();

$snippets[0] = $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
    'id' => 0,
    'property_preprocess' => 1,
    'name' => 'linguaSelector',
    'description' => 'Languages selector drop down.',
    'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/lingua.selector.snippet.php'),
        ), '', true, true);
$properties = include $sources['properties'] . 'linguaselector.snippet.properties.php';
$snippets[0]->setProperties($properties);
unset($properties);

$snippets[1] = $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 0,
    'property_preprocess' => 0,
    'name' => 'linguaCultureKey',
    'description' => 'Helper snippet to get running time cultureKey.',
    'snippet' => 'return $modx->cultureKey;',
        ), '', true, true);

return $snippets;