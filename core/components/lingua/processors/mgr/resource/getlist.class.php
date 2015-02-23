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
include_once MODX_CORE_PATH . 'model/modx/processors/resource/getlist.class.php';

class LinguaResourceGetListProcessor extends modResourceGetListProcessor {

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where(array(
                'pagetitle:LIKE' => "$query%"
            ));
        }
        return $c;
    }

    public function beforeIteration(array $list) {
        if ($this->getProperty('combo', false)) {
            $empty = array(
                'id' => 0,
                'pagetitle' => '&nbsp;',
            );
            $list[] = $empty;
        }

        return $list;
    }

    public function prepareRow(xPDOObject $object) {
        $objectArray = parent::prepareRow($object);
        if ($this->getProperty('combo', false)) {
            $objectArray = array(
                'id' => $objectArray['id'],
                'pagetitle' => $objectArray['pagetitle'],
            );
        }

        return $objectArray;
    }

}

return 'LinguaResourceGetListProcessor';
