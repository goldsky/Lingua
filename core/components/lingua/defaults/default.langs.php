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
 * Filling up db tables
 *
 * @package lingua
 * @subpackage build
 */
$collection = array();
$langs = include dirname(__FILE__) . '/modx_lingua_langs.php';
foreach ($langs as $lang) {
    $oldLang = $modx->getObject('linguaLangs', array(
		'lang_code' => $lang['lang_code'],
		'lcid_string' => $lang['lcid_string'],
		'lcid_dec' => $lang['lcid_dec'],
    ));
    if ($oldLang) {
        continue;
    }
	$newLang = $modx->newObject('linguaLangs');
	$newLang->fromArray(array(
		'active' => $lang['lang_code'] === 'en' ? 1 : 0,
		'local_name' => $lang['local_name'],
		'lang_code' => $lang['lang_code'],
		'lcid_string' => $lang['lcid_string'],
		'lcid_dec' => $lang['lcid_dec'],
		'date_format_lite' => $lang['date_format_lite'],
		'date_format_full' => $lang['date_format_full'],
		'is_rtl' => $lang['is_rtl'],
		'flag' => $lang['flag'],
			), '', true, true);
	$collection[] = $newLang;
}
return $collection;