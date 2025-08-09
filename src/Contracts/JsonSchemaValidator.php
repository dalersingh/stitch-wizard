<?php

namespace StitchWizard\Contracts;

interface JsonSchemaValidator
{
    public function validate(array $data, array $schema): array;
}
