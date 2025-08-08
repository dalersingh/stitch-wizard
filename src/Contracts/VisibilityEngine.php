<?php

namespace StitchWizard\Contracts;

interface VisibilityEngine
{
    public function evaluate(array $context, array $rules): bool;
}
