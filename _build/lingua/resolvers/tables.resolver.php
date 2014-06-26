<?php

/**
 * Lingua
 *
 * Copyright 2013 by goldsky <goldsky@virtudraft.com>
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
 * Resolve creating db tables
 *
 * @package lingua
 * @subpackage build
 */
if ($modx = & $object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modelPath = $modx->getOption('core_path') . 'components/lingua/model/';
            $tablePrefix = $modx->getOption('lingua.table_prefix', null, $modx->config[modX::OPT_TABLE_PREFIX] . 'lingua_');
            $modx->addPackage('lingua', $modelPath, $tablePrefix);
            $manager = $modx->getManager();
            if ($manager->createObjectContainer('linguaLangs')) {
                $defaults = include $modx->getOption('core_path') . 'components/lingua/defaults/default.langs.php';
                foreach ($defaults as $default) {
                    $default->save();
                }
            }
            $manager->createObjectContainer('linguaSiteContent');
            $manager->createObjectContainer('linguaSiteTmplvarContentvalues');
            $manager->createObjectContainer('linguaSiteTmplvars');
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;