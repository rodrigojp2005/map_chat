<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('comentarios')) {
            // Rename legacy column if needed
            Schema::table('comentarios', function (Blueprint $table) {
                if (Schema::hasColumn('comentarios', 'gincana_id') && !Schema::hasColumn('comentarios', 'mapchat_id')) {
                    $table->renameColumn('gincana_id', 'mapchat_id');
                }
            });

            // Drop existing FKs safely (if they exist)
            $dropFkIfExists = function (string $tableName, string $column) {
                $constraint = \DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->select('CONSTRAINT_NAME')
                    ->whereRaw('TABLE_SCHEMA = DATABASE()')
                    ->where('TABLE_NAME', $tableName)
                    ->where('COLUMN_NAME', $column)
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->value('CONSTRAINT_NAME');
                if ($constraint) {
                    Schema::table($tableName, function (Blueprint $table) use ($constraint) {
                        // Laravel needs the constraint name to drop; use raw since name may differ
                        \DB::statement('ALTER TABLE `'.$table->getTable().'` DROP FOREIGN KEY `'.$constraint.'`');
                    });
                }
            };

            $dropFkIfExists('comentarios', 'mapchat_id');
            $dropFkIfExists('comentarios', 'gincana_id');

            // Ensure FK to mapchats exists
            $hasFkToMapchats = (bool) \DB::table('information_schema.KEY_COLUMN_USAGE')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'comentarios')
                ->where('COLUMN_NAME', 'mapchat_id')
                ->where('REFERENCED_TABLE_NAME', 'mapchats')
                ->exists();

            if (!$hasFkToMapchats) {
                Schema::table('comentarios', function (Blueprint $table) {
                    $table->foreign('mapchat_id')->references('id')->on('mapchats')->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('comentarios')) {
            // Drop FK if exists
            $constraint = \DB::table('information_schema.KEY_COLUMN_USAGE')
                ->select('CONSTRAINT_NAME')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'comentarios')
                ->where('COLUMN_NAME', 'mapchat_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->value('CONSTRAINT_NAME');
            if ($constraint) {
                Schema::table('comentarios', function (Blueprint $table) use ($constraint) {
                    \DB::statement('ALTER TABLE `'.$table->getTable().'` DROP FOREIGN KEY `'.$constraint.'`');
                });
            }

            Schema::table('comentarios', function (Blueprint $table) {
                if (Schema::hasColumn('comentarios', 'mapchat_id') && !Schema::hasColumn('comentarios', 'gincana_id')) {
                    $table->renameColumn('mapchat_id', 'gincana_id');
                }
            });
        }
    }
};
