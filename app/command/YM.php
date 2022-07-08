<?php
declare (strict_types = 1);

namespace app\command;

use think\console\command\Make;

class YM extends Make
{
    protected $type = "Model";

    protected function configure()
    {
        parent::configure();
        $this->setName('ym:create')
            ->setDescription('Create a new model class for yulinzhihou Restful Api');
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.stub';
    }

    protected function buildClass(string $name): string
    {
        $namespace   = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
        $baseNamespace = trim(implode('\\', array_slice(explode('\\', $name), 0, 2)), '\\');

        $class = str_replace($namespace . '\\', '', $name);
        $stub  = file_get_contents($this->getStub());

        return str_replace(['{%baseNamespace%}','{%className%}','{%modelNamespace%}','{%validateNamespace%}', '{%namespace%}', '{%app_namespace%}'], [
            $baseNamespace,
            $class,
            $baseNamespace.'\model',
            $baseNamespace.'\validate',
            $namespace,
            $this->app->getNamespace(),
        ], $stub);
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\model';
    }
}
