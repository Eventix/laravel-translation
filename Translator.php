<?php

namespace Eventix\Translation;

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
        $this->loaded[$namespace][$group][$locale] = $lines;
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
    public function getLines($locale, $group, $namespace = null, $differential = false) {
        $this->load($namespace, $group, $locale, $differential);

        return $this->loaded[$namespace][$group][$locale];
    }
}