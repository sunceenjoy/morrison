<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add(
    'portal',
    new Route(
        '/',
        array('_controller' => 'Morrison\Web\Controller\VoteController::index',)
    )
);

$collection->add(
    'vote-post',
    new Route(
        '/vote/post',
        array('_controller' => 'Morrison\Web\Controller\VoteController::post',)
    )
);

return $collection;
