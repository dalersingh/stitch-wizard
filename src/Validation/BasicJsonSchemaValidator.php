<?php

namespace StitchWizard\Validation;

use Illuminate\Support\Facades\Validator;
use StitchWizard\Contracts\JsonSchemaValidator;

class BasicJsonSchemaValidator implements JsonSchemaValidator
{
    public function validate(array $data, array $schema): array
    {
        $rules = [];

        foreach ($schema as $field) {
            if (! isset($field['key'])) {
                continue;
            }

            if (isset($field['rules']) && is_array($field['rules']) && $field['rules'] !== []) {
                $rules[$field['key']] = $field['rules'];
            }
        }

        if ($rules === []) {
            return [];
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        return [];
    }
}
