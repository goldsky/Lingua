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
 * @package lingua
 * @subpackage lingua_controller
 */
require_once dirname(__FILE__) . '/model/lingua/lingua.class.php';

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
