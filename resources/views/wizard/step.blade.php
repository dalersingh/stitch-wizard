<script src="https://cdn.tailwindcss.com"></script>
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
                @elseif ($type === 'multiselect')
                    <select
                        id="{{ $key }}"
                        name="{{ $key }}[]"
                        multiple
                        class="border rounded px-3 py-2 @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                    >
                        @foreach ($field['options'] ?? [] as $opt)
                            <option
                                value="{{ $opt['value'] }}"
                                @selected(is_array($value) && in_array($opt['value'], $value))
                            >
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                @elseif ($type === 'textarea')
                    <textarea
                        id="{{ $key }}"
                        name="{{ $key }}"
                        rows="4"
                        class="border rounded px-3 py-2 w-full @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                    >{{ $value }}</textarea>
                @elseif ($type === 'checkbox' || $type === 'toggle')
                    <input
                        id="{{ $key }}"
                        name="{{ $key }}"
                        type="checkbox"
                        value="1"
                        @checked($value)
                        class="@if($type==='toggle') peer sr-only @else border rounded @endif @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                    />
                    @if($type==='toggle')
                        <label for="{{ $key }}" class="inline-flex items-center cursor-pointer">
                            <span class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-blue-600 relative transition">
                                <span class="dot absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></span>
                            </span>
                        </label>
                    @endif
                @elseif ($type === 'radio')
                    <div class="flex flex-col gap-1">
                        @foreach ($field['options'] ?? [] as $opt)
                            <label class="inline-flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="{{ $key }}"
                                    value="{{ $opt['value'] }}"
                                    @checked($value == $opt['value'])
                                    class="border rounded @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                                >
                                <span>{{ $opt['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif ($type === 'date' || $type === 'time')
                    <input
                        id="{{ $key }}"
                        name="{{ $key }}"
                        type="{{ $type }}"
                        value="{{ $value }}"
                        class="border rounded px-3 py-2 w-full @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                    />
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
