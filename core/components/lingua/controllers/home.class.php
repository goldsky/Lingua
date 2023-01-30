<?php

use MODX\Revolution\modExtraManagerController;

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

class LinguaHomeManagerController extends modExtraManagerController
{
    /** @var Lingua\Lingua $Lingua */
    public $Lingua;


    /**
     *
     */
    public function initialize()
    {
        $this->Lingua = $this->modx->services->get('Lingua');

        $this->addCss($this->Lingua->config['cssUrl'] . 'mgr.css');
        $this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/lingua.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Lingua.config = ' . $this->modx->toJSON($this->Lingua->config) . ';
        });
        </script>');

        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['lingua:default', 'lingua:cmp'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('lingua');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/window.autosync.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/window.manualsync.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/grid.sync.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/combo.resource.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/window.resourcescope.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/grid.resourcescopes.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'ux/CheckColumn.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/window.tvpattern.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/grid.tvspatterns.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/combo.tv.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/window.tv.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/grid.tvs.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/window.lang.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/grid.langs.js');
		$this->addJavascript($this->Lingua->config['jsUrl'] . 'mgr/widgets/panel.home.js');
		$this->addLastJavascript($this->Lingua->config['jsUrl'] . 'mgr/sections/index.js');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->Lingua->config['templatesPath'] . 'home.tpl';
    }
}
