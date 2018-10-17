<?php

namespace Eventix\Translation;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Translation\FileLoader;

class AllLoader implements Loader {
    /**
     *
     * @var array|String
     */
    protected $classSources = [
        FileLoader::class,
        DatabaseLoader::class,
    ];

    /**
     * All instances of Interface implementations loading
     *
     * @var array|LoaderInterface
     */
    protected $sources = [];

    /**
     * Create a new file loader instance.
     *
     * @param  $files
     * @param  string $path
     */
    public function __construct($files, $path) {
        foreach ($this->classSources as $class)
            if (in_array(Loader::class, class_implements($class)))
                $this->sources[] = new $class($files, $path);
    }

    /**
     * Load the messages for the given locale.
     *
     * @param  string $locale
     * @param  string $group
     * @param  string $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null, $differential = false) {
        return array_reduce($this->sources, function ($carry, $item) use ($locale, $group, $namespace, $differential) {
            return array_replace_recursive($carry, $item->load($locale, $group, $namespace, $differential));
        }, []);
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string $namespace
     * @param  string $hint
     * @return void
     */
    public function addNamespace($namespace, $hint) {
        foreach ($this->sources as $source)
            $source->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path) {
        foreach ($this->sources as $source)
            $source->addJsonPath($path);
    }

    public function namespaces() {
        $parts = [];
        foreach ($this->sources as $source)
            $parts[] = $source->namespaces();

        return call_user_func_array('array_merge_recursive', $parts);
    }
}
