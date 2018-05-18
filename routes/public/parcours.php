<?php
define("DBG_MODE", true);
ini_set( 'display_errors', true );
error_reporting( E_ALL | E_NOTICE );
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);

$p = dirname(__FILE__).'/../../app/Http/Controllers/PublicControllers/ParcoursController.php';
require_once($p);

use App\Http\Controllers;
use App\Http\Controllers\Test as Test;



var_dump($p);
var_dump(file_exists($p));
new Test();

exit;

$router->get('/list', 'ParcoursController@list');
$router->get('/{id:[a-z0-9-]+}', 'CitiesController@get');