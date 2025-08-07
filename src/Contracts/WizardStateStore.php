<?php

namespace StitchWizard\Contracts;

interface WizardStateStore
{
    public function get(string $wizardId): array;
    public function put(string $wizardId, array $data): void;
    public function clear(string $wizardId): void;
}
