<?php

$router->get('/list', 'InterestsController@list');
$router->get('/{id:[a-z0-9-]+}', 'InterestsController@get');