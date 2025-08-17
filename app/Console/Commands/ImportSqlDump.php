<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportSqlDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *  php artisan db:import-dump path/to/file.sql --truncate --only=users,gincanas,comentarios
     */
    protected $signature = 'db:import-dump
        {path : Caminho para o arquivo .sql (absoluto ou relativo ao projeto)}
        {--truncate : Apaga dados das tabelas antes de importar}
        {--only= : Lista separada por vírgulas de tabelas a importar (padrão: todas as encontradas)}
        {--skip=migrations : Lista separada por vírgulas de tabelas a pular (padrão: migrations)}';

    /**
     * The console command description.
     */
    protected $description = 'Importa dados (apenas INSERTs) de um dump SQL no banco configurado (opção de truncar tabelas).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $inputPath = $this->argument('path');
        $path = $this->resolvePath($inputPath);

        if (!is_file($path)) {
            $this->error("Arquivo não encontrado: {$path}");
            return Command::FAILURE;
        }

        $onlyList = $this->option('only');
        $only = $onlyList ? collect(explode(',', $onlyList))->map(fn($t) => trim($t))->filter()->values()->all() : null;

        $skipList = $this->option('skip') ?: 'migrations';
        $skip = collect(explode(',', (string) $skipList))->map(fn($t) => trim($t))->filter()->values()->all();

        $this->info('Lendo dump...');
        $sql = file_get_contents($path);
        if ($sql === false) {
            $this->error('Falha ao ler o arquivo.');
            return Command::FAILURE;
        }

        // Extrai apenas INSERTs completos terminados por ponto e vírgula
        $this->info('Extraindo INSERTs...');
        $pattern = '/INSERT\s+INTO\s+`?([A-Za-z0-9_]+)`?\s+.*?;\s*/si';
        if (!preg_match_all($pattern, $sql, $matches)) {
            $this->warn('Nenhum INSERT encontrado no dump. Nada a fazer.');
            return Command::SUCCESS;
        }

        $tablesInDump = collect($matches[1])->map(fn($t) => strtolower($t));
        $uniqueTables = $tablesInDump->unique()->values()->all();

        // Filtra statements por only/skip
        $statements = [];
        foreach ($matches[0] as $idx => $statement) {
            $table = strtolower($matches[1][$idx]);
            if (in_array($table, $skip, true)) {
                continue;
            }
            if ($only && !in_array($table, $only, true)) {
                continue;
            }
            $statements[] = $statement;
        }

        if (empty($statements)) {
            $this->warn('Nenhum INSERT aplicável após filtros only/skip.');
            return Command::SUCCESS;
        }

        // Se solicitado, truncar tabelas em ordem segura (filhas antes dos pais)
        if ($this->option('truncate')) {
            $this->info('Desativando verificações de chave estrangeira e truncando tabelas...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Ordem padrão conhecida (adicione aqui se necessário). Vamos intersectar com as tabelas realmente presentes.
            $truncateOrder = [
                'failed_jobs',
                'jobs',
                'notifications',
                'push_subscriptions',
                'personal_access_tokens',
                'password_reset_tokens',
                'comentarios',
                'mapchat_comment_notifications',
                'gincana_locais',
                'participacoes',
                'gincanas',
                'users',
            ];

            $present = array_values(array_intersect($truncateOrder, $uniqueTables));

            foreach ($present as $table) {
                if (in_array($table, $skip, true)) {
                    continue;
                }
                if ($only && !in_array($table, $only, true)) {
                    continue;
                }
                try {
                    DB::statement('TRUNCATE TABLE `'.$table.'`');
                    $this->line(" - TRUNCATE `{$table}`");
                } catch (\Throwable $e) {
                    $this->warn("Falha ao truncar {$table}: ".$e->getMessage());
                }
            }
        }

        $this->info('Importando dados...');
        $count = 0;
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($statements as $stmt) {
                // Opcionalmente, poderíamos transformar em INSERT IGNORE/REPLACE conforme necessidade.
                DB::unprepared($stmt);
                $count++;
                if ($count % 50 === 0) {
                    $this->line(" - {$count} INSERTs aplicados...");
                }
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            // Reativa FKs por segurança
            try { DB::statement('SET FOREIGN_KEY_CHECKS=1'); } catch (\Throwable $ignored) {}
            $this->error('Erro ao importar: '.$e->getMessage());
            return Command::FAILURE;
        }

        $this->info("Importação concluída. INSERTs aplicados: {$count}.");
        return Command::SUCCESS;
    }

    private function resolvePath(string $inputPath): string
    {
        if (Str::startsWith($inputPath, ['/', './', '../'])) {
            return realpath($inputPath) ?: $inputPath; // pode não existir ainda
        }
        // Tenta em storage/app/dumps e no base_path
        $candidates = [
            storage_path('app/'.$inputPath),
            storage_path('app/dumps/'.$inputPath),
            base_path($inputPath),
        ];
        foreach ($candidates as $c) {
            if (is_file($c)) {
                return $c;
            }
        }
        // como fallback, considere relativo ao base_path
        return base_path($inputPath);
    }
}
