<?php

namespace Eventix\Translation;

class Translator extends \Illuminate\Translation\Translator {

    protected $differential = null;
    protected $diffCache = [];

    public function load($namespace, $group, $locale, $differential = null) {
        $diff = $differential ?? $this->differential;
        if (array_key_exists($diff, $this->diffCache)) {
            $this->loaded[$namespace][$group][$locale] = $this->diffCache[$diff];
        }else{
            // The loader is responsible for returning the array of language lines for the
            // given namespace, group, and locale. We'll set the lines in this array of
            // lines that have already been loaded so that we can easily access them.
            $lines = $this->loader->load($locale, $group, $namespace, $diff);
            $this->loaded[$namespace][$group][$locale] = $this->diffCache[$diff] = $lines;
        }
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
}