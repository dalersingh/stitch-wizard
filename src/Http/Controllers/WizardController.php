<?php

namespace StitchWizard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use StitchWizard\Contracts\JsonSchemaValidator;
use StitchWizard\Contracts\VisibilityEngine;
use StitchWizard\Contracts\WizardStateStore;

class WizardController extends Controller
{
    protected WizardStateStore $stateStore;

    protected JsonSchemaValidator $validator;

    protected VisibilityEngine $visibilityEngine;

    public function __construct(
        WizardStateStore $stateStore,
        JsonSchemaValidator $validator,
        VisibilityEngine $visibilityEngine
    ) {
        $this->stateStore = $stateStore;
        $this->validator = $validator;
        $this->visibilityEngine = $visibilityEngine;
    }

    public function show(string $id): mixed
    {
        $wizard = $this->loadWizard($id);
        if (! $wizard) {
            abort(404);
        }

        $firstStep = $wizard['steps'][0] ?? null;
        if (! $firstStep) {
            abort(404);
        }

        $stepKey = $firstStep['key'];
        $currentState = $this->stateStore->get($id);
        $visibleFields = $this->visibleFields($firstStep['fields'], $currentState);

        return view('stitch-wizard::wizard.step', [
            'wizardId' => $id,
            'stepKey' => $stepKey,
            'prevStepKey' => null,
            'stepIndex' => 1,
            'totalSteps' => count($wizard['steps']),
            'wizard' => $wizard,
            'step' => $firstStep,
            'fields' => $visibleFields,
            'values' => $currentState,
        ]);
    }

    public function showStep(string $id, string $key): mixed
    {
        $wizard = $this->loadWizard($id);
        if (! $wizard) {
            abort(404);
        }

        $step = $this->findStep($wizard, $key);
        if (! $step) {
            abort(404);
        }

        $currentState = $this->stateStore->get($id);
        $visibleFields = $this->visibleFields($step['fields'], $currentState);

        $stepIndex = array_search($key, array_column($wizard['steps'], 'key'), true) + 1;

        return view('stitch-wizard::wizard.step', [
            'wizardId' => $id,
            'stepKey' => $key,
            'prevStepKey' => $this->previousStepKey($wizard, $key),
            'stepIndex' => $stepIndex,
            'totalSteps' => count($wizard['steps']),
            'wizard' => $wizard,
            'step' => $step,
            'fields' => $visibleFields,
            'values' => $currentState,
        ]);
    }

    public function postStep(string $id, string $key, Request $request): mixed
    {
        $wizard = $this->loadWizard($id);
        if (! $wizard) {
            abort(404);
        }

        $step = $this->findStep($wizard, $key);
        if (! $step) {
            abort(404);
        }

        $currentState = $this->stateStore->get($id);
        $inputData = $request->all();
        $context = array_merge($currentState, $inputData);

        $visibleFields = $this->visibleFields($step['fields'], $context);
        $fieldKeys = array_column($visibleFields, 'key');
        $filteredInput = array_intersect_key($inputData, array_flip($fieldKeys));

        $errors = $this->validator->validate($filteredInput, $visibleFields);

        if (! empty($errors)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $errors], 422);
            }

            return view('stitch-wizard::wizard.step', [
                'wizardId' => $id,
                'stepKey' => $key,
                'prevStepKey' => $this->previousStepKey($wizard, $key),
                'stepIndex' => array_search($key, array_column($wizard['steps'], 'key'), true) + 1,
                'totalSteps' => count($wizard['steps']),
                'wizard' => $wizard,
                'step' => $step,
                'fields' => $visibleFields,
                'values' => $context,
                'fieldErrors' => $errors,
            ]);
        }

        foreach ($visibleFields as $field) {
            if (($field['type'] ?? null) === 'file') {
                $key = $field['key'];
                if (isset($filteredInput[$key]) && $filteredInput[$key] instanceof UploadedFile) {
                    $filteredInput[$key] = $filteredInput[$key]
                        ->store('stitch-wizard', config('filesystems.default', 'local'));
                }
            }
        }

        $updatedState = array_merge($currentState, $filteredInput);
        $this->stateStore->put($id, $updatedState);

        $nextStepKey = $this->nextStepKey($wizard, $key);
        $nextStep = $nextStepKey ? $this->findStep($wizard, $nextStepKey) : null;

        if ($request->expectsJson() || $request->ajax()) {
            if ($nextStep) {
                $nextVisible = $this->visibleFields($nextStep['fields'], $updatedState);

                return view('stitch-wizard::wizard.step', [
                    'wizardId' => $id,
                    'stepKey' => $nextStepKey,
                    'prevStepKey' => $this->previousStepKey($wizard, $nextStepKey),
                    'stepIndex' => array_search($nextStepKey, array_column($wizard['steps'], 'key'), true) + 1,
                    'totalSteps' => count($wizard['steps']),
                    'wizard' => $wizard,
                    'step' => $nextStep,
                    'fields' => $nextVisible,
                    'values' => $updatedState,
                ])->render();
            }

            return response()->json(['redirect' => route('stitch-wizard.finalize', ['id' => $id])]);
        }

        if ($nextStep) {
            $nextVisible = $this->visibleFields($nextStep['fields'], $updatedState);

            return view('stitch-wizard::wizard.step', [
                'wizardId' => $id,
                'stepKey' => $nextStepKey,
                'prevStepKey' => $this->previousStepKey($wizard, $nextStepKey),
                'stepIndex' => array_search($nextStepKey, array_column($wizard['steps'], 'key'), true) + 1,
                'totalSteps' => count($wizard['steps']),
                'wizard' => $wizard,
                'step' => $nextStep,
                'fields' => $nextVisible,
                'values' => $updatedState,
            ]);
        }

        return redirect()->route('stitch-wizard.finalize', ['id' => $id]);
    }

    public function finalize(string $id, Request $request): mixed
    {
        $this->stateStore->clear($id);

        if ($request->expectsJson() || $request->ajax()) {
            return response('<div class="wizard-success">Wizard completed successfully!</div>');
        }

        return view('stitch-wizard::wizard.success', [
            'wizardId' => $id,
        ]);
    }

    private function loadWizard(string $id): ?array
    {
        return config("stitch-wizard.wizards.{$id}");
    }

    private function findStep(array $wizard, string $key): ?array
    {
        foreach ($wizard['steps'] as $step) {
            if ($step['key'] === $key) {
                return $step;
            }
        }

        return null;
    }

    private function nextStepKey(array $wizard, string $currentKey): ?string
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

    private function visibleFields(array $fields, array $context): array
    {
        return array_filter($fields, function ($field) use ($context) {
            if (! isset($field['visibility'])) {
                return true;
            }

            return $this->visibilityEngine->evaluate($context, $field['visibility']);
        });
    }

    private function previousStepKey(array $wizard, string $currentKey): ?string
    {
        $steps = $wizard['steps'];
        foreach ($steps as $index => $step) {
            if ($step['key'] === $currentKey && $index > 0) {
                return $steps[$index - 1]['key'];
            }
        }

        return null;
    }
}
