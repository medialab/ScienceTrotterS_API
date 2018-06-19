<?php

namespace Database\Builder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultBuilder
{
    public function init () {
        $this->initialize_uuid();
        $this->table_users();
        $this->table_users_token();
        $this->table_cities();
        $this->table_parcours();
        $this->table_interests();
        $this->table_colors();
    }

    public function initialize_uuid () {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
    }

    public function table_users () {
        Schema::dropIfExists('users');
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->text('firstname');
                $table->text('lastname');
                $table->text('email')->unique();
                $table->text('password')->unique();
                $table->boolean('state');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE users ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }

    public function table_users_token () {
        Schema::dropIfExists('users_token');
        if (!Schema::hasTable('users_token')) {
            Schema::create('users_token', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->text('user');
                $table->text('key');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE users_token ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }

    public function table_colors () {
        Schema::dropIfExists('colors');
        if (!Schema::hasTable('colors')) {
            Schema::create('colors', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->text('name');
                $table->text('color');
            });
            DB::statement('ALTER TABLE colors ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }

    public function table_cities () {
        Schema::dropIfExists('cities');
        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->json('title');
                $table->text('image')->nullable();
                $table->json('geoloc')->nullable();
                $table->text('force_lang')->nullable();
                $table->boolean('state');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE cities ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }

    public function table_parcours () {
        Schema::dropIfExists('parcours');
        if (!Schema::hasTable('parcours')) {
            Schema::create('parcours', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('cities_id')->nullable();
                $table->json('title');
                $table->json('time')->nullable();
                $table->json('audio')->nullable();
                $table->text('color')->nullable();
                $table->json('description')->nullable();
                $table->text('force_lang')->nullable();
                $table->boolean('state');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE parcours ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }

    public function table_interests () {
        Schema::dropIfExists('interests');
        if (!Schema::hasTable('interests')) {
            Schema::create('interests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('cities_id')->nullable();
                $table->uuid('parcours_id')->nullable();
                $table->text('header_image')->nullable();
                $table->json('title');
                $table->text('address')->nullable();
                $table->json('geoloc')->nullable();
                $table->json('schedule')->nullable();
                $table->json('price')->nullable();
                $table->json('audio')->nullable();
                $table->json('transport')->nullable();
                $table->json('audio_script')->nullable();
                $table->json('gallery_image')->nullable();
                $table->json('bibliography')->nullable();
                $table->text('force_lang')->nullable();
                $table->boolean('state');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE interests ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }
}
