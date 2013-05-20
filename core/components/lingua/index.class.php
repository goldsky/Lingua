<?php

require_once dirname(__FILE__) . '/model/lingua.class.php';

abstract class LinguaManagerController extends modExtraManagerController {

	/** @var Lingua $lingua */
	public $lingua;

	public function initialize() {
		$this->lingua = new Lingua($this->modx);

		$this->addCss($this->lingua->config['cssUrl'] . 'mgr.css');
		$this->addJavascript($this->lingua->config['jsUrl'] . 'mgr/lingua.js');
		$this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Lingua.config = ' . $this->modx->toJSON($this->lingua->config) . ';
        });
        </script>');
		return parent::initialize();
	}

	public function getLanguageTopics() {
		return array('lingua:default', 'lingua:cmp');
	}

	public function checkPermissions() {
		return true;
	}

}

class IndexManagerController extends LinguaManagerController {

	public static function getDefaultController() {
		return 'home';
	}

}