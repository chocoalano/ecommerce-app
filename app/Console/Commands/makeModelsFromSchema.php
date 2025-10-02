<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\Filesystem;

class MakeModelsFromSchema extends Command
{
    protected $signature = 'make:models-from-schema
                            {--namespace=App\\Models : Namespace for generated models}
                            {--base=App\\Models\\BaseModel : Base model class to extend}
                            {--force : Overwrite existing model files}';

    protected $description = 'Generate Eloquent models for all tables by inspecting the database schema.';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $conn = DB::connection();

        // 1) Ambil semua nama tabel (robust terhadap casing/driver)
        $tables = [];
        try {
            $schema = $conn->getDoctrineSchemaManager();
            $platform = $schema->getDatabasePlatform();
            // Hindari error enum MySQL
            if (method_exists($platform, 'registerDoctrineTypeMapping')) {
                $platform->registerDoctrineTypeMapping('enum', 'string');
            }
            $tables = $schema->listTableNames();
        } catch (\Throwable $e) {
            $driver = $conn->getDriverName();

            if ($driver === 'mysql') {
                $dbName = $conn->getDatabaseName();
                // ALIAS ke 'name' supaya konsisten
                $rows = $conn->select(
                    'SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
                    [$dbName]
                );
                $tables = array_values(array_filter(array_map(fn($r) => $r->name ?? null, $rows)));
            } elseif ($driver === 'sqlite') {
                $rows = $conn->select('SELECT name FROM sqlite_master WHERE type = "table" ORDER BY name');
                $tables = array_values(array_filter(array_map(fn($r) => $r->name ?? null, $rows)));
            } else {
                // Fallback generik: SHOW TABLES lalu ambil value pertama
                $rows = $conn->select('SHOW TABLES');
                $tables = [];
                foreach ($rows as $row) {
                    $arr = (array) $row;
                    if (!empty($arr)) {
                        $tables[] = reset($arr);
                    }
                }
            }
        }

        // 2) Abaikan tabel internal Laravel
        $ignored = [
            'migrations',
            'failed_jobs',
            'jobs',
            'job_batches',
            'password_resets',
            'cache',
            'cache_locks',
            'sessions',
            'personal_access_tokens',
        ];

        // 3) Ambil kolom per tabel untuk infer relasi
        $tableColumns = [];
        foreach ($tables as $t) {
            if (in_array($t, $ignored, true)) {
                continue;
            }
            $tableColumns[$t] = $this->getColumns($t);
        }

        // 4) Siapkan namespace & base class
        $namespace = trim($this->option('namespace'), '\\');
        $baseClass = trim($this->option('base'), '\\');
        $baseClassShort = class_basename($baseClass);

        // 5) Pastikan direktori model ada
        $dir = base_path(str_replace('App\\', 'app/', $namespace));
        if (!$this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }

        // 6) Generate per tabel
        foreach (array_keys($tableColumns) as $table) {
            $modelName = Str::studly(Str::singular($table));
            $path = $dir . "/{$modelName}.php";

            if ($this->files->exists($path) && !$this->option('force')) {
                $this->warn("Skip {$modelName} (exists). Use --force to overwrite.");
                continue;
            }

            $columns = $tableColumns[$table] ?? [];
            $usesSoftDeletes = array_key_exists('deleted_at', $columns);

            // $fillable: semua kolom selain PK/timestamps/softdeletes
            $fillable = array_values(array_filter(array_keys($columns), function ($c) {
                return !in_array($c, ['id', 'created_at', 'updated_at', 'deleted_at'], true);
            }));

            // casts
            $casts = $this->inferCasts($columns);

            // timestamps
            $hasCreated = array_key_exists('created_at', $columns);
            $hasUpdated = array_key_exists('updated_at', $columns);
            $timestamps = $hasCreated && $hasUpdated;

            // belongsTo dari *_id
            $belongsTo = [];
            foreach ($columns as $name => $type) {
                if (Str::endsWith($name, '_id')) {
                    $relatedTable = Str::plural(Str::beforeLast($name, '_id'));
                    $relatedModel = Str::studly(Str::singular($relatedTable));
                    $method = Str::camel(Str::beforeLast($name, '_id'));
                    $belongsTo[$method] = $relatedModel;
                }
            }

            // hasMany: tabel lain yang punya {singular_table}_id
            $hasMany = [];
            foreach ($tableColumns as $otherTable => $otherColumns) {
                if ($otherTable === $table) {
                    continue;
                }
                $fkName = Str::singular($table) . '_id';
                if (array_key_exists($fkName, $otherColumns)) {
                    $relatedModel = Str::studly(Str::singular($otherTable));
                    $method = Str::camel(Str::plural(Str::singular($otherTable)));
                    $hasMany[$method] = [$relatedModel, $fkName];
                }
            }

            // pivot: tidak ada 'id' & tepat 2 kolom *_id
            $idCols = array_values(array_filter(array_keys($columns), fn($c) => Str::endsWith($c, '_id')));
            $isPivot = !array_key_exists('id', $columns) && count($idCols) === 2;

            // render & tulis file
            $content = $this->renderModel(
                $namespace,
                $modelName,
                $table,
                $baseClass,
                $baseClassShort,
                $timestamps,
                $usesSoftDeletes,
                $fillable,
                $casts,
                $belongsTo,
                $hasMany,
                $isPivot
            );

            $this->files->put($path, $content);
            $this->info("Generated: {$modelName}");
        }

        $this->line("\nDone. Review \$fillable and relationships for accuracy.");
        $this->line("Tip: tighten security by replacing guarded=[] with explicit \$fillable in each model.");

        return self::SUCCESS;
    }

    /**
     * Ambil daftar kolom untuk sebuah tabel (type disederhanakan).
     * @return array<string,string>
     */
    protected function getColumns(string $table): array
    {
        $conn = DB::connection();
        $driver = $conn->getDriverName();

        // Coba Doctrine (paling akurat)
        try {
            $schema = $conn->getDoctrineSchemaManager();
            $details = $schema->listTableColumns($table);
            $out = [];
            foreach ($details as $name => $col) {
                $out[$name] = strtolower((string) $col->getType());
            }
            return $out;
        } catch (\Throwable $e) {
            // Lanjut ke fallback
        }

        // Fallback MySQL: information_schema
        if ($driver === 'mysql') {
            $db = $conn->getDatabaseName();
            $rows = $conn->select(
                'SELECT COLUMN_NAME AS name, DATA_TYPE AS dtype FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
                [$db, $table]
            );
            $out = [];
            foreach ($rows as $r) {
                $out[$r->name] = strtolower($r->dtype);
            }
            return $out;
        }

        // Fallback terakhir: DESCRIBE (MySQL-like)
        try {
            $rows = $conn->select("DESCRIBE `$table`");
            $out = [];
            foreach ($rows as $r) {
                $arr = (array) $r;
                // Field / Type keys bisa beda casing; normalisasi
                $field = $arr['Field'] ?? $arr['field'] ?? null;
                $type  = $arr['Type']  ?? $arr['type']  ?? null;
                if ($field) {
                    $out[$field] = strtolower((string) $type);
                }
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Infer casts yang masuk akal dari tipe kolom.
     * @param array<string,string> $columns
     * @return array<string,string>
     */
    protected function inferCasts(array $columns): array
    {
        $casts = [];
        foreach ($columns as $name => $type) {
            $t = strtolower($type ?? '');
            if (in_array($name, ['created_at', 'updated_at', 'deleted_at'], true)) {
                $casts[$name] = 'datetime';
                continue;
            }
            if (Str::contains($t, 'json')) {
                $casts[$name] = 'array';
            } elseif (Str::contains($t, ['tinyint(1)', 'bool'])) {
                $casts[$name] = 'boolean';
            } elseif (Str::contains($t, 'int')) {
                $casts[$name] = 'integer';
            } elseif (Str::contains($t, ['decimal', 'double', 'float'])) {
                $casts[$name] = 'float';
            } elseif (Str::contains($t, ['date', 'time'])) {
                $casts[$name] = 'datetime';
            }
        }
        return $casts;
    }

    /**
     * Render isi file model.
     */
    protected function renderModel(
        string $namespace,
        string $modelName,
        string $table,
        string $baseClass,
        string $baseClassShort,
        bool $timestamps,
        bool $usesSoftDeletes,
        array $fillable,
        array $casts,
        array $belongsTo,
        array $hasMany,
        bool $isPivot
    ): string {
        $useSoft = $usesSoftDeletes ? "\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;" : '';
        $softTrait = $usesSoftDeletes ? "\n    use SoftDeletes;" : '';
        $tableLine = "\n    protected \$table = '$table';";
        $timestampsLine = $timestamps ? '' : "\n    public \$timestamps = false;";
        $fillableLine = empty($fillable) ? '' : "\n    protected \$fillable = [\n        '" . implode("','", $fillable) . "'\n    ];";
        $castsLine = '';
        if (!empty($casts)) {
            $pairs = [];
            foreach ($casts as $k => $v) {
                $pairs[] = "        '$k' => '$v',";
            }
            $castsLine = "\n    protected \$casts = [\n" . implode("\n", $pairs) . "\n    ];";
        }

        // relasi
        $relCode = '';
        foreach ($belongsTo as $method => $related) {
            $relCode .= "\n    public function $method()\n    {\n        return \$this->belongsTo($related::class);\n    }\n";
        }
        foreach ($hasMany as $method => [$related, $fk]) {
            $relCode .= "\n    public function $method()\n    {\n        return \$this->hasMany($related::class, '$fk');\n    }\n";
        }

        $pivotDoc = $isPivot ? "\n    // Pivot table detected: consider defining belongsToMany() on the related models." : '';

        $content = <<<PHP
<?php

namespace $namespace;

use $baseClass;$useSoft

class $modelName extends $baseClassShort
{
    $softTrait$tableLine$timestampsLine$fillableLine$castsLine$pivotDoc
$relCode
}
PHP;
        return $content;
    }
}
