<?php

/**
 * Lingua
 *
 * Copyright 2013 by goldsky <goldsky@virtudraft.com>
 *
 * This file is part of Lingua
 *
 * Lingua build script
 *
 * @package lingua
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* define version */
define('PKG_NAME', 'Lingua');
define('PKG_NAME_LOWER', 'lingua');
define('PKG_VERSION', '1.0.0');
define('PKG_RELEASE', 'dev.2');

/* override with your own defines here (see build.config.sample.php) */
require_once dirname(__FILE__) . '/build.config.php';
require_once realpath(MODX_CORE_PATH) . '/model/modx/modx.class.php';

/* define sources */
$root = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
$sources = array(
	'root' => $root,
	'build' => BUILD_PATH,
	'resolvers' => BUILD_PATH . 'resolvers' . DIRECTORY_SEPARATOR,
	'data' => BUILD_PATH . 'data' . DIRECTORY_SEPARATOR,
	'properties' => realpath(BUILD_PATH . 'data/properties/') . DIRECTORY_SEPARATOR,
	'source_core' => realpath(MODX_CORE_PATH . 'components') . DIRECTORY_SEPARATOR . PKG_NAME_LOWER,
	'source_assets' => realpath(MODX_ASSETS_PATH . 'components') . DIRECTORY_SEPARATOR . PKG_NAME_LOWER,
	'docs' => realpath(MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER . '/docs/') . DIRECTORY_SEPARATOR,
	'lexicon' => realpath(MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER . '/lexicon/') . DIRECTORY_SEPARATOR,
);
unset($root);

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
echo '<pre>';

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/');

/* create category */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', 'Lingua');

/* add snippets */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding in snippets.');
$snippets = include $sources['data'] . 'transport.snippets.php';
if (is_array($snippets)) {
	$category->addMany($snippets);
} else {
	$modx->log(modX::LOG_LEVEL_FATAL, 'Adding snippets failed.');
}

/* add plugins */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding in plugins.');
$plugins = include $sources['data'] . 'transport.plugins.php';
if (is_array($plugins)) {
	$category->addMany($plugins);
} else {
	$modx->log(modX::LOG_LEVEL_FATAL, 'Adding plugins failed.');
}

/* create category vehicle */
$attr = array(
	xPDOTransport::UNIQUE_KEY => 'category',
	xPDOTransport::PRESERVE_KEYS => false,
	xPDOTransport::UPDATE_OBJECT => true,
	xPDOTransport::RELATED_OBJECTS => true,
	xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
		'Snippets' => array(
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'name',
		),
		'Chunks' => array(
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'name',
		),
		'Plugins' => array(
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'name',
		),
		'PluginEvents' => array(
			xPDOTransport::PRESERVE_KEYS => true,
			xPDOTransport::UPDATE_OBJECT => false,
			xPDOTransport::UNIQUE_KEY => array('pluginid', 'event'),
		),
	)
);
$vehicle = $builder->createVehicle($category, $attr);
$vehicle->resolve('file', array(
	'source' => $sources['source_core'],
	'target' => "return MODX_CORE_PATH . 'components/';",
));
$vehicle->resolve('file', array(
	'source' => $sources['source_assets'],
	'target' => "return MODX_ASSETS_PATH . 'components/';",
));
$builder->putVehicle($vehicle);

/* load system settings */
$settings = include $sources['data'] . 'transport.settings.php';
if (!is_array($settings)) {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in settings.');
} else {
	$attributes = array(
		xPDOTransport::UNIQUE_KEY => 'key',
		xPDOTransport::PRESERVE_KEYS => true,
		xPDOTransport::UPDATE_OBJECT => false,
	);
	foreach ($settings as $setting) {
		$vehicle = $builder->createVehicle($setting, $attributes);
		$builder->putVehicle($vehicle);
	}
	$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' System Settings.');
}
unset($settings, $setting, $attributes);

$modx->log(modX::LOG_LEVEL_INFO, 'Adding in PHP resolvers...');
flush();
$vehicle->resolve('php', array(
	'source' => $sources['resolvers'] . 'tables.resolver.php',
));

$builder->putVehicle($vehicle);
$modx->log(modX::LOG_LEVEL_INFO, 'Adding in PHP resolvers done.');
flush();

$modx->log(modX::LOG_LEVEL_INFO, 'Adding in Default contents ...');
flush();
$modelPath = $modx->getOption('core_path') . 'components/lingua/model/';
$modelPath = realpath($modelPath) . DIRECTORY_SEPARATOR;
$modx->addPackage('lingua', $modelPath, 'modx_lingua_');
$langs = include $sources['data'] . 'transport.langs.php';
if (!is_array($langs)) {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in language.');
} else {
	$attributes = array(
		xPDOTransport::UNIQUE_KEY => array('lang_code', 'lcid_string', 'lcid_dec'),
		xPDOTransport::PRESERVE_KEYS => true,
		xPDOTransport::UPDATE_OBJECT => false,
	);
	foreach ($langs as $lang) {
		$vehicle = $builder->createVehicle($lang, $attributes);
		$builder->putVehicle($vehicle);
	}
	$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($langs) . ' Languages.');
	unset($langs, $lang, $attributes);
}
$modx->log(modX::LOG_LEVEL_INFO, 'Adding in Default contents done.');
flush();

$modx->log(modX::LOG_LEVEL_INFO, 'Packaging in menu...');
$menu = include $sources['data'] . 'transport.menu.php';
if (empty($menu)) {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in menu.');
} else {
	$vehicle = $builder->createVehicle($menu, array(
		xPDOTransport::PRESERVE_KEYS => true,
		xPDOTransport::UPDATE_OBJECT => true,
		xPDOTransport::UNIQUE_KEY => 'text',
		xPDOTransport::RELATED_OBJECTS => true,
		xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
			'Action' => array(
				xPDOTransport::PRESERVE_KEYS => false,
				xPDOTransport::UPDATE_OBJECT => true,
				xPDOTransport::UNIQUE_KEY => array('namespace', 'controller'),
			),
		),
	));
	$modx->log(modX::LOG_LEVEL_INFO, 'Adding in PHP resolvers...');
	$builder->putVehicle($vehicle);
	unset($vehicle, $menu);
}


/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
	'license' => file_get_contents($sources['docs'] . 'license.txt'),
	'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
	'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
));

$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "\n<br />" . PKG_NAME . " package built.<br />\nExecution time: {$totalTime}\n");

exit();