<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeCustomModel extends GeneratorCommand
{
    protected $name = 'make:custom-model';
    protected $description = 'Membuat Model dengan tableName, primaryKey, fillable, dan migration otomatis';
    protected $type = 'Model';
    protected $additionalData;

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    protected function getStub()
    {
        return base_path('stubs/model.custom.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Models';
    }

    public function handle()
    {
        $name = $this->getNameInput();
        $tableName = $this->ask('Masukkan nama table?', Str::plural(Str::snake(class_basename($name))));
        $primaryKey = $this->ask('Masukkan nama primary key?', 'id');
        $columns = $this->ask('Masukkan kolom fillable (pisahkan dengan koma)', 'name,email');
        $columnsArray = array_map('trim', explode(',', $columns));

        $this->additionalData = [
            'tableName'  => $tableName,
            'primaryKey' => $primaryKey,
            'fillable'   => $columnsArray,
        ];

        $result = parent::handle();
        if ($result !== false) {
            $this->info('Model berhasil dibuat.');
            $this->createMigration();
        }

        return $result;
    }

    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $stub = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
        $stub = $this->replaceCustomPlaceholders($stub);
        return $stub;
    }

    protected function replaceCustomPlaceholders($stub)
    {
        $primaryKey = $this->additionalData['primaryKey'];
        $fillableArray = $this->additionalData['fillable'];
        $fillableString = "['".implode("','", $fillableArray)."']";

        $castsString = '[]';
        if (in_array('extra', $fillableArray)) {
            $castsString = "['extra' => 'json']";
        }

        return str_replace(
            ['{{ primaryKey }}', '{{ fillable }}', '{{ casts }}'],
            [$primaryKey, $fillableString, $castsString],
            $stub
        );
    }

    protected function createMigration()
    {
        $tableName = $this->additionalData['tableName'];
        $migrationClass = 'Create'.Str::studly($tableName).'Table';
        $primaryKey = $this->additionalData['primaryKey'];

        if ($primaryKey === 'id') {
            $primaryKeyField = "\$table->id();";
        } else {
            $primaryKeyField = "\$table->id('{$primaryKey}');";
        }

        $columns = '';
        foreach ($this->additionalData['fillable'] as $column) {
            if ($column === $primaryKey) {
                continue;
            }
            if ($column === 'extra') {
                $columns .= "\n            \$table->json('extra')->nullable();";
            } else {
                $columns .= "\n            \$table->string('{$column}');";
            }
        }

        if (empty(trim($columns))) {
            $columns = "\n            // Tambahkan kolom lain di sini";
        }

        $stub = $this->files->get(base_path('stubs/migration.custom.stub'));
        $stub = str_replace(
            ['{{ class }}', '{{ table }}', '{{ primaryKeyField }}', '{{ columnDefinitions }}'],
            [$migrationClass, $tableName, $primaryKeyField, $columns],
            $stub
        );

        $timestamp = date('Y_m_d_His');
        $migrationFile = database_path("migrations/{$timestamp}_create_{$tableName}_table.php");
        $this->files->put($migrationFile, $stub);
        $this->info("Migration created: {$migrationFile}");
    }
}
