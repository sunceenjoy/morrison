<?php
define('WEBROOT', __DIR__);
umask(0000);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
require '../app/bootstrap.php';
require $c['app_resource_dir'].'/web/services.php';

use Morrison\Core\AppKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;

$request = Request::createFromGlobals();

$app = new AppKernel($c);
$response = $app->handle($request, HttpKernel::MASTER_REQUEST, !$c['config']['debug']);
$response->send();
