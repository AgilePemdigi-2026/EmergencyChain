<div
    id="notification-widget"
    class="relative"
    data-index-url="{{ route('notifications.index') }}"
    data-read-all-url="{{ route('notifications.read-all') }}"
    data-read-url-template="{{ route('notifications.read', ['notificationId' => '__ID__']) }}"
>
    <!-- Tombol lonceng -->
    <button
        type="button"
        id="notification-toggle"
        aria-label="Buka notifikasi"
        aria-expanded="false"
        aria-controls="notification-dropdown"
        class="relative flex h-10 w-10 items-center justify-center
               rounded-xl border border-slate-200 bg-white
               text-slate-600 transition hover:bg-rose-50
               hover:text-red-500 focus-visible:outline-2
               focus-visible:outline-red-500"
    >
        <svg xmlns="http://www.w3.org/2000/svg"
             width="21" height="21" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
            <path d="M10 21h4"/>
        </svg>

        <span
            id="notification-badge"
            class="absolute -right-2 -top-2 hidden min-w-5
                   rounded-full bg-red-500 px-1 py-0.5
                   text-center text-[10px] font-bold
                   leading-4 text-white"
        >0</span>
    </button>

    <!-- Dropdown -->
    <div
        id="notification-dropdown"
        class="absolute right-0 top-12 z-50 hidden
               w-[min(21rem,calc(100vw-2rem))]
               overflow-hidden rounded-2xl border border-slate-200
               bg-white shadow-xl sm:w-96"
    >
        <div class="flex items-center justify-between
                    border-b border-slate-100 px-4 py-3">
            <div>
                <h2 class="text-sm font-bold text-slate-800">
                    Notifikasi
                </h2>
                <p id="notification-count"
                   class="text-xs text-slate-400"
                   aria-live="polite">
                    Memuat...
                </p>
            </div>

            <button
                type="button"
                id="notification-read-all"
                class="text-xs font-semibold text-red-500
                       hover:text-red-600"
            >
                Tandai semua dibaca
            </button>
        </div>

        <div
            id="notification-list"
            class="max-h-96 overflow-y-auto"
            aria-label="Daftar notifikasi"
        >
            <p class="px-4 py-8 text-center text-sm text-slate-400">
                Memuat notifikasi...
            </p>
        </div>

        <div
            id="notification-error"
            role="status"
            class="hidden border-t border-red-100
                   px-4 py-2 text-xs text-red-600"
        ></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('notification-widget');
    if (!widget) return;

    const toggle = document.getElementById('notification-toggle');
    const dropdown = document.getElementById('notification-dropdown');
    const badge = document.getElementById('notification-badge');
    const count = document.getElementById('notification-count');
    const list = document.getElementById('notification-list');
    const readAll = document.getElementById('notification-read-all');
    const errorBox = document.getElementById('notification-error');

    const indexUrl = widget.dataset.indexUrl;
    const readAllUrl = widget.dataset.readAllUrl;
    const readUrlTemplate = widget.dataset.readUrlTemplate;
    const csrfToken = @json(csrf_token());

    let loading = false;

    function showError(message = '') {
        errorBox.textContent = message;
        errorBox.classList.toggle('hidden', !message);
    }

    function safeUrl(value) {
        if (typeof value !== 'string' || !value.trim()) {
            return null;
        }

        try {
            const url = new URL(value, window.location.origin);

            if (
                url.origin !== window.location.origin ||
                !['http:', 'https:'].includes(url.protocol)
            ) {
                return null;
            }

            return url.href;
        } catch {
            return null;
        }
    }

    async function patch(url) {
        const response = await fetch(url, {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        if (!response.ok) {
            throw new Error('Gagal memperbarui notifikasi.');
        }

        return response.json();
    }

    function renderNotifications(items) {
        list.replaceChildren();

        if (!items.length) {
            const empty = document.createElement('p');
            empty.className =
                'px-4 py-8 text-center text-sm text-slate-400';
            empty.textContent = 'Belum ada notifikasi.';
            list.appendChild(empty);
            return;
        }

        items.forEach((notification) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className =
                'block w-full border-b border-slate-100 px-4 py-3 ' +
                'text-left transition hover:bg-rose-50 ' +
                (notification.read ? 'bg-white' : 'bg-rose-50/60');

            const header = document.createElement('div');
            header.className = 'flex items-start gap-2';

            const content = document.createElement('div');
            content.className = 'min-w-0 flex-1';

            const title = document.createElement('p');
            title.className =
                'text-sm font-semibold text-slate-800';
            title.textContent = notification.title || 'Notifikasi';

            const message = document.createElement('p');
            message.className = 'mt-1 text-xs text-slate-600';
            message.textContent = notification.message || '';

            const time = document.createElement('p');
            time.className = 'mt-2 text-[11px] text-slate-400';
            time.textContent = notification.time || '';

            content.append(title, message, time);
            header.appendChild(content);

            if (!notification.read) {
                const dot = document.createElement('span');
                dot.className =
                    'mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500';
                dot.setAttribute('aria-label', 'Belum dibaca');
                header.appendChild(dot);
            }

            item.appendChild(header);

            item.addEventListener('click', async () => {
                if (item.disabled) return;

                item.disabled = true;
                showError();

                try {
                    if (!notification.read) {
                        const readUrl = readUrlTemplate.replace(
                            '__ID__',
                            encodeURIComponent(notification.id)
                        );

                        await patch(readUrl);
                    }

                    const destination = safeUrl(notification.url);

                    if (destination) {
                        window.location.assign(destination);
                    } else {
                        await loadNotifications();
                    }
                } catch {
                    showError('Gagal membuka notifikasi. Coba lagi.');
                    item.disabled = false;
                }
            });

            list.appendChild(item);
        });
    }

    async function loadNotifications() {
        if (loading || document.hidden) return;

        loading = true;

        try {
            const response = await fetch(indexUrl, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil notifikasi.');
            }

            const data = await response.json();
            const unread = Number(data.unread_count) || 0;

            badge.textContent = unread > 99 ? '99+' : String(unread);
            badge.classList.toggle('hidden', unread === 0);

            count.textContent =
                unread + ' notifikasi belum dibaca';

            if (!dropdown.classList.contains('hidden')) {
                renderNotifications(data.notifications || []);
            }

            showError();
        } catch {
            if (!dropdown.classList.contains('hidden')) {
                showError('Notifikasi gagal dimuat. Coba lagi.');
            }
        } finally {
            loading = false;
        }
    }

    toggle.addEventListener('click', () => {
        const isOpening = dropdown.classList.contains('hidden');

        dropdown.classList.toggle('hidden', !isOpening);
        toggle.setAttribute('aria-expanded', String(isOpening));

        if (isOpening) loadNotifications();
    });

    readAll.addEventListener('click', async () => {
        readAll.disabled = true;
        showError();

        try {
            await patch(readAllUrl);
            await loadNotifications();
        } catch {
            showError('Gagal menandai semua notifikasi.');
        } finally {
            readAll.disabled = false;
        }
    });

    document.addEventListener('click', (event) => {
        if (!widget.contains(event.target)) {
            dropdown.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' &&
            !dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) loadNotifications();
    });

    loadNotifications();
    setInterval(loadNotifications, 5000);
});
</script>
