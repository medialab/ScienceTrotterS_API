<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\Cities;
use App\Utils\CheckerUtil;

class CitiesController extends Controller
{
  protected $bAdmin = false;
  protected $sModelClass = 'Cities';
}