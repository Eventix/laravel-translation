<?php

namespace Eventix\Translation;

use Illuminate\Contracts\Translation\Loader;

class DatabaseLoader implements Loader {

    /**
     * All of the namespace hints.
     *
     * @var array
     */
    protected $hints = [];

    public function addJsonPath($path) {
    }

    public function namespaces() {
        return $this->hints;
    }

    /**
     * Load the messages for the given locale.
     *
     * @param  string $locale
     * @param  string $group
     * @param  string $namespace
     * @param  string $differential
     * @return array
     */
    public function load($locale, $group, $namespace = null, $differential = false) {
        $lines = $allLines = $this->linesCallback($locale, $group, $namespace, $differential);
        if (!count($allLines))
            return [];
        foreach ($lines as $key => $line) {
            $k = explode(".", $key);
            if (count($k) > 1) {
                $this->recursiveExplore($allLines, $k, $line);
                unset($allLines[$key]);
            }
        }

        return $allLines;
    }
    
    private function linesCallback($locale, $group, $namespace, $differential) {
        $r = \DB::table('translation')
            ->where('locale', $locale)
            ->where('group', $group)
            ->where('namespace', $namespace == '*' ? null : $namespace);

        $r = ($differential === false ? $r->whereNull('company_id') : $r->where('company_id', $differential));

        return $r->pluck('value', 'name');
    }

    /**
     * Insert a value over an array key into an array using a recursive process.
     *
     * @param array $line The referenced array to insert into
     * @param array $key The split key
     * @param $value The translated value
     * @return mixed The current part of the changed array
     */
    private function recursiveExplore(Array &$line, Array $key, $value) {
        // Do the insertion
        if (count($key) == 1)
            return $line[$key[0]] = $value;
        // Get the next part of the key and check whether it exists in the main array
        $v = array_shift($key);
        if (!key_exists($v, $line))
            $line[$v] = [];

        return $this->recursiveExplore($line[$v], $key, $value);
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string $namespace
     * @param  string $hint
     * @return void
     */
    public function addNamespace($namespace, $hint) {
        $this->hints[$namespace] = $hint;
    }
}
