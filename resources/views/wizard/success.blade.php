<script src="https://cdn.tailwindcss.com"></script>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-white p-4">
    <div data-wizard-root class="w-full max-w-xl bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="mx-auto mb-6 flex items-center justify-center w-20 h-20 rounded-full bg-indigo-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M20.285 6.709a1 1 0 01.006 1.414l-9 9a1 1 0 01-1.414.002l-4.5-4.5a1 1 0 011.414-1.414l3.793 3.793 8.293-8.293a1 1 0 011.408-.002z" clip-rule="evenodd" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold mb-4" tabindex="-1">Wizard Completed</h1>

        <p class="text-slate-600 mb-8">Your information has been submitted successfully!</p>

        <a
            href="{{ route('stitch-wizard.show', ['id' => $wizardId]) }}"
            class="w-full inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition"
        >
            Start Over
        </a>
    </div>
</div>
