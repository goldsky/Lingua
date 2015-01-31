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
 * Filling up db tables
 *
 * @package lingua
 * @subpackage build
 */
$collection = array();
$patterns = include dirname(__FILE__) . '/modx_lingua_site_tmplvars_patterns.php';
foreach ($patterns as $pattern) {
    $oldPattern = $modx->getObject('linguaSiteTmplvarsPatterns', array(
		'type' => $pattern['type'],
		'search' => $pattern['search'],
		'replacement' => $pattern['replacement'],
    ));
    if ($oldPattern) {
        continue;
    }
	$newPattern = $modx->newObject('linguaSiteTmplvarsPatterns');
	$newPattern->fromArray(array(
		'type' => $pattern['type'],
		'search' => $pattern['search'],
		'replacement' => $pattern['replacement'],
			), '', true, true);
	$collection[] = $newPattern;
}
return $collection;