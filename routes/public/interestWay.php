<?php

$router->get('/list', 'InterestWayController@list');
$router->get('/closest/{sInterestId:[a-z0-9-]+}', 'InterestWayController@closest');

$router->get('/interest/{sInterestId:[a-z0-9-]+}', 'InterestWayController@byInterest');
$router->get('/interests/{sInterestId1:[a-z0-9-]+}/{sInterestId2:[a-z0-9-]+}', 'InterestWayController@byInterests');