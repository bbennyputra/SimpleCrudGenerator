<?php

namespace Luthfi\CrudGenerator;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CrudMake extends Command
{
    private $files;
    private $modelName;
    private $pluralModelName;
    private $lowerCasePluralModel;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create simple Laravel CRUD files of given model name.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->getModelName();

        $this->generateModel();
        $this->generateController();
        $this->generateMigration();
        $this->generateViews();
        $this->generateTests();

        $this->info('CRUD files generated successfully!');
    }

    public function getModelName()
    {
        $this->modelName = $this->argument('name');

        $this->pluralModelName = str_plural($this->modelName);
        $this->lowerCasePluralModel = strtolower($this->pluralModelName);
    }

    public function generateModel()
    {
        $this->callSilent('make:model', ['name' => $this->modelName]);;

        $this->info($this->modelName.' model generated.');
    }

    public function generateController()
    {
        if (! $this->files->isDirectory(app_path('Http/Controllers'))) {
            $this->files->makeDirectory(app_path('Http/Controllers'), 0777, true, true);
        }

        $controllerPath = app_path('Http/Controllers/'.$this->pluralModelName.'Controller.php');
        $this->files->put($controllerPath, $this->files->get(__DIR__.'/stubs/controller.model.stub'));

        $this->info($this->pluralModelName.'Controller generated.');
    }
    public function generateMigration()
    {
        $migrationFilePath = database_path('migrations/'.date('Y_m_d_His').'_create_'.$this->lowerCasePluralModel.'_table.php');
        $this->files->put($migrationFilePath, $this->files->get(__DIR__.'/stubs/migration-create.stub'));

        $this->info($this->modelName.' table migration generated.');
    }

    public function generateViews()
    {
        $viewPath = resource_path('views/'.$this->lowerCasePluralModel);
        if (! $this->files->isDirectory($viewPath)) {
            $this->files->makeDirectory($viewPath, 0777, true, true);
        }

        $this->files->put($viewPath.'/index.blade.php', $this->files->get(__DIR__.'/stubs/view-index.stub'));
        $this->files->put($viewPath.'/forms.blade.php', $this->files->get(__DIR__.'/stubs/view-forms.stub'));

        $this->info($this->modelName.' view files generated.');
    }

    public function generateTests()
    {
        $this->callSilent('make:test', ['name' => 'Manage'.$this->pluralModelName.'Test']);
        $this->info('Manage'.$this->pluralModelName.'Test generated.');

        $this->callSilent('make:test', ['name' => 'Models/'.$this->modelName.'Test', '--unit' => true]);
        $this->info($this->modelName.'Test (model) generated.');
    }
}