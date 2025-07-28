<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('MODX_API_MODE', true);
require_once dirname(dirname(__DIR__)) . '/config.core.php';

// Проверка существования MODX_CONFIG_KEY
if (!defined('MODX_CONFIG_KEY')) {
    define('MODX_CONFIG_KEY', 'config');
}

require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

define('PKG_NAME', 'cookieconsent');
define('PKG_NAME_LOWER', 'cookieconsent');
define('PKG_VERSION', '1.0.0');
define('PKG_RELEASE', 'pl');

$root = dirname(__DIR__) . '/';
$sources = [
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'resolvers' => $root . '_build/resolvers/',
    'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
    'source_assets' => $root . 'assets/components/' . PKG_NAME_LOWER,
];

require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/');

$category = $modx->newObject('modCategory');
$category->set('category', PKG_NAME);

$chunks = include $sources['data'] . 'transport.chunks.php';
$category->addMany($chunks, 'Chunks');

$plugins = include $sources['data'] . 'transport.plugins.php';
$category->addMany($plugins, 'Plugins');


$vehicle = $builder->createVehicle($category, [
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
        'Chunks' => [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ],
        'Plugins' => [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
                'PluginEvents' => [
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'], // ключ к успешной установке!
                ]
            ]
        ]
    ]
]);


if ($vehicle->resolve('php', array('source' => $sources['resolvers'] . 'resolver.settings.php'))) {
    $modx->log(modX::LOG_LEVEL_INFO,'Added resolver resolver.settings.php to category.');
}
else {
    $modx->log(modX::LOG_LEVEL_INFO,'Could not add resolver resolver.settings.php to category.');
}

if ($vehicle->resolve('php', array('source' => $sources['resolvers'] . 'resolver.clientconfig.php'))) {
	$modx->log(modX::LOG_LEVEL_INFO,'Added resolver resolver.clientconfig.php to category.');
}
else {
	$modx->log(modX::LOG_LEVEL_INFO,'Could not add resolver resolver.clientconfig.php to category.');
}


$builder->putVehicle($vehicle);

// Compile SCSS
$scss_compiler = 'sass';
$scss_file = $sources['source_assets'] . '/css/cookieconsent.scss';
$css_file = $sources['source_assets'] . '/css/cookieconsent.css';
shell_exec("$scss_compiler $scss_file $css_file");


$builder->setPackageAttributes([
    'changelog' => file_get_contents($sources['source_core'] . '/docs/' . 'changelog.txt'),
    'license' => file_get_contents($sources['source_core'] . '/docs/' . 'license.txt'),
    'readme' => file_get_contents($sources['source_core'] . '/docs/' . 'readme.txt'),
]);


$assetsVehicle = $builder->createVehicle([
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH . 'components/';",
], [
    'vehicle_class' => 'xPDOFileVehicle',
]);
$builder->putVehicle($assetsVehicle);

$builder->pack();