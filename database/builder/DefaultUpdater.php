<?php

namespace Database\Builder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBuilder
{
    public function init () {
        $this->table_interests();
    }

    public function table_interests () {
        Schema::table('interests', function (Blueprint $table) {
            $table->json('distances')->nullable();
        });
    }
}
