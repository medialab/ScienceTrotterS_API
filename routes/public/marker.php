<?php

$router->get('/get/{color:[0-9a-f]+}', 'MarkerController@get');