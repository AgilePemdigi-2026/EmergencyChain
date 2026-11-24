
@props([
    'id' => 'password',
    'name' => 'password',
    'placeholder' => 'Masukkan password',
    'autocomplete' => 'current-password',
])

<div class="relative" data-password-wrapper>

    <input
        type="password"
        id="{{ $id }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        autocomplete="{{ $autocomplete }}"
        {{ $attributes->class([
            'password-toggle-input w-full rounded-xl border',
            'border-red-500' => $errors->has($name),
            'border-slate-200' => !$errors->has($name),
            'bg-white py-3 pl-4 pr-12',
            'text-sm text-slate-800',
            'outline-none transition',
            'placeholder:text-slate-400',
            'focus:border-red-500',
            'focus:ring-2 focus:ring-red-100',
        ]) }}
    >

    <button
        type="button"
        data-password-toggle
        aria-label="Tampilkan password"
        aria-pressed="false"
        aria-controls="{{ $id }}"
        class="absolute inset-y-0 right-0 flex
               items-center rounded-r-xl px-4
               text-slate-400 transition
               hover:text-red-500
               focus-visible:outline-2
               focus-visible:outline-red-500"
    >

        <!-- Eye Icon -->
        <svg
            data-eye-open
            class="h-5 w-5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>

        <!-- Eye Off Icon -->
        <svg
            data-eye-closed
            class="hidden h-5 w-5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M3 3l18 18"/>
            <path d="M10.6 10.6a2 2 0 002.8 2.8"/>
            <path d="M9.9 5.2A10.9 10.9 0 0112 5c6 0 10 7 10 7a16.8 16.8 0 01-3 3.8"/>
            <path d="M6.2 6.2C3.6 8.5 2 12 2 12s4 7 10 7a10 10 0 005.8-1.8"/>
        </svg>

    </button>

</div>
