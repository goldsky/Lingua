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

namespace Lingua\Processors\ResourceScopes;

use MODX\Revolution\Processors\Model\CreateProcessor;
use Lingua\Model\LinguaResourceScopes;

class Create extends CreateProcessor {

    public $classKey = LinguaResourceScopes::class;
    public $languageTopics = array('lingua:cmp');
    public $objectType = 'lingua.ResourceScopesCreate';

    /**
     * {@inheritDoc}
     * @return boolean
     */
    public function initialize() {
        $this->object = $this->modx->newObject($this->classKey);
        $props = $this->getProperties();
        $properties = array();
        foreach ($props as $k => $v) {
            if (!preg_match('/^property_/', $k)) {
                continue;
            }
            $properties[preg_replace('/^property_/', '', $k)] = $v;
            $this->unsetProperty($k);
        }
        $this->setProperty('properties', json_encode($properties));

        return parent::initialize();
    }

}
