<?php

$router->get('/list', 'ParcoursController@list');
$router->get('/length/{id:[a-z0-9-]+}', 'ParcoursController@length');
$router->get('/trace/{id:[a-z0-9-]+}', 'ParcoursController@trace');

$router->get('/{id:[a-z0-9-]+}', 'ParcoursController@get');
$router->get('/byCityId/{sCityId:[a-z0-9-]+}', 'ParcoursController@byCityId');
$router->get('/byId/{sParcourId:[a-z0-9-]+}', 'ParcoursController@byId');
