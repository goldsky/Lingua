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
 * @package Lingua
 * @subpackage build
 */

function setEventObjects(array $events = array()) {
    global $modx;
    $eventObjects = array();
    foreach ($events as $k => $v) {
        $eventObjects[$v] = $modx->newObject('modPluginEvent');
        $eventObjects[$v]->fromArray(array(
            'event' => $v,
            'priority' => 0,
            'propertyset' => 0,
                ), '', true, true);

    }
    return $eventObjects;
}
$events = setEventObjects(array(
    'OnPageNotFound',
    'OnHandleRequest',
    'OnInitCulture',
    'OnLoadWebDocument',
    /////////////////// MANAGER SIDE ///////////////////
    'OnDocFormPrerender',
    'OnResourceTVFormRender',
    'OnDocFormSave',
    'OnResourceDuplicate',
    'OnEmptyTrash',
    'OnTemplateSave',
    'OnTempFormSave',
    'OnTVFormSave',
    'OnSnipFormSave',
    'OnPluginFormSave',
    'OnMediaSourceFormSave',
    'OnChunkFormSave',
    'OnSiteRefresh',
));

return $events;