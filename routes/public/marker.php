<?php

$router->get('/get/{color:[0-9a-fA-F]+}', 'MarkerController@get');