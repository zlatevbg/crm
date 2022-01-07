<?php

namespace App\Extensions\Translation;

class FileLoader extends \Illuminate\Translation\FileLoader
{
    /**
     * Load a locale from a given path.
     *
     * @param  string  $path
     * @param  string  $locale
     * @param  string  $group
     * @return array
     */
    protected function loadPath($path, $locale, $group)
    {
        $paths = array_wrap($path);
        foreach ($paths as $path) {
            if ($this->files->exists($full = "{$path}/{$locale}/{$group}.php")) {
                return $this->files->getRequire($full);
            }
        }

        return [];
    }

    /**
     * Load a local namespaced translation group for overrides.
     *
     * @param  array  $lines
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    protected function loadNamespaceOverrides(array $lines, $locale, $group, $namespace)
    {
        $paths = array_wrap($this->path);
        foreach ($paths as $path) {
            $file = "{$path}/vendor/{$namespace}/{$locale}/{$group}.php";

            if ($this->files->exists($file)) {
                return array_replace_recursive($lines, $this->files->getRequire($file));
            }
        }

        return $lines;
    }

    /**
     * Load a locale from the given JSON file path.
     *
     * @param  string  $locale
     * @return array
     */
    protected function loadJsonPaths($locale)
    {
        return collect(array_merge($this->jsonPaths, array_wrap($this->path)))
            ->reduce(function ($output, $path) use ($locale) {
                return $this->files->exists($full = "{$path}/{$locale}.json")
                    ? array_merge($output,
                        json_decode($this->files->get($full), true)
                    ) : $output;
            }, []);
    }
}
