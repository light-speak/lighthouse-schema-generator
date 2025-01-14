<?php

namespace LightSpeak\LighthouseSchemaGenerator\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use ReflectionException;
use Symfony\Component\Finder\SplFileInfo;

class ModelsUtils
{
    public function __construct(private readonly Reflection $reflection)
    {
    }

    /**
     * @param array $files
     * @param string $path models path
     * @return Collection
     * @throws ReflectionException
     */
    public function getModels(array $files, string $path = ''): Collection
    {
        $models = collect($files)->map(function (SplFileInfo $file) use ($path) {
            $path = $path ? $path . '/' . $file->getRelativePathName() : $file->getRelativePathName();

            return $this->getNamespace($path);
        })->filter(function (string $class) {
            $valid = false;

            if (class_exists($class)) {
                $reflection = $this->reflection->reflectionClass($class);
                $valid      = $reflection->isSubclassOf(Model::class) && (!$reflection->isAbstract());
            }

            return $valid;
        })->map(function (string $modelNamespace) {
            /** @var class-string $modelNamespace */
            return (new $modelNamespace());
        });

        return $models->values();
    }

    /**
     * @param string $path
     * @return string
     */
    private function getNamespace(string $path): string
    {
        return sprintf(
            '\%s%s',
            app()->getNamespace(),
            strtr(substr($path, 0, strrpos($path, '.') ?: null), '/', '\\')
        );
    }
}
