<?php

$router->get('/list', 'CitiesController@list');
$router->get('/byId/{sCityId}', 'CitiesController@byId');