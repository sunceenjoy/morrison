<?php

define('DOCROOT', dirname(__DIR__));

$c = new \Morrison\Core\Container();
$c['res_dir'] = DOCROOT.'/res';
$c['log_dir'] = $c['res_dir'].'/logs';
$c['app_dir'] = DOCROOT.'/app';
$c['config_dir'] = $c['app_dir'].'/config';
$c['app_resource_dir'] = $c['app_dir'].'/resources';

require $c['app_resource_dir'].'/core/services.php';

\Morrison\Core\ErrorHandler::register($c['env'], $c['mailer'], $c['config']['debug']);
