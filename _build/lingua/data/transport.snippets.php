<?php

/**
 * Lingua
 *
 * Copyright 2013-2015 by goldsky <goldsky@virtudraft.com>
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

$snippets = array();

$snippets['lingua.selector'] = $modx->newObject('modSnippet');
$snippets['lingua.selector']->fromArray(array(
    'id' => 0,
    'property_preprocess' => 1,
    'name' => 'lingua.selector',
    'description' => 'Languages selector drop down.',
    'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/lingua.selector.snippet.php'),
        ), '', true, true);
$properties = include $sources['properties'] . 'lingua.selector.snippet.properties.php';
$snippets['lingua.selector']->setProperties($properties);
unset($properties);

$snippets['lingua.cultureKey'] = $modx->newObject('modSnippet');
$snippets['lingua.cultureKey']->fromArray(array(
    'id' => 0,
    'property_preprocess' => 1,
    'name' => 'lingua.cultureKey',
    'description' => 'Helper snippet to get the run time cultureKey, which is set by lingua\'s plugin.',
    'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/lingua.culturekey.snippet.php'),
        ), '', true, true);

$snippets['lingua.getField'] = $modx->newObject('modSnippet');
$snippets['lingua.getField']->fromArray(array(
    'id' => 0,
    'property_preprocess' => 1,
    'name' => 'lingua.getField',
    'description' => 'Get the value of the given field for the run time culture key.',
    'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/lingua.getfield.snippet.php'),
        ), '', true, true);

$snippets['lingua.getValue'] = $modx->newObject('modSnippet');
$snippets['lingua.getValue']->fromArray(array(
    'id' => 0,
    'property_preprocess' => 1,
    'name' => 'lingua.getValue',
    'description' => 'Get the value of the clone\'s field for the run time culture key.',
    'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/lingua.getvalue.snippet.php'),
        ), '', true, true);
$properties = include $sources['properties'] . 'lingua.selector.snippet.properties.php';
$snippets['lingua.getValue']->setProperties($properties);
unset($properties);

return $snippets;