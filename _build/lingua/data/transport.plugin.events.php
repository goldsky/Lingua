<?php

/**
 * @package Lingua
 * @subpackage build
 */
$events = array();

$events['OnHandleRequest'] = $modx->newObject('modPluginEvent');
$events['OnHandleRequest']->fromArray(array(
    'event' => 'OnHandleRequest',
    'priority' => 0,
    'propertyset' => 0,
        ), '', true, true);

return $events;