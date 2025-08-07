<?php

namespace StitchWizard\Visibility;

use StitchWizard\Contracts\VisibilityEngine;

class SimpleVisibilityEngine implements VisibilityEngine
{
    public function evaluate(array $context, array $rules): bool
    {
        if (! isset($rules['rules']) || $rules['rules'] === []) {
            return true;
        }

        $logic = strtolower($rules['logic'] ?? 'all') === 'any' ? 'any' : 'all';

        $results = array_map(function (array $rule) use ($context) {
            $path = $rule['path'] ?? null;
            $op = $rule['op'] ?? '=';
            $value = $rule['value'] ?? null;

            $actual = data_get($context, $path);

            switch ($op) {
                case '=':
                    return $actual == $value;
                case '!=':
                    return $actual != $value;
                case '>':
                    return $actual > $value;
                case '>=':
                    return $actual >= $value;
                case '<':
                    return $actual < $value;
                case '<=':
                    return $actual <= $value;
                case 'in':
                    return in_array($actual, (array) $value, true);
                case 'not_in':
                    return ! in_array($actual, (array) $value, true);
                case 'exists':
                    return data_get($context, $path, '__missing__') !== '__missing__';
                case 'truthy':
                    return (bool) $actual === true;
                case 'falsy':
                    // Consider any value that evaluates to boolean false as “falsy”.
                    return ! (bool) $actual;
                default:
                    return true;
            }
        }, $rules['rules']);

        return $logic === 'all'
            ? ! in_array(false, $results, true)
            : in_array(true, $results, true);
    }
}
