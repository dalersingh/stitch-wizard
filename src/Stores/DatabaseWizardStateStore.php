<?php

namespace StitchWizard\Stores;

use Illuminate\Contracts\Session\Session;
use StitchWizard\Contracts\WizardStateStore;
use StitchWizard\Models\WizardState;

class DatabaseWizardStateStore implements WizardStateStore
{
    /**
     * The session instance.
     */
    protected Session $session;

    /**
     * Create a new database wizard state store instance.
     *
     * @return void
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Get the wizard state from the database.
     */
    public function get(string $wizardId): array
    {
        $state = WizardState::where('session_id', $this->session->getId())
            ->where('wizard_id', $wizardId)
            ->first();

        return $state ? $state->state : [];
    }

    /**
     * Store the wizard state in the database.
     */
    public function put(string $wizardId, array $data): void
    {
        WizardState::updateOrCreate(
            [
                'session_id' => $this->session->getId(),
                'wizard_id' => $wizardId,
            ],
            [
                'state' => $data,
            ]
        );
    }

    /**
     * Clear the wizard state from the database.
     */
    public function clear(string $wizardId): void
    {
        WizardState::where('session_id', $this->session->getId())
            ->where('wizard_id', $wizardId)
            ->delete();
    }
}
