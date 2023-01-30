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

namespace Lingua\Processors\TvPatterns;

use Lingua\Model\LinguaSiteTmplvarsPatterns;
use MODX\Revolution\Processors\Model\UpdateProcessor;

class Update extends UpdateProcessor {

    public $classKey = LinguaSiteTmplvarsPatterns::class;
    public $languageTopics = array('lingua:cmp');
    public $objectType = 'lingua.TVPatternUpdate';

    public function initialize() {
        $primaryKey = $this->getProperty($this->primaryKeyField,false);
        if (empty($primaryKey)) return $this->modx->lexicon($this->objectType.'_err_ns');
        $this->object = $this->modx->getObject($this->classKey,$primaryKey);
        if (empty($this->object)) return $this->modx->lexicon($this->objectType.'_err_nfs',array($this->primaryKeyField => $primaryKey));

        $check = $this->modx->getCount($this->classKey, array(
            'type' => $this->getProperty('type'),
            'search' => $this->getProperty('search'),
            'replacement' => $this->getProperty('replacement'),
        ));
        if ($check) {
            return $this->modx->lexicon('lingua.pattern_exists', array('type' => $this->getProperty('type')));
        }

        if ($this->checkSavePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('save')) {
            return $this->modx->lexicon('access_denied');
        }
        return true;
    }

}
