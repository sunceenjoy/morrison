<?php

$c['log.send_email'] = function ($c) {
    $logger = new \Monolog\Logger('send_email');
    $level  = $c['config']['debug'] ? \Monolog\Logger::DEBUG : \Monolog\Logger::INFO;
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($c['log_dir'].'/send_email.log', $level));
    return $logger;
};
