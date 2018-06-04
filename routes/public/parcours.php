<?php

$router->get('/list', 'ParcoursController@list');
$router->get('/byCityId/{sCityId:[a-z0-9-]+}', 'ParcoursController@byCityId');
$router->get('/byId/{sParcourId:[a-z0-9-]+}', 'ParcoursController@byId');
