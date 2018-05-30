<?php

$router->get('/list', 'InterestsAdminController@list');
$router->get('/{id:[a-z0-9-]+}', 'InterestsAdminController@get');