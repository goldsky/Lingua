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
class ResourceScopesGetListProcessor extends modObjectGetListProcessor {

    public $classKey = 'linguaResourceScopes';
    public $languageTopics = array('lingua:cmp');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'lingua.ResourceScopesGetList';

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->leftJoin('modResource', 'Resource', 'Resource.id = linguaResourceScopes.resource_id');
        $c->select(array(
            'linguaResourceScopes.*',
            'pagetitle' => 'Resource.pagetitle'
        ));
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where(array(
                'Resource.pagetitle:LIKE' => '%' . $query . '%',
            ));
        }
        return $c;
    }

    /**
     * Prepare the row for iteration
     * @param xPDOObject $object
     * @return array
     */
    public function prepareRow(xPDOObject $object) {
        $objectArray = $object->toArray();
        $properties = json_decode($objectArray['properties'], 1);
        foreach ($properties as $k => $v) {
            $objectArray['property_' . $k] = $v;
        }
        return $objectArray;
    }

}

return 'ResourceScopesGetListProcessor';
