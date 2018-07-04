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
        $this->table_interest_way();
    }

    public function table_interests () {
        Schema::table('interests', function (Blueprint $table) {
            /*$table->json('distances')->nullable();
            $columns = $table->getColumns();
            $bFound = false;
            foreach ($columns as $oCol) {
                if ($oCol->name === 'distances') {
                    # code...
                }
            }
            
            exit;*/
        });
    }

    public function table_interest_way () {
        Schema::dropIfExists('interest_way');
        if (!Schema::hasTable('interest_way')) {
            Schema::create('interest_way', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('int1');
                $table->uuid('int2');
                $table->float('time');
                $table->float('distance');
            });

            DB::statement('ALTER TABLE interest_way ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
            DB::statement('ALTER TABLE interest_way ALTER COLUMN int1 SET DEFAULT uuid_generate_v4();');
            DB::statement('ALTER TABLE interest_way ALTER COLUMN int2 SET DEFAULT uuid_generate_v4();');
        }
    }
}