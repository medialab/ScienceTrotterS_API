<?php 
namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Interests;

class InterestsAdminController extends InterestsController
{
	protected $bAdmin = true;
}
