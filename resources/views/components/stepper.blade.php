<ol class="wizard-steps" aria-label="Steps">
    @foreach($steps as $key => $label)
        <li @if($key === $current) aria-current="step" @endif>{{ $label }}</li>
    @endforeach
</ol>
