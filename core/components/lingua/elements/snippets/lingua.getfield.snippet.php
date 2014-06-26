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
 * @package lingua
 * @subpackage lingua_getfield
 */

$field = $modx->getOption('field', $scriptProperties);
if (empty($field)) {
    return;
}

$langCodeField = $modx->getOption('codeField', $scriptProperties, $modx->getOption('lingua.code_field', null, 'lang_code'));
$defaultLinguaCorePath = $modx->getOption('core_path') . 'components/lingua/';
$linguaCorePath = $modx->getOption('lingua.core_path', null, $defaultLinguaCorePath);
$lingua = $modx->getService('lingua', 'Lingua', $linguaCorePath . 'model/lingua/', $scriptProperties);

if (!($lingua instanceof Lingua)) {
    return;
}

$langObj = $modx->getObject('linguaLangs', array(
    $langCodeField => $modx->cultureKey
));
if (!$langObj) {
    return;
}
$output = $langObj->get($field);
if (!$output) {
    return;
}
return $output;