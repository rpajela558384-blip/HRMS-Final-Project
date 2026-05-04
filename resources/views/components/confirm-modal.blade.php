@props([
    'id'      => 'confirm-modal',
    'title'   => 'Are you sure?',
    'message' => 'This action cannot be undone.',
    'confirmText' => 'Confirm',
    'confirmClass' => 'bg-teal-600 hover:bg-teal-700 text-white',
])

<div x-data="{ open: false }"
     x-on:open-modal-{{ $id }}.window="open = true"
     class="inline">
    {{ $trigger }}

    <template x-teleport="body">
        <div x-show="open" x-transition.opacity
             class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
             @keydown.escape.window="open = false">
            <div x-show="open" x-transition.scale
                 @click.outside="open = false"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-2">{{ $title }}</h3>
                <p class="text-sm text-slate-500 mb-6">{{ $message }}</p>
                <div class="flex gap-3 justify-end">
                    <button @click="open = false"
                            type="button"
                            class="px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-600 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <span>{{ $action }}</span>
                </div>
            </div>
        </div>
    </template>
</div>
