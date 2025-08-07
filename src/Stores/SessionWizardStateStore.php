<?php

namespace StitchWizard\Stores;

use StitchWizard\Contracts\WizardStateStore;
use Illuminate\Contracts\Session\Session;

class SessionWizardStateStore implements WizardStateStore
{
    protected Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function get(string $wizardId): array
    {
        return (array) $this->session->get("stitch-wizard.{$wizardId}", []);
    }

    public function put(string $wizardId, array $data): void
    {
        $this->session->put("stitch-wizard.{$wizardId}", $data);
    }

    public function clear(string $wizardId): void
    {
        $this->session->forget("stitch-wizard.{$wizardId}");
    }
}
