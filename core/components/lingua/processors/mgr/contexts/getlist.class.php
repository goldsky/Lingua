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
class ContextsGetListProcessor extends modObjectGetListProcessor {

    public $classKey = 'modContext';
    public $languageTopics = array('lingua:cmp');
    public $defaultSortField = 'key';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'lingua.ContextsGetList';

    /**
     * {@inheritDoc}
     * @return boolean
     */
    public function initialize() {
        $this->setProperty('limit', 0);

        return parent::initialize();
    }

    /**
     * Can be used to adjust the query prior to the COUNT statement
     *
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->where(array(
            'key:!=' => 'mgr'
        ));
        return $c;
    }

    /**
     * Return arrays of objects (with count) converted to JSON.
     *
     * The JSON result includes two main elements, total and results. This format is used for list
     * results.
     *
     * @access public
     * @param array $array An array of data objects.
     * @param mixed $count The total number of objects. Used for pagination.
     * @return string The JSON output.
     */
    public function outputArray(array $array,$count = false) {
        if ($count === false) { $count = count($array); }
        return '{"success":true,"message":"","total":"'.$count.'","results":'.$this->modx->toJSON($array).'}';
    }

}

return 'ContextsGetListProcessor';
