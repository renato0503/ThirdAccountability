<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait NormalizesBrlNumbers
{
    protected function normalizeBrl(Request $req, array $paths): void
    {
        $all = $req->all();
        foreach ($paths as $path) {
            $this->normalizeAtPath($all, explode('.', $path));
        }
        $req->replace($all);
    }

    private function normalizeAtPath(array &$data, array $segments): void
    {
        if (empty($segments)) {
            return;
        }
        $seg = array_shift($segments);

        if ($seg === '*') {
            foreach ($data as &$item) {
                if (is_array($item)) {
                    if (empty($segments)) {
                        // shouldn't happen — '*' at the end is meaningless
                    } else {
                        $this->normalizeAtPath($item, $segments);
                    }
                }
            }
            return;
        }

        if (!array_key_exists($seg, $data)) {
            return;
        }

        if (empty($segments)) {
            $data[$seg] = $this->brlToNumeric($data[$seg]);
            return;
        }

        if (is_array($data[$seg])) {
            $this->normalizeAtPath($data[$seg], $segments);
        }
    }

    private function brlToNumeric($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }
        if (is_numeric($value)) {
            return $value;
        }
        if (!is_string($value)) {
            return $value;
        }
        $v = preg_replace('/[^\d,\.\-]/', '', $value);
        if ($v === '' || $v === null) {
            return null;
        }
        if (strpos($v, ',') !== false) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }
        return $v;
    }
}
