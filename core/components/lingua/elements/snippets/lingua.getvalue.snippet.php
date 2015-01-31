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
 * @package lingua
 * @subpackage lingua_getfield
 */

$field = $modx->getOption('field', $scriptProperties);
if (empty($field)) {
    return;
}

$id = $modx->getOption('id', $scriptProperties, $modx->resource->get('id'));
$emptyReturnsDefault = $modx->getOption('emptyReturnsDefault', $scriptProperties, $modx->getOption('lingua.empty_returns_default', null, false));

$defaultLinguaCorePath = $modx->getOption('core_path') . 'components/lingua/';
$linguaCorePath = $modx->getOption('lingua.core_path', null, $defaultLinguaCorePath);
$lingua = $modx->getService('lingua', 'Lingua', $linguaCorePath . 'model/lingua/', $scriptProperties);
$debug = $modx->getOption('lingua.debug');
if (!($lingua instanceof Lingua)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[lingua.getValue]: !($lingua instanceof Lingua)');
    return;
}

$langObj = $modx->getObject('linguaLangs', array(
    'lang_code' => $modx->cultureKey
));
if (!$langObj) {
    if ($debug) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[lingua.getValue]: Missing field\'s value for ' . $field . ' in ' . $modx->cultureKey);
    }
    return;
}
$c = $modx->newQuery('linguaSiteContent');
$c->where(array(
    'resource_id' => $id,
    'lang_id' => $langObj->get('id'),
));
$linguaSiteContent = $modx->getObject('linguaSiteContent', $c);
$resource = $modx->getObject('modResource', $id);
if (!$resource) {
    if ($debug) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[lingua.getValue]: Missing resource for ' . $field . ' in ' . $modx->cultureKey);
    }
    return;
}

$tableFields = array('pagetitle', 'longtitle', 'description', 'alias',
    'link_attributes', 'introtext', 'content', 'menutitle', 'uri', 'uri_override',
    'properties');
$output = '';
if (in_array($field, $tableFields)) {
    if ($linguaSiteContent) {
        $output = $linguaSiteContent->get($field);
        if (empty($output)) {
            if ($emptyReturnsDefault) {
                $output = $resource->get($field); // return default language's value
            }
        }
    } else {
        $output = $resource->get($field); // return default language's value
    }
}
// try TV
else {
    $tv = $modx->getObject('modTemplateVar', array(
        'name' => $field,
    ));
    if ($tv) {
        $linguaSiteTmplvarContentvalues = $modx->getObject('linguaSiteTmplvarContentvalues', array(
            'lang_id' => $langObj->get('id'),
            'tmplvarid' => $tv->get('id'),
            'contentid' => $id,
        ));
        if ($linguaSiteTmplvarContentvalues) {
            $value = $linguaSiteTmplvarContentvalues->get('value');
            $tv->set('resourceId', $id);
            if (empty($value) && !$emptyReturnsDefault) {
                $tv->set('value', $value); // return empty value
            } else {
                $tv->set('value', $value);
            }
        }
        $output = $tv->renderOutput($resource->get('id'));
    }
}

return $output;