<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AutoIndexTables extends Command
{
    protected $signature = 'db:auto-index';
    protected $description = 'Automatically add missing indexes to all tables';

    public function handle()
    {
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . DB::getDatabaseName();

        foreach ($tables as $tableObj) {
            $table = $tableObj->$key;

            if (!Schema::hasTable($table)) {
                $this->warn("⚠️ Skipping {$table}: base table not found.");
                continue;
            }

            $columns = Schema::getColumnListing($table);

            foreach ($columns as $column) {
                if (preg_match('/_id$/', $column) || preg_match('/_code$/', $column)) {
                    $indexName = substr("idx_{$table}_{$column}", 0, 60);
                    try {
                        $existingIndexes = DB::select("SHOW INDEX FROM {$table}");
                    } catch (\Throwable $e) {
                        $this->warn("⚠️ Skipping {$table}.{$column}: cannot inspect indexes ({$e->getMessage()}).");
                        continue;
                    }
                    
                    $alreadyExists = collect($existingIndexes)
                        ->pluck('Key_name')
                        ->contains($indexName);

                    if (!$alreadyExists) {
                        DB::statement("ALTER TABLE {$table} ADD INDEX {$indexName} ({$column})");
                        $this->info("✅ Indexed: {$table}.{$column}");
                    } else {
                        $this->line("ℹ️ Already indexed: {$table}.{$column}");
                    }
                }
            }
        }

        $this->info('🎯 All possible foreign/id/code fields are now indexed!');
        return Command::SUCCESS;
    }
}