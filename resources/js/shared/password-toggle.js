
function initPasswordToggles() {
    const wrappers = document.querySelectorAll(
        '[data-password-wrapper]'
    );

    wrappers.forEach((wrapper) => {
        const input = wrapper.querySelector(
            '.password-toggle-input'
        );

        const button = wrapper.querySelector(
            '[data-password-toggle]'
        );

        const eyeOpen = wrapper.querySelector(
            '[data-eye-open]'
        );

        const eyeClosed = wrapper.querySelector(
            '[data-eye-closed]'
        );

        if (!input || !button || !eyeOpen || !eyeClosed) {
            return;
        }

        button.addEventListener('click', () => {
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';

            eyeOpen.classList.toggle('hidden', isHidden);
            eyeClosed.classList.toggle('hidden', !isHidden);

            button.setAttribute(
                'aria-label',
                isHidden
                    ? 'Sembunyikan password'
                    : 'Tampilkan password'
            );

            button.setAttribute(
                'aria-pressed',
                String(isHidden)
            );
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initPasswordToggles
    );
} else {
    initPasswordToggles();
}
