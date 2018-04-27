<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Utils\APIControllerUtil as Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Items;
use App\Models\Cities;

class HomeController extends Controller
{

    public function initialize_database() {
        // $this->table_cities();
        // $this->table_users_token();
        $this->table_cities();
        return $this->sendResponse(null, null);
    }

    public function initialize_database_mockup() {
        $this->mock_db();
        return $this->sendResponse(null, null);
    }

    public function table_users() {
        if (!Schema::hasTable('users')) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
            Schema::create('users', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->text('firstname');
                $table->text('lastname');
                $table->text('password');
                $table->text('email');
                $table->boolean('state');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE users ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }

    public function table_users_token() {
        if (!Schema::hasTable('users_token')) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
            Schema::create('users_token', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->text('user');
                $table->text('key');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE users_token ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }

    public function table_cities() {
        if (!Schema::hasTable('cities')) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
            Schema::create('cities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->text('label');
                $table->text('image');
                $table->integer('state');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE cities ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }

    /** MOCKUP -- */

    public function mock_db() {
        Cities::truncate();

        $aCountries = ['Dijon', 'Le Havre', 'Menton', 'Nancy', 'Paris', 'Poitiers', 'Reims'];
        foreach ($aCountries as $dValue) {
            $aCity = new Cities();
            $aCity->label = $dValue;
            $aCity->image = 'http://science-trotters.actu.com/media/cities/'.strtolower(str_replace(' ', '-', $dValue)).'.jpg';
            $aCity->state = 1;
            $aCity->save();
        }
    }

}
