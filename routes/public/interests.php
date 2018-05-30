<?php

$router->get('/list', 'PublicControllers/InterestsController@list');
$router->get('/{id:[a-z0-9-]+}', 'PublicControllers/InterestsController@get');