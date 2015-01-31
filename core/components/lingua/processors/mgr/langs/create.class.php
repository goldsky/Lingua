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
 * @subpackage lingua_processor
 */
class LangsCreateProcessor extends modObjectCreateProcessor {

    public $classKey = 'linguaLangs';
    public $languageTopics = array('lingua:cmp');
    public $objectType = 'lingua.LangsCreate';

    public function beforeSave() {
        $langCode = $this->getProperty('lang_code');
        $lcidString = $this->getProperty('lcid_string');
        $lcidDecimal = $this->getProperty('lcid_dec');

        if (empty($langCode) && empty($lcidString) && empty($lcidDecimal)) {
            $this->addFieldError('lang_code', $this->modx->lexicon('lingua.langs_err_ns_lang_data'));
            $this->addFieldError('lcid_string', $this->modx->lexicon('lingua.langs_err_ns_lang_data'));
            $this->addFieldError('lcid_dec', $this->modx->lexicon('lingua.langs_err_ns_lang_data'));
        } else if ($this->doesAlreadyExist(array('lcid_string' => $lcidString))) {
            $this->addFieldError('lcid_string', $this->modx->lexicon('lingua.langs_err_lcid_string_exists'));
        } else if ($this->doesAlreadyExist(array('lcid_dec' => $lcidDecimal))) {
            $this->addFieldError('lcid_dec', $this->modx->lexicon('lingua.langs_err_lcid_dec_exists'));
        }
        return parent::beforeSave();
    }

}

return 'LangsCreateProcessor';
