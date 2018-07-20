<?php

$router->get('/list', 'InterestsController@list');
$router->get('/closest', 'InterestsController@closest');
$router->get('/{id:[a-z0-9-]+}', 'InterestsController@get');
$router->get('/listen/{id:[a-z0-9-]+}', 'InterestsController@listen');

$router->get('/byParcourId/{sParcourId:[a-z0-9-]+}', 'InterestsController@byParcoursId');
$router->get('/byParcoursId/{sParcourId:[a-z0-9-]+}', 'InterestsController@byParcoursId');
$router->get('/byNoParcours/{sCityId:[a-z0-9-]+}', 'InterestsController@byNoParcours');

$router->get('/byCityId/{sCityId:[a-z0-9-]+}', 'InterestsController@byCityId');
$router->get('/byId/{sInterestId:[a-z0-9-]+}', 'InterestsController@byId');
