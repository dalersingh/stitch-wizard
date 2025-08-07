<?php

namespace StitchWizard\Models;

use Illuminate\Database\Eloquent\Model;

class WizardState extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wizard_states';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'state' => 'array',
    ];
}
