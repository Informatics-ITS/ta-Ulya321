<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Filesystem\Filesystem;

class MakeCustomIndexView extends Command
{
    protected $signature = 'make:custom-index-view {model}';
    protected $description = 'Membuat file index.blade.php dinamis berdasarkan model dan mendaftarkan routes';
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $model = $this->argument('model');
        $modelClass = $this->qualifyModel($model);

        if (!class_exists($modelClass)) {
            $this->error("Model {$model} tidak ditemukan.");
            return false;
        }

        $instance = new $modelClass;
        $fillable = $instance->getFillable();

        if (empty($fillable)) {
            $table = $instance->getTable();
            $fillable = Schema::getColumnListing($table);
        }

        $primaryKey = $instance->getKeyName();
        $excluded = [$primaryKey, 'created_at', 'updated_at', 'deleted_at'];
        $fields = array_filter($fillable, function ($field) use ($excluded) {
            return !in_array($field, $excluded);
        });

        $headers = "";
        foreach ($fields as $field) {
            $headers .= "                                    <th>" . ucfirst(str_replace('_', ' ', $field)) . "</th>\n";
        }
        $headers .= "                                    <th>Aksi</th>\n";

        $rowData = "";
        foreach ($fields as $field) {
            $rowData .= "                                        <td>{{ \$item->$field }}</td>\n";
        }
        $rowData .= "                                        <td>\n";
        $rowData .= "                                            <button type=\"button\" class=\"btn btn-sm btn-info edit-btn\" data-bs-toggle=\"modal\" data-bs-target=\"#addEditModal\">\n";
        $rowData .= "                                                <i class=\"fas fa-edit\"></i>\n";
        $rowData .= "                                            </button>\n";
        $rowData .= "                                            <button type=\"button\" class=\"btn btn-sm btn-danger delete-btn\">\n";
        $rowData .= "                                                <i class=\"fas fa-trash\"></i>\n";
        $rowData .= "                                            </button>\n";
        $rowData .= "                                            <form method=\"POST\" action=\"{{ route('" . Str::kebab(Str::pluralStudly($model)) . ".destroy', \$item->{$primaryKey}) }}\" style=\"display:none;\" class=\"delete-form\">\n";
        $rowData .= "                                                @csrf\n";
        $rowData .= "                                                @method('DELETE')\n";
        $rowData .= "                                            </form>\n";
        $rowData .= "                                        </td>\n";

        $formFields = "";
        $dynamicJsSetFields = "";
        foreach ($fields as $field) {
            $label = ucfirst(str_replace('_', ' ', $field));
            if (stripos($field, 'email') !== false) {
                $type = 'email';
            } elseif (stripos($field, 'tanggal') !== false || stripos($field, 'date') !== false) {
                $type = 'date';
            } elseif (preg_match('/(phone|telepon|hp|nim|nik|nip|kode|postal|zip|no_|num|number)/i', $field)) {
                $type = 'number';
            } else {
                $type = 'text';
            }
            $formFields .= "                        <div class=\"mb-3\">\n";
            $formFields .= "                            <label for=\"{$field}\" class=\"form-label\">{$label}</label>\n";
            $formFields .= "                            <input type=\"{$type}\" class=\"form-control\" id=\"{$field}\" name=\"{$field}\" required>\n";
            $formFields .= "                        </div>\n";
            $dynamicJsSetFields .= "    \$('#{$field}').val(item.{$field});\n";
        }

        $modelVariable = lcfirst(class_basename($model));
        $collectionVariable = Str::camel(Str::plural($modelVariable));

        $stub = $this->files->get(base_path('stubs/index.blade.stub'));

        $replacements = [
            '{{ title }}' => class_basename($model),
            '{{ collectionVariable }}' => $collectionVariable,
            '{{ tableHeaders }}' => $headers,
            '{{ tableRowData }}' => $rowData,
            '{{ formFields }}' => $formFields,
        ];

        $contents = str_replace(array_keys($replacements), array_values($replacements), $stub);
        $contents = str_replace(
            '<input type="hidden" name="id" id="id">',
            "<input type=\"hidden\" name=\"{$primaryKey}\" id=\"{$primaryKey}\">",
            $contents
        );

        $addBtnJs = "\$('.add-btn').on('click', function() {\n".
                    "    \$('#addEditModalLabel').text('Tambah Data ".class_basename($model)."');\n".
                    "    \$('#dataForm')[0].reset();\n".
                    "    \$('#{$primaryKey}').val('');\n".
                    "    \$('#dataForm').validate().resetForm();\n".
                    "});\n";

        $editBtnJs = "\$('.edit-btn').on('click', function() {\n".
                     "    var row = \$(this).closest('tr');\n".
                     "    var item = row.data('item');\n".
                     "    \$('#addEditModalLabel').text('Edit Data ".class_basename($model)."');\n".
                     "    \$('#{$primaryKey}').val(item.{$primaryKey});\n".
                     $dynamicJsSetFields.
                     "});\n";

        $contents = str_replace(
            "$('.edit-btn').on('click', function() {",
            "// === addBtnJs ===\n{$addBtnJs}\n// === editBtnJs ===\n{$editBtnJs}\n\n$('.edit-btn').on('click', function() {",
            $contents
        );

        $folder = resource_path("views/content/" . Str::kebab($collectionVariable));
        $viewPath = $folder . "/index.blade.php";

        if ($this->files->exists($viewPath)) {
            $this->error("View {$viewPath} sudah ada.");
            return false;
        }

        if (!$this->files->isDirectory($folder)) {
            $this->files->makeDirectory($folder, 0755, true);
        }

        $this->files->put($viewPath, $contents);
        $this->info("View created: {$viewPath}");

        $this->generateRoutes($model, $collectionVariable);
        return true;
    }

    protected function qualifyModel($model)
    {
        $model = trim($model, '\\');
        return "App\\Models\\" . $model;
    }

    protected function generateRoutes($model, $collectionVariable)
    {
        $controllerName = $model . 'Controller';
        $controllerClass = "App\\Http\\Controllers\\" . $controllerName;
        $modelPluralKebab = Str::kebab(Str::pluralStudly($model));
        $routeBlock  = "\n\n/* Routes for {$modelPluralKebab} generated automatically */\n";
        $routeBlock .= "Route::get('/{$modelPluralKebab}', [{$controllerClass}::class, 'index'])->name('{$modelPluralKebab}.index');\n";
        $routeBlock .= "Route::post('/{$modelPluralKebab}/save', [{$controllerClass}::class, 'save{$model}'])->name('{$modelPluralKebab}.save');\n";
        $routeBlock .= "Route::delete('/{$modelPluralKebab}/{id}', [{$controllerClass}::class, 'delete{$model}'])->name('{$modelPluralKebab}.destroy');\n";

        $routesFile = base_path('routes/web.php');
        $currentRoutes = $this->files->get($routesFile);

        if (strpos($currentRoutes, "/* Routes for {$modelPluralKebab} generated automatically */") === false) {
            $this->files->append($routesFile, $routeBlock);
            $this->info("Routes appended to routes/web.php");
            $this->runPintOnFile($routesFile);
        } else {
            $this->info("Routes for {$modelPluralKebab} already exist in routes/web.php");
        }
    }

    protected function runPintOnFile($filePath)
    {
        $relativePath = str_replace(base_path().'/', '', $filePath);
        $command = 'cd '.base_path().' && ./vendor/bin/pint '.escapeshellarg($relativePath);
        exec($command, $output, $returnVar);
    }
}
