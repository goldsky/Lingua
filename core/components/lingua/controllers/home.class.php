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
 * @subpackage lingua_controller
 */

class LinguaHomeManagerController extends LinguaManagerController {

	public function process(array $scriptProperties = array()) {

	}

	public function getPageTitle() {
		return $this->modx->lexicon('lingua');
	}

	public function loadCustomCssJs() {
		$this->addJavascript($this->lingua->config['jsUrl'] . 'ux/CheckColumn.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/window.tvpattern.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/grid.tvspatterns.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/combo.tv.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/window.tv.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/grid.tvs.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/window.lang.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/grid.langs.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/panel.home.js');
		$this->addLastJavascript($this->lingua->config['jsUrl'] . 'mgr/sections/index.js');
	}

	public function getTemplateFile() {
		return $this->lingua->config['templatesPath'] . 'home.tpl';
	}

}