<?php

class LinguaHomeManagerController extends LinguaManagerController {

	public function process(array $scriptProperties = array()) {

	}

	public function getPageTitle() {
		return $this->modx->lexicon('lingua');
	}

	public function loadCustomCssJs() {
		$this->addJavascript($this->lingua->config['jsUrl'] . 'ux/CheckColumn.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/lingua.grid.langs.js');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->addLastJavascript($this->lingua->config['jsUrl'] . 'mgr/sections/index.js');
	}

	public function getTemplateFile() {
		return $this->lingua->config['templatesPath'] . 'home.tpl';
	}

}