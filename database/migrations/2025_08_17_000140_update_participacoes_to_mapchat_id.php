<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('participacoes')) {
            Schema::table('participacoes', function (Blueprint $table) {
                if (Schema::hasColumn('participacoes', 'gincana_id') && !Schema::hasColumn('participacoes', 'mapchat_id')) {
                    $table->renameColumn('gincana_id', 'mapchat_id');
                }
            });
            // Drop existing FKs safely
            $dropFkIfExists = function (string $tableName, string $column) {
                $constraint = \DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->select('CONSTRAINT_NAME')
                    ->whereRaw('TABLE_SCHEMA = DATABASE()')
                    ->where('TABLE_NAME', $tableName)
                    ->where('COLUMN_NAME', $column)
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->value('CONSTRAINT_NAME');
                if ($constraint) {
                    \DB::statement('ALTER TABLE `'.$tableName.'` DROP FOREIGN KEY `'.$constraint.'`');
                }
            };
            $dropFkIfExists('participacoes', 'mapchat_id');
            $dropFkIfExists('participacoes', 'gincana_id');

            // Ensure FK exists
            $hasFk = (bool) \DB::table('information_schema.KEY_COLUMN_USAGE')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'participacoes')
                ->where('COLUMN_NAME', 'mapchat_id')
                ->where('REFERENCED_TABLE_NAME', 'mapchats')
                ->exists();
            if (!$hasFk) {
                Schema::table('participacoes', function (Blueprint $table) {
                    $table->foreign('mapchat_id')->references('id')->on('mapchats')->cascadeOnDelete();
                });
            }

            // Unique index adjustment
            $uniqueNameOld = 'participacoes_user_id_gincana_id_unique';
            $hasOldUnique = (bool) \DB::table('information_schema.STATISTICS')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'participacoes')
                ->where('INDEX_NAME', $uniqueNameOld)
                ->exists();
            if ($hasOldUnique) {
                Schema::table('participacoes', function (Blueprint $table) use ($uniqueNameOld) {
                    \DB::statement('ALTER TABLE `participacoes` DROP INDEX `'.$uniqueNameOld.'`');
                });
            }
            $hasNewUnique = (bool) \DB::table('information_schema.STATISTICS')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'participacoes')
                ->where('INDEX_NAME', 'participacoes_user_id_mapchat_id_unique')
                ->exists();
            if (!$hasNewUnique) {
                Schema::table('participacoes', function (Blueprint $table) {
                    $table->unique(['user_id','mapchat_id']);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('participacoes')) {
            // Drop FK if exists
            $constraint = \DB::table('information_schema.KEY_COLUMN_USAGE')
                ->select('CONSTRAINT_NAME')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'participacoes')
                ->where('COLUMN_NAME', 'mapchat_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->value('CONSTRAINT_NAME');
            if ($constraint) {
                \DB::statement('ALTER TABLE `participacoes` DROP FOREIGN KEY `'.$constraint.'`');
            }
            // Drop the unique index on (user_id,mapchat_id) if exists
            $hasNewUnique = (bool) \DB::table('information_schema.STATISTICS')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'participacoes')
                ->where('INDEX_NAME', 'participacoes_user_id_mapchat_id_unique')
                ->exists();
            if ($hasNewUnique) {
                \DB::statement('ALTER TABLE `participacoes` DROP INDEX `participacoes_user_id_mapchat_id_unique`');
            }
            Schema::table('participacoes', function (Blueprint $table) {
                if (Schema::hasColumn('participacoes', 'mapchat_id')) {
                    $table->renameColumn('mapchat_id', 'gincana_id');
                }
            });
        }
    }
};
