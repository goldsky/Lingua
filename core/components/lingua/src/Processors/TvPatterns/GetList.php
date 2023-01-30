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
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOQuery;
use xPDO\Om\xPDOObject;

class GetList extends GetListProcessor {

    public $classKey = LinguaSiteTmplvarsPatterns::class;
    public $languageTopics = array('lingua:cmp');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'lingua.TVPattersGetList';

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where(array(
                'type:LIKE' => $query . '%',
                'OR:search:LIKE' => '%' . $query . '%',
                'OR:replacement:LIKE' => '%' . $query . '%',
            ));
        }
        return $c;
    }

    /**
     * Get the data of the query
     * @return array
     */
    public function getData() {
        $data = array();
        $limit = intval($this->getProperty('limit'));
        $start = intval($this->getProperty('start'));
        $sortKey = $this->getProperty('sort');

        /* query for chunks */
        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);
        $data['total'] = $this->modx->getCount($this->classKey,$c);
        $c = $this->prepareQueryAfterCount($c);
        
        /* make more convenient sorting */
        if (!empty($sortKey) && $sortKey != 'id') {
            $c->sortby('type',$this->getProperty('dir'));
            $c->sortby('search',$this->getProperty('dir'));
            $c->sortby($sortKey,$this->getProperty('dir'));
        } else {
            $c->sortby('id',$this->getProperty('dir'));
        }
        
        if ($limit > 0) {
            $c->limit($limit,$start);
        }

        $data['results'] = $this->modx->getCollection($this->classKey,$c);
        return $data;
    }

}
