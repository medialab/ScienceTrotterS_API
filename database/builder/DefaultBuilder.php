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

    public function table_cities () {
        Schema::dropIfExists('cities');
        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->json('title');
                $table->text('image');
                $table->json('geoloc');
                $table->text('force_lang');
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
                $table->uuid('cities_id');
                $table->json('title');
                $table->json('time');
                $table->json('audio');
                $table->json('description');
                $table->text('force_lang');
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
                $table->uuid('cities_id');
                $table->uuid('parcours_id');
                $table->text('header_image');
                $table->json('title');
                $table->text('address');
                $table->json('geoloc');
                $table->json('schedule');
                $table->json('price');
                $table->json('audio');
                $table->json('transport');
                $table->json('audio_script');
                $table->json('galery_image');
                $table->json('bibliography');
                $table->text('force_lang');
                $table->boolean('state');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE interests ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }
}
