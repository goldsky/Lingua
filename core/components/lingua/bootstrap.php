<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

// Load the classes
$modx->addPackage('Lingua\Model', $namespace['path'] . 'src/', null, 'Lingua\\');

$modx->services->add('Lingua', function ($c) use ($modx) {
    return new Lingua\Lingua($modx);
});
