<?php

/**
 * Lingua
 *
 * Copyright 2013 by goldsky <goldsky@virtudraft.com>
 *
 * This file is part of Lingua
 *
 * Resolve creating db tables
 *
 * @package lingua
 * @subpackage build
 */

if ($modx = & $object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $modelPath = $modx->getOption('core_path') . 'components/lingua/model/';
            $modx->addPackage('lingua', $modelPath, 'modx_lingua_');
            $manager = $modx->getManager();
            if (!$manager->createObjectContainer('Langs')) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[Lingua] `Langs` table was unable to be created');
            }
            break;
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;