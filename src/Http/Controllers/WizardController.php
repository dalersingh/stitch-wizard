<?php

namespace StitchWizard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use StitchWizard\Contracts\JsonSchemaValidator;
use StitchWizard\Contracts\VisibilityEngine;
use StitchWizard\Contracts\WizardStateStore;

class WizardController extends Controller
{
    protected $stateStore;
    protected $validator;
    protected $visibilityEngine;

    public function __construct(
        WizardStateStore $stateStore,
        JsonSchemaValidator $validator,
        VisibilityEngine $visibilityEngine
    ) {
        $this->stateStore = $stateStore;
        $this->validator = $validator;
        $this->visibilityEngine = $visibilityEngine;
    }

    public function show(string $id)
    {
        $wizard = $this->loadWizard($id);
        if (!$wizard) {
            abort(404);
        }

        $firstStep = $wizard['steps'][0] ?? null;
        if (!$firstStep) {
            abort(404);
        }

        $stepKey = $firstStep['key'];
        
        return view('stitch-wizard::wizard.step', [
            'wizardId' => $id,
            'stepKey' => $stepKey,
            'wizard' => $wizard,
            'step' => $firstStep,
            'values' => $this->stateStore->get($id),
        ]);
    }

    public function postStep(string $id, string $key, Request $request)
    {
        $wizard = $this->loadWizard($id);
        if (!$wizard) {
            abort(404);
        }

        $step = $this->findStep($wizard, $key);
        if (!$step) {
            abort(404);
        }

        $currentState = $this->stateStore->get($id);
        $inputData = $request->all();
        $context = array_merge($currentState, $inputData);
        
        $visibleFields = $this->visibleFields($step['fields'], $context);
        $fieldKeys = array_column($visibleFields, 'key');
        $filteredInput = array_intersect_key($inputData, array_flip($fieldKeys));
        
        $errors = $this->validator->validate($filteredInput, $visibleFields);
        
        if (!empty($errors)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $errors], 422);
            }
            
            return view('stitch-wizard::wizard.step', [
                'wizardId' => $id,
                'stepKey' => $key,
                'wizard' => $wizard,
                'step' => $step,
                'values' => $context,
                'errors' => $errors,
            ]);
        }
        
        $updatedState = array_merge($currentState, $filteredInput);
        $this->stateStore->put($id, $updatedState);
        
        $nextStepKey = $this->nextStepKey($wizard, $key);
        $nextStep = $nextStepKey ? $this->findStep($wizard, $nextStepKey) : null;
        
        if ($request->expectsJson() || $request->ajax()) {
            if ($nextStep) {
                return view('stitch-wizard::wizard.step', [
                    'wizardId' => $id,
                    'stepKey' => $nextStepKey,
                    'wizard' => $wizard,
                    'step' => $nextStep,
                    'values' => $updatedState,
                ])->render();
            }
            
            return response()->json(['redirect' => route('stitch-wizard.finalize', ['id' => $id])]);
        }
        
        if ($nextStep) {
            return view('stitch-wizard::wizard.step', [
                'wizardId' => $id,
                'stepKey' => $nextStepKey,
                'wizard' => $wizard,
                'step' => $nextStep,
                'values' => $updatedState,
            ]);
        }
        
        return redirect()->route('stitch-wizard.finalize', ['id' => $id]);
    }

    public function finalize(string $id, Request $request)
    {
        $this->stateStore->clear($id);
        
        if ($request->expectsJson() || $request->ajax()) {
            return response('<div class="wizard-success">Wizard completed successfully!</div>');
        }
        
        return view('stitch-wizard::wizard.success', [
            'wizardId' => $id,
        ]);
    }
    
    private function loadWizard(string $id)
    {
        return config("stitch-wizard.wizards.{$id}");
    }
    
    private function findStep(array $wizard, string $key)
    {
        foreach ($wizard['steps'] as $step) {
            if ($step['key'] === $key) {
                return $step;
            }
        }
        
        return null;
    }
    
    private function nextStepKey(array $wizard, string $currentKey)
    {
        $steps = $wizard['steps'];
        $currentIndex = -1;
        
        foreach ($steps as $index => $step) {
            if ($step['key'] === $currentKey) {
                $currentIndex = $index;
                break;
            }
        }
        
        if ($currentIndex >= 0 && isset($steps[$currentIndex + 1])) {
            return $steps[$currentIndex + 1]['key'];
        }
        
        return null;
    }
    
    private function visibleFields(array $fields, array $context)
    {
        return array_filter($fields, function ($field) use ($context) {
            if (!isset($field['visibility'])) {
                return true;
            }
            
            return $this->visibilityEngine->evaluate($context, $field['visibility']);
        });
    }
}
