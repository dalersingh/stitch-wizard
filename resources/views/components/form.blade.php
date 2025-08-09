@props([
    'action' => '',
    'method' => 'POST',
])

<form method="{{ $method }}" action="{{ $action }}" {{ $attributes }}>
    @csrf
    
    <div class="wizard-form-content">
        {{ $slot }}
    </div>
    
    <div class="wizard-form-actions">
        <button type="submit" class="wizard-button wizard-button-primary">
            Continue
        </button>
    </div>
</form>
