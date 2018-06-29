<?php

$router->get('/list', 'InterestsController@list');
$router->get('/{id:[a-z0-9-]+}', 'InterestsAdminController@get');

$router->get('/byParcourId/{sParcourId:[a-z0-9-]+}', 'InterestsController@byParcoursId');
$router->get('/byParcoursId/{sParcourId:[a-z0-9-]+}', 'InterestsController@byParcoursId');
$router->get('/byNoParcours/{sCityId:[a-z0-9-]+}', 'InterestsController@byNoParcours');

$router->get('/byCityId/{sCityId:[a-z0-9-]+}', 'InterestsController@byCityId');
$router->get('/byId/{sInterestId:[a-z0-9-]+}', 'InterestsController@byId');
