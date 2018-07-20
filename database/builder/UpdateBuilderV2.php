<?php
namespace Database\Builder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBuilderV2
{
    public function init () {
        $this->table_listen();
    }

    public function table_listen () {
        Schema::dropIfExists('listen_audio');
        if (!Schema::hasTable('listen_audio')) {
            Schema::create('listen_audio', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->text('lang');
                $table->text('app_id');
                $table->uuid('cont_id');
                $table->text('cont_type');
                $table->timestamps();

                $table->unique(['lang', 'app_id', 'cont_type', 'cont_id']);
            });

            DB::statement('ALTER TABLE listen_audio ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }
}
