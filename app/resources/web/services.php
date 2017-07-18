<?php

$c['router.routes'] = function ($c) {
    $routes = require $c['app_resource_dir'].'/web/routing.php';
    return $routes;
};

$c['controller_resolver'] = function ($c) {
    return new \Morrison\Core\HttpKernel\Controller\ControllerResolver($c);
};

$c['router.url_matcher'] = function ($c) {
    return new \Symfony\Component\Routing\Tests\Fixtures\RedirectableUrlMatcher($c['router.routes'], $c['router.request_context']);
};

$c['log.vote_checker'] = function ($c) {
    $logger = new \Monolog\Logger('vote_checker');
    $level  = $c['config']['debug'] ? \Monolog\Logger::DEBUG : \Monolog\Logger::INFO;
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($c['log_dir'].'/vote_checker.log', $level));
    return $logger;
};

$c['vote_checker'] = function ($c) {
    return new \Morrison\Web\Helper\VoteChecker($c['log.vote_checker'], $c['redis']);
};

$c['event_listener.page_not_found'] = function ($c) {
    return new \Morrison\Core\EventListener\ExceptionListener();
};
