<?php

$router->get('/list', 'ParcoursController@list');
$router->get('/{id:[a-z0-9-]+}', 'ParcoursController@get');
$router->get('/byCityId/{sCityId:[a-z0-9-]+}', 'ParcoursController@byCityId');
$router->get('/byId/{sParcourId:[a-z0-9-]+}', 'ParcoursController@byId');
