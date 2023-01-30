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
 * Resolve change context setting for MODX3
 *
 * @package lingua
 * @subpackage build
 */

/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */
/** @var  MODX\Revolution\modX $modx */

use MODX\Revolution\modSystemSetting;
use MODX\Revolution\modContextSetting;

if ($transport->xpdo) {
    $modx = $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $systemSetting = $modx->getObject(modSystemSetting::class, [
                'key' => 'modRequest.class',
                'value' => 'LinguaRequest'
            ]);
            if ($systemSetting) {
                foreach ($systemSetting as $setting) {
                    $setting->set('value', 'Lingua\Model\LinguaRequest');
                    $setting->save();
                }
                $modx->log(modX::LOG_LEVEL_INFO, "Fixed system setting modRequest.class");
            }

            $contextSettings = $modx->getCollection(modContextSetting::class, [
                'key' => 'modRequest.class',
                'value' => 'LinguaRequest'
            ]);
            if ($contextSettings) {
                foreach ($contextSettings as $setting) {
                    $setting->set('value', 'Lingua\Model\LinguaRequest');
                    $setting->save();
                }
                $modx->log(modX::LOG_LEVEL_INFO, "Fixed context settings modRequest.class");
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;
