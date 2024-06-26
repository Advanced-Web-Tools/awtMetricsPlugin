<?php

use settings\settings;

use admin\navbar;

$metrics = new navbar;

$settings = new settings;

$name = 'awtMetrics';
$version = "1.2.0";
$src = PLUGINS . $name . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
$enginePath = $src . 'awtMetrics.php';
$dependenciesPath = $src . 'awtMetrics.class.php';

include_once $dependenciesPath;

addDependency('high', $name, $dependenciesPath, $version);

addEngine($name, $enginePath, $version, 'start');

$pluginPages['awtMetrics'] = PLUGINS . 'awtMetrics' . DIRECTORY_SEPARATOR . 'plugin.page.php';

if (function_exists('navbarLoader')) {
    $location = HOSTNAME . 'awt-content/plugins/awtMetrics/data/icons/';

    $metrics->addItem(array('icon' => $location . 'chart-line-solid.svg', 'name' => 'Metrics', 'link' => HOSTNAME . 'awt-admin/?page=awtMetrics'));

    array_push($navbar, $metrics);
}



if (isset($api_executors)) {
    addToApiExecution("getViewes", $class = new awtMetrics);
}


if (!$settings->checkIfSettingExists("Show Metrics widgets")) {
    $settings->createSetting("Show Metrics widgets", "true");
    $settings->fetchSettings();
}


if (function_exists('loadAllWidgets') && defined("DASHBOARD") && $settings->getSettingsValue("Show Metrics widgets") == "true") {
    $widget = array("name" => "Metrics Widget", "src" => $src . "metricsWidget.php");
    pushToWidgets($widget);
}


