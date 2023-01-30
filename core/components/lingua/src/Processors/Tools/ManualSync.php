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

namespace Lingua\Processors\Tools;

use MODX\Revolution\modResource;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOQuery;
use xPDO\Om\xPDOObject;

class ManualSync extends GetListProcessor {

    public $classKey = modResource::class;
    public $languageTopics = array('lingua:cmp');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'lingua.ToolManualSync';

    /**
     * {@inheritDoc}
     * @return boolean
     */
    public function initialize() {
        // Apache's timeout: 600 secs
        if (function_exists('ini_get') && !ini_get('safe_mode')) {
            if (function_exists('set_time_limit')) {
                set_time_limit(600);
            }
            if (function_exists('ini_set')) {
                if (ini_get('max_execution_time') !== 600) {
                    ini_set('max_execution_time', 600);
                }
            }
        }

        $props = $this->getProperties();
        if (empty($props['ids'])) {
            return $this->modx->lexicon($this->objectType . '_err_ns');
        }
        $this->setProperty('ids', json_decode($props['ids']), true);
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
            'id:IN' => $this->getProperty('ids')
        ));
        return $c;
    }

    /**
     * Prepare the row for iteration
     * @param xPDOObject $object
     * @return array
     */
    public function prepareRow(xPDOObject $object) {
        $Lingua = $this->modx->services->get('Lingua');
        $Lingua->synchronize($object);
        return $object->toArray();
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
    public function outputArray(array $array, $count = false) {
        if ($count === false) {
            $count = count($array);
        }
        $cacheManager = $this->modx->getCacheManager();
        $cacheManager->refresh(array(
            'lingua/resource' => array(),
        ));
        $this->modx->error->total = $count;
        return $this->success($this->modx->lexicon('lingua.sync_suc'), array(
                    'total' => $count,
                    'results' => $array,
                    'totalUpdated' => $count,
                    'nextStart' => (int) $this->getProperty('start') + (int) $this->getProperty('limit'),
        ));
    }

}

return 'ToolManualSyncProcessor';
