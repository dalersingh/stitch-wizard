<script src="https://cdn.tailwindcss.com"></script>
<div class="wizard-success-container max-w-xl mx-auto">
    <h1 class="text-xl font-bold mb-6">Wizard Completed</h1>
    
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <p>Your information has been submitted successfully!</p>
    </div>
    
    <div class="mt-4">
        <a 
            href="{{ route('stitch-wizard.show', ['id' => $wizardId]) }}" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
            Start Over
        </a>
    </div>
</div>
