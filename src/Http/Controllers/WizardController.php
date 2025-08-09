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
        $structure = $this->resolveStepStructure($firstStep, $currentState);
        $stepIndex = 1;
        $totalSteps = count($wizard['steps']);
        $pct = $this->computePct($stepIndex, $totalSteps);

        return view('stitch-wizard::wizard.step', [
            'wizardId' => $id,
            'stepKey' => $stepKey,
            'prevStepKey' => null,
            'stepIndex' => $stepIndex,
            'totalSteps' => $totalSteps,
            'wizard' => $wizard,
            'step' => $firstStep,
            'sections' => $structure['sections'],
            'fields' => $structure['fields'],
            'values' => $currentState,
            'pct' => $pct,
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
        $structure = $this->resolveStepStructure($step, $currentState);
        $stepIndex = $this->stepIndex($wizard, $key);
        $pct = $this->computePct($stepIndex, count($wizard['steps']));

        return view('stitch-wizard::wizard.step', [
            'wizardId' => $id,
            'stepKey' => $key,
            'prevStepKey' => $this->previousStepKey($wizard, $key),
            'stepIndex' => $stepIndex,
            'totalSteps' => count($wizard['steps']),
            'wizard' => $wizard,
            'step' => $step,
            'sections' => $structure['sections'],
            'fields' => $structure['fields'],
            'values' => $currentState,
            'pct' => $pct,
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

        $structure = $this->resolveStepStructure($step, $context);
        $fieldKeys = array_column($structure['fields'], 'key');
        $filteredInput = array_intersect_key($inputData, array_flip($fieldKeys));

        $errors = $this->validator->validate($filteredInput, $structure['fields']);

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
                'sections' => $structure['sections'],
                'fields' => $structure['fields'],
                'values' => $context,
                'fieldErrors' => $errors,
                'pct' => $this->computePct(
                    $this->stepIndex($wizard, $key),
                    count($wizard['steps'])
                ),
            ]);
        }

        foreach ($structure['fields'] as $field) {
            if (($field['type'] ?? null) === 'file') {
                $fieldKey = $field['key'];
                if (isset($filteredInput[$fieldKey]) && $filteredInput[$fieldKey] instanceof UploadedFile) {
                    $filteredInput[$fieldKey] = $filteredInput[$fieldKey]
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
                $nextStructure = $this->resolveStepStructure($nextStep, $updatedState);

                return view('stitch-wizard::wizard.step', [
                    'wizardId' => $id,
                    'stepKey' => $nextStepKey,
                    'prevStepKey' => $this->previousStepKey($wizard, $nextStepKey),
                    'stepIndex' => array_search($nextStepKey, array_column($wizard['steps'], 'key'), true) + 1,
                    'totalSteps' => count($wizard['steps']),
                    'wizard' => $wizard,
                    'step' => $nextStep,
                    'sections' => $nextStructure['sections'],
                    'fields' => $nextStructure['fields'],
                    'values' => $updatedState,
                    'pct' => $this->computePct(
                        $this->stepIndex($wizard, $nextStepKey),
                        count($wizard['steps'])
                    ),
                ])->render();
            }

            return response()->json(['redirect' => route('stitch-wizard.finalize', ['id' => $id])]);
        }

        if ($nextStep) {
            $nextStructure = $this->resolveStepStructure($nextStep, $updatedState);

            return view('stitch-wizard::wizard.step', [
                'wizardId' => $id,
                'stepKey' => $nextStepKey,
                'prevStepKey' => $this->previousStepKey($wizard, $nextStepKey),
                'stepIndex' => array_search($nextStepKey, array_column($wizard['steps'], 'key'), true) + 1,
                'totalSteps' => count($wizard['steps']),
                'wizard' => $wizard,
                'step' => $nextStep,
                'sections' => $nextStructure['sections'],
                'fields' => $nextStructure['fields'],
                'values' => $updatedState,
                'pct' => $this->computePct(
                    $this->stepIndex($wizard, $nextStepKey),
                    count($wizard['steps'])
                ),
            ]);
        }

        return redirect()->route('stitch-wizard.finalize', ['id' => $id]);
    }

    public function finalize(string $id, Request $request): mixed
    {
        $this->stateStore->clear($id);

        if ($request->expectsJson() || $request->ajax()) {
            return response(
                view('stitch-wizard::wizard.success', ['wizardId' => $id])->render()
            );
        }

        return view('stitch-wizard::wizard.success', [
            'wizardId' => $id,
        ]);
    }

    private function stepIndex(array $wizard, string $key): int
    {
        $idx = array_search($key, array_column($wizard['steps'], 'key'), true);

        return $idx === false ? 0 : ($idx + 1);
    }

    private function computePct(int $stepIndex, int $totalSteps): int
    {
        if ($totalSteps <= 0) {
            return 0;
        }

        return (int) floor((($stepIndex - 1) / $totalSteps) * 100);
    }

    private function resolveStepStructure(array $step, array $context): array
    {
        if (isset($step['sections']) && is_array($step['sections'])) {
            $sections = [];
            $flat = [];

            foreach ($step['sections'] as $section) {
                $fields = $this->visibleFields($section['fields'] ?? [], $context);
                $fields = array_values($fields);

                $sections[] = [
                    'title'  => $section['title'] ?? null,
                    'fields' => $fields,
                ];

                foreach ($fields as $field) {
                    $flat[] = $field;
                }
            }

            return [
                'sections' => $sections,
                'fields'   => $flat,
            ];
        }

        $fields = $this->visibleFields($step['fields'] ?? [], $context);

        return [
            'sections' => null,
            'fields'   => array_values($fields),
        ];
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
