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
 * Validates before action.
 *
 * @package lingua
 * @subpackage build
 */
if ($modx = & $object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            if ($modx->getDebug()) {
                $modx->log(modX::LOG_LEVEL_WARN, 'validator xPDOTransport::ACTION_INSTALL');
                $modelPath = $modx->getOption('core_path') . 'components/lingua/model/';
                $tablePrefix = $modx->getOption('lingua.table_prefix', null, $modx->config[modX::OPT_TABLE_PREFIX] . 'lingua_');
                if ($modx->addPackage('lingua', $modelPath, $tablePrefix)) {
                    $modx->log(modX::LOG_LEVEL_WARN, '[Lingua] package was added in validator xPDOTransport::ACTION_INSTALL');
                }
            }
            break;
        case xPDOTransport::ACTION_UPGRADE:
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            if ($modx->getDebug()) {
                $modx->log(modX::LOG_LEVEL_WARN, 'validator xPDOTransport::ACTION_UNINSTALL');
            }
            $modelPath = $modx->getOption('core_path') . 'components/lingua/model/';
            $tablePrefix = $modx->getOption('lingua.table_prefix', null, $modx->config[modX::OPT_TABLE_PREFIX] . 'lingua_');
            if ($modx->addPackage('lingua', $modelPath, $tablePrefix)) {
                if ($modx->getDebug()) {
                    $modx->log(modX::LOG_LEVEL_WARN, '[Lingua] package was added in validator xPDOTransport::ACTION_UNINSTALL');
                }
//                $manager = $modx->getManager();
//                $manager->removeObjectContainer('linguaLangs');
//                $manager->removeObjectContainer('linguaSiteContent');
//                $manager->removeObjectContainer('linguaSiteTmplvarContentvalues');
//                $manager->removeObjectContainer('linguaSiteTmplvars');
            }
            break;
    }
}
return true;