<?php

/**
 * Lingua
 *
 * Copyright 2013-2014 by goldsky <goldsky@virtudraft.com>
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
include_once MODX_CORE_PATH . 'model/modx/processors/element/tv/getlist.class.php';

class TVComboGetListProcessor extends modTemplateVarGetListProcessor {

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->leftJoin('modCategory', 'Category');
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where(array(
                'name:LIKE' => $query . '%',
            ));
        }
        $excludeExisting = $this->getProperty('excludeExisting');
        if ($excludeExisting) {
            $tvs = $this->modx->getCollection('linguaSiteTmplvars');
            if ($tvs) {
                $ids = array();
                foreach ($tvs as $tv) {
                    $ids[] = $tv->get('tmplvarid');
                }
                $c->where(array(
                    'id:NOT IN' => $ids,
                ));
            }
        }
        return $c;
    }

}

return 'TVComboGetListProcessor';
