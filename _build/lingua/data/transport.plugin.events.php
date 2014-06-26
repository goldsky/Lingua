<?php

/**
 * Lingua
 *
 * Copyright 2013 by goldsky <goldsky@virtudraft.com>
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
 * @package Lingua
 * @subpackage build
 */
$events = array();

$events['OnInitCulture'] = $modx->newObject('modPluginEvent');
$events['OnInitCulture']->fromArray(array(
    'event' => 'OnInitCulture',
    'priority' => 0,
    'propertyset' => 0,
        ), '', true, true);

$events['OnDocFormPrerender'] = $modx->newObject('modPluginEvent');
$events['OnDocFormPrerender']->fromArray(array(
    'event' => 'OnDocFormPrerender',
    'priority' => 0,
    'propertyset' => 0,
        ), '', true, true);

$events['OnDocFormSave'] = $modx->newObject('modPluginEvent');
$events['OnDocFormSave']->fromArray(array(
    'event' => 'OnDocFormSave',
    'priority' => 0,
    'propertyset' => 0,
        ), '', true, true);

$events['OnResourceDelete'] = $modx->newObject('modPluginEvent');
$events['OnResourceDelete']->fromArray(array(
    'event' => 'OnResourceDelete',
    'priority' => 0,
    'propertyset' => 0,
        ), '', true, true);

$events['OnResourceDuplicate'] = $modx->newObject('modPluginEvent');
$events['OnResourceDuplicate']->fromArray(array(
    'event' => 'OnResourceDuplicate',
    'priority' => 0,
    'propertyset' => 0,
        ), '', true, true);

$events['OnWebPageInit'] = $modx->newObject('modPluginEvent');
$events['OnWebPageInit']->fromArray(array(
    'event' => 'OnWebPageInit',
    'priority' => 0,
    'propertyset' => 0,
        ), '', true, true);

$events['OnWebPageInit'] = $modx->newObject('modPluginEvent');
$events['OnWebPageInit']->fromArray(array(
    'event' => 'OnWebPageInit',
    'priority' => 0,
    'propertyset' => 0,
        ), '', true, true);

return $events;