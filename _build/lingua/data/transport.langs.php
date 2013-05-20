<?php

/**
 * Lingua
 *
 * Copyright 2013 by goldsky <goldsky@virtudraft.com>
 *
 * This file is part of Lingua
 *
 * Filling up db tables
 *
 * @package lingua
 * @subpackage build
 */
$collection = array();
$langs = include $sources['data'] . 'contents/modx_lingua_langs.php';
foreach ($langs as $lang) {
	$newLang = $modx->newObject('Langs');
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