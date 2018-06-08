<?php

$router->post('/login', 'UsersController@login');
$router->get('/logout', 'UsersController@logout');