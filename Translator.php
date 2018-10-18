<?php

namespace Eventix\Translation;

use Illuminate\Support\Arr;

class Translator extends \Illuminate\Translation\Translator {

    protected $differential = null;

    public function load($namespace, $group, $locale, $differential = null) {
        $diff = $differential ?? $this->differential;
        if ($this->isLoaded($namespace, $group, $locale, $diff)) {
            return;
        }
        // The loader is responsible for returning the array of language lines for the
        // given namespace, group, and locale. We'll set the lines in this array of
        // lines that have already been loaded so that we can easily access them.
        $lines = $this->loader->load($locale, $group, $namespace, $diff);
        $this->loaded[$namespace][$group][$locale][$diff] = $lines;
    }

    /**
     * Set the differential thing, can be anything since it is directly passed to the callback defined in the
     * configuration file.
     *
     * @param $differential The parameter to be passed.
     */
    public function setDifferential($differential) {
        $this->differential = $differential;
    }

    /**
     * Get all lines loaded in this translator
     *
     * @param $locale The locale to load the translation of
     * @param $group The translation group
     * @param null $namespace The namespace to load
     * @param bool $differential The differential to load
     * @return mixed All translated lines
     */
    public function getLines($locale, $group, $namespace = null, $differential = null) {
        $diff = $differential ?? $this->differential;
        $this->load($namespace, $group, $locale, $diff);

        return $this->loaded[$namespace][$group][$locale][$diff];
    }

    /**
     * Get the translation for a given key from the JSON translation files.
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string  $locale
     * @return string|array|null
     */
    public function getFromJson($key, array $replace = [], $locale = null, $differential = null)
    {
        $locale = $locale ?: $this->locale;
        $diff = $differential ?? $this->differential;

        // For JSON translations, there is only one file per locale, so we will simply load
        // that file and then we will be ready to check the array for the key. These are
        // only one level deep so we do not need to do any fancy searching through it.
        $this->load('*', '*', $locale);

        $line = $this->loaded['*']['*'][$locale][$key][$diff] ?? null;

        // If we can't find a translation for the JSON key, we will attempt to translate it
        // using the typical translation file. This way developers can always just use a
        // helper such as __ instead of having to pick between trans or __ with views.
        if (! isset($line)) {
            $fallback = $this->get($key, $replace, $locale);

            if ($fallback !== $key) {
                return $fallback;
            }
        }

        return $this->makeReplacements($line ?: $key, $replace);
    }

    /**
     * Retrieve a language line out the loaded array.
     *
     * @param  string  $namespace
     * @param  string  $group
     * @param  string  $locale
     * @param  string  $item
     * @param  array   $replace
     * @return string|array|null
     */
    protected function getLine($namespace, $group, $locale, $item, array $replace, $differential = null)
    {
        $diff = $differential ?? $this->differential;
        $this->load($namespace, $group, $locale, $diff);

        $line = Arr::get($this->loaded[$namespace][$group][$locale][$diff], $item);

        if (is_string($line)) {
            return $this->makeReplacements($line, $replace);
        } elseif (is_array($line) && count($line) > 0) {
            return $line;
        }
    }

    /**
     * Determine if the given group has been loaded.
     *
     * @param  string  $namespace
     * @param  string  $group
     * @param  string  $locale
     * @return bool
     */
    protected function isLoaded($namespace, $group, $locale, $differential = null)
    {
        $diff = $differential ?? $this->differential;
        return isset($this->loaded[$namespace][$group][$locale][$diff]);
    }
}