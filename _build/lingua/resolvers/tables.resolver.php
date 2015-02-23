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
 * Resolve creating db tables
 *
 * @package lingua
 * @subpackage build
 */
if ($modx = & $object->xpdo) {
    // http://forums.modx.com/thread/88734/package-version-check#dis-post-489104
    $c = $modx->newQuery('transport.modTransportPackage');
    $c->where(array(
        'workspace' => 1,
        "(SELECT
            `signature`
          FROM {$modx->getTableName('modTransportPackage')} AS `latestPackage`
          WHERE `latestPackage`.`package_name` = `modTransportPackage`.`package_name`
          ORDER BY
             `latestPackage`.`version_major` DESC,
             `latestPackage`.`version_minor` DESC,
             `latestPackage`.`version_patch` DESC,
             IF(`release` = '' OR `release` = 'ga' OR `release` = 'pl','z',`release`) DESC,
             `latestPackage`.`release_index` DESC
          LIMIT 1,1) = `modTransportPackage`.`signature`",
    ));
    $c->where(array(
        'modTransportPackage.signature:LIKE' => '%lingua%',
        'OR:modTransportPackage.package_name:LIKE' => '%lingua%',
        'installed:IS NOT' => null
    ));
    $oldPackage = $modx->getObject('transport.modTransportPackage', $c);

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modelPath = $modx->getOption('core_path') . 'components/lingua/model/';
            $tablePrefix = $modx->getOption('lingua.table_prefix', null, $modx->config[modX::OPT_TABLE_PREFIX] . 'lingua_');
            $modx->addPackage('lingua', $modelPath, $tablePrefix);
            $modx->addExtensionPackage('lingua', '[[++core_path]]components/lingua/model/', array('tablePrefix' => $tablePrefix));

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
            $manager->createObjectContainer('linguaResourceScopes');
            if ($oldPackage && $oldPackage->compareVersion('2.0.0-rc1', '>')) {
                $manager->addField('linguaSiteContent', 'parent', array('after' => 'link_attributes'));
                $manager->addField('linguaSiteContent', 'isfolder', array('after' => 'parent'));
                $manager->addField('linguaSiteContent', 'context_key', array('after' => 'menutitle'));
                $manager->addField('linguaSiteContent', 'content_type', array('after' => 'context_key'));
            }
            if ($manager->createObjectContainer('linguaSiteTmplvarsPatterns')) {
                $defaults = include $modx->getOption('core_path') . 'components/lingua/defaults/default.patterns.php';
                foreach ($defaults as $default) {
                    $default->save();
                }
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            $modx->removeExtensionPackage('lingua');
            break;
    }
}

return true;
