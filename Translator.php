<?php

namespace Eventix\Translation;

class Translator extends \Illuminate\Translation\Translator {

    protected $differential = null;
    protected $diffCache = [];

    public function load($namespace, $group, $locale, $differential = null) {
        $diff = $differential ?? $this->differential;
        if (!isset($this->diffCache[$diff][$namespace][$group][$locale])) {
            // The loader is responsible for returning the array of language lines for the
            // given namespace, group, and locale. We'll set the lines in this array of
            // lines that have already been loaded so that we can easily access them.
            $lines = $this->loader->load($locale, $group, $namespace, $diff);
            $this->diffCache[$diff][$namespace][$group][$locale] = $lines;
        }

        $this->loaded[$namespace][$group][$locale] = $this->diffCache[$diff][$namespace][$group][$locale];
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