<div class="wizard-step-container max-w-xl mx-auto">
    <h1 class="text-xl font-bold mb-6">
        {{ $step['title'] ?? $stepKey }}
    </h1>

    @if (isset($stepIndex, $totalSteps))
        <p class="text-sm text-gray-500 mb-2">
            Step {{ $stepIndex }} of {{ $totalSteps }}
        </p>
    @endif

    <form
        method="POST"
        action="{{ route('stitch-wizard.step', ['id' => $wizardId, 'key' => $stepKey]) }}"
        class="space-y-6"
        enctype="multipart/form-data"
    >
        @csrf

        @foreach (($fields ?? $step['fields']) as $field)
            @php
                $key   = $field['key'];
                $type  = $field['type'] ?? 'text';
                $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $key));
                $value = old($key, $values[$key] ?? '');
                $fieldErrorsForKey = (isset($fieldErrors) && is_array($fieldErrors))
                    ? ($fieldErrors[$key] ?? [])
                    : (isset($errors) ? $errors->get($key) : []);
            @endphp

            <div class="flex flex-col gap-1">
                <label class="font-medium" for="{{ $key }}">{{ $label }}</label>

                @if ($type === 'select')
                    <select
                        id="{{ $key }}"
                        name="{{ $key }}"
                        class="border rounded px-3 py-2 @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                    >
                        <option value="">-- Select --</option>
                        @foreach ($field['options'] ?? [] as $opt)
                            <option
                                value="{{ $opt['value'] }}"
                                @selected($value == $opt['value'])
                            >
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input
                        id="{{ $key }}"
                        name="{{ $key }}"
                        type="{{ $type }}"
                        value="{{ $value }}"
                        class="border rounded px-3 py-2 w-full @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                    />
                @endif

                @if(!empty($fieldErrorsForKey))
                    <p class="text-sm text-red-600">{{ $fieldErrorsForKey[0] }}</p>
                @endif
            </div>
        @endforeach

        <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
            Continue
        </button>
    </form>

    <div class="mt-4">
        <a
            href="{{ isset($prevStepKey) ? route('stitch-wizard.step.show', ['id' => $wizardId, 'key' => $prevStepKey]) : route('stitch-wizard.show', ['id' => $wizardId]) }}"
            class="text-blue-600 hover:underline text-sm"
        >
            &larr; Back
        </a>
    </div>
</div>
