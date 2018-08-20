<?php
namespace Database\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class UpdateBuilder
{
    public function init () {
        // UPDATE V1
        $this->table_interest_way();

        // UPDATE V2
        $this->table_listen();
        $this->table_credits();
    }
    public function table_interest_way () {
        //Schema::dropIfExists('interest_way');
        if (!Schema::hasTable('interest_way')) {
            Schema::create('interest_way', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('int1');
                $table->uuid('int2');
                $table->float('time');
                $table->boolean('state');
                $table->float('distance');
                $table->json('api_response')->nullable();
            });
            DB::statement('ALTER TABLE interest_way ALTER COLUMN state SET DEFAULT true;');
            DB::statement('ALTER TABLE interest_way ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
            DB::statement('ALTER TABLE interest_way ALTER COLUMN int1 SET DEFAULT uuid_generate_v4();');
            DB::statement('ALTER TABLE interest_way ALTER COLUMN int2 SET DEFAULT uuid_generate_v4();');
        }
    }

    // UPDATE V1
    public function table_interest_way () {
        //Schema::dropIfExists('interest_way');
        if (!Schema::hasTable('interest_way')) {
            Schema::create('interest_way', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('int1');
                $table->uuid('int2');
                $table->float('time');
                $table->boolean('state');
                $table->float('distance');
                $table->json('api_response')->nullable();
            });

            DB::statement('ALTER TABLE interest_way ALTER COLUMN state SET DEFAULT true;');
            DB::statement('ALTER TABLE interest_way ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
            DB::statement('ALTER TABLE interest_way ALTER COLUMN int1 SET DEFAULT uuid_generate_v4();');
            DB::statement('ALTER TABLE interest_way ALTER COLUMN int2 SET DEFAULT uuid_generate_v4();');
        }
    }


    // UPDATE V2
    public function table_listen () {
        Schema::dropIfExists('listen_audio');
        if (!Schema::hasTable('listen_audio')) {
            Schema::create('listen_audio', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->text('lang');
                $table->text('file')->nullable();
                $table->text('app_id');
                $table->uuid('cont_id');
                $table->text('cont_type');
                $table->timestamps();

                $table->unique(['lang', 'app_id', 'cont_type', 'cont_id', 'file']);
            });

            DB::statement('ALTER TABLE listen_audio ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }

    public function table_credits () {
        Schema::dropIfExists('credits');
        if (!Schema::hasTable('credits')) {
            Schema::create('credits', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->json('title');
                $table->json('content')->nullable();
                $table->text('css');
                $table->boolean('state');
                $table->timestamps();

            });

            DB::statement('ALTER TABLE credits ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
        }
    }
}