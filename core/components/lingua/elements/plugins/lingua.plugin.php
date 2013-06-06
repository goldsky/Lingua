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
 * @package lingua
 * @subpackage lingua_plugin
 */

if ($modx->context->key === 'mgr') {
    return;
}

$event = $modx->event->name;
switch ($event) {
    case 'OnHandleRequest':
        $langKey = $modx->getOption('lingua.get.key', $scriptProperties, 'lang');
        $lang = $modx->cultureKey;
        if (isset($_GET[$langKey]) &&
                $_GET[$langKey] !== '' &&
                $_GET[$langKey] !== $lang
        ) {
            $lang = $_GET[$langKey];
            $_SESSION['cultureKey'] = $lang;
            $modx->cultureKey = $lang;
            $modx->setOption('cultureKey', $lang);
            setcookie('modx.lingua.switcher', $lang, time() + (1 * 24 * 60 * 60));
        } else if (isset($_COOKIE['modx.lingua.switcher']) &&
                $_COOKIE['modx.lingua.switcher'] !== '' &&
                $_COOKIE['modx.lingua.switcher'] !== $lang
        ) {
            $lang = $_COOKIE['modx.lingua.switcher'];
            $_SESSION['cultureKey'] = $lang;
            $modx->cultureKey = $lang;
            $modx->setOption('cultureKey', $lang);
        }

        $modx->setPlaceholder('lingua.cultureKey', $lang);
        $modx->setPlaceholder('lingua.language', $lang);
        break;

    default:
        break;
}
return;