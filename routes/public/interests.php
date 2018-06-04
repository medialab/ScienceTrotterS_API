<?php

$router->get('/list', 'InterestsController@list');
$router->get('/byParcourId/{sParcourId:[a-z0-9-]+}', 'InterestsController@byParcourId');
$router->get('/byCityId/{sCityId:[a-z0-9-]+}', 'InterestsController@byCityId');
$router->get('/byId/{sInterestId:[a-z0-9-]+}', 'InterestsController@byId');
