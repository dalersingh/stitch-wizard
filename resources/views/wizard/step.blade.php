<script src="https://cdn.tailwindcss.com"></script>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-white p-4">
<div data-wizard-root class="wizard-step-container w-full max-w-xl bg-white rounded-xl shadow-lg p-8">
    <h1 class="text-xl font-bold mb-6" tabindex="-1">
        {{ $step['title'] ?? $stepKey }}
    </h1>

    @if (isset($stepIndex, $totalSteps))
        @php $pct = intval(($stepIndex / max($totalSteps,1))*100); @endphp
        <div class="w-full h-2 bg-slate-200 rounded mb-4 overflow-hidden">
            <div class="h-full bg-indigo-500 transition-all" style="width: {{ $pct }}%"></div>
        </div>
        <p class="text-sm text-gray-500 mb-4">
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
                        class="border rounded-lg px-3 py-2 w-full border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
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
                        class="border rounded-lg px-3 py-2 w-full border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
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
                        class="border rounded-lg px-3 py-2 w-full border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                    >{{ $value }}</textarea>
                @elseif ($type === 'checkbox' || $type === 'toggle')
                    <input
                        id="{{ $key }}"
                        name="{{ $key }}"
                        type="checkbox"
                        value="1"
                        @checked($value)
                        class="@if($type==='toggle') peer sr-only @else border rounded-lg @endif @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
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
                                    class="border rounded-lg @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
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
                        class="border rounded-lg px-3 py-2 w-full border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                    />
                @else
                    <input
                        id="{{ $key }}"
                        name="{{ $key }}"
                        type="{{ $type }}"
                        value="{{ $value }}"
                        class="border rounded-lg px-3 py-2 w-full border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @if(!empty($fieldErrorsForKey)) border-red-500 @endif"
                    />
                @endif

                @if(!empty($fieldErrorsForKey))
                    <p class="text-sm text-red-600">{{ $fieldErrorsForKey[0] }}</p>
                @endif
            </div>
        @endforeach

        <button
            type="submit"
            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition"
        >
            Continue
        </button>
    </form>

    <div class="mt-4 text-center">
        <a
            href="{{ isset($prevStepKey) ? route('stitch-wizard.step.show', ['id' => $wizardId, 'key' => $prevStepKey]) : route('stitch-wizard.show', ['id' => $wizardId]) }}"
            class="text-slate-500 hover:text-slate-700 text-sm"
        >
            &larr; Back
        </a>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const hydrate = (html) => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    return doc.querySelector('[data-wizard-root]');
  };
  const root = () => document.querySelector('[data-wizard-root]');
  const toggleBusy = (on) => root().classList.toggle('opacity-50 pointer-events-none', on);
  const swap = (newRoot, url, push = true) => {
    root().replaceWith(newRoot);
    if (push) history.pushState({}, '', url);
    window.scrollTo({ top: 0 });
    newRoot.querySelector('h1')?.focus();
  };
  const handle = (url, opts = null, push = true) => {
    toggleBusy(true);
    fetch(url, opts).then(r => r.text()).then(html => {
      const fresh = hydrate(html);
      if (fresh) swap(fresh, url, push);
    }).finally(() => toggleBusy(false));
  };
  document.body.addEventListener('submit', e => {
    const form = e.target.closest('form');
    if (!form) return;
    e.preventDefault();
    const data = new FormData(form);
    handle(form.action, { method: 'POST', body: data, headers: { 'X-Requested-With': 'fetch' } });
  });
  document.body.addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    if (!a.href.includes('/wizard')) return;
    if (a.target && a.target !== '_self') return;
    e.preventDefault();
    handle(a.href);
  });
  window.addEventListener('popstate', () => handle(location.href, null, false));
});
</script>
