function readCookie(name) {
    const escaped = name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1');
    const match = document.cookie.match(new RegExp(`(?:^|; )${escaped}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : null;
}

export function csrfToken() {
    const fromMeta = document.querySelector('meta[name="csrf-token"]')?.content;
    if (fromMeta) {
        return fromMeta;
    }

    const fromPlayer = document.querySelector('#activity-player')?.dataset?.csrfToken;
    if (fromPlayer) {
        return fromPlayer;
    }

    return readCookie('XSRF-TOKEN');
}

export function setCsrfToken(token) {
    if (!token) {
        return;
    }

    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        meta.setAttribute('content', token);
    }

    document.querySelectorAll('[data-csrf-token]').forEach((el) => {
        el.dataset.csrfToken = token;
    });
}

export async function refreshCsrfToken() {
    const res = await fetch('/csrf-token', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (!res.ok) {
        throw new Error('csrf refresh failed');
    }

    const data = await res.json();
    if (data.token) {
        setCsrfToken(data.token);
    }

    return data.token ?? null;
}

function buildHeaders(options, token) {
    const headers = new Headers(options.headers ?? {});

    if (!headers.has('Accept')) {
        headers.set('Accept', 'application/json');
    }

    if (!headers.has('X-Requested-With')) {
        headers.set('X-Requested-With', 'XMLHttpRequest');
    }

    const body = options.body;
    const isFormData = typeof FormData !== 'undefined' && body instanceof FormData;

    if (!isFormData && !headers.has('Content-Type') && body && typeof body === 'string') {
        headers.set('Content-Type', 'application/json');
    }

    if (token) {
        headers.set('X-CSRF-TOKEN', token);
    }

    return headers;
}

export async function csrfFetch(url, options = {}, { retry419 = true } = {}) {
    const token = csrfToken();
    const headers = buildHeaders(options, token);

    const response = await fetch(url, {
        ...options,
        headers,
        credentials: options.credentials ?? 'same-origin',
    });

    if (response.status === 419 && retry419) {
        try {
            await refreshCsrfToken();
            const retryHeaders = buildHeaders(options, csrfToken());
            return fetch(url, {
                ...options,
                headers: retryHeaders,
                credentials: options.credentials ?? 'same-origin',
            });
        } catch {
            return response;
        }
    }

    return response;
}

export async function readErrorMessage(response, fallback = 'Une erreur est survenue.') {
    try {
        const data = await response.clone().json();
        if (typeof data?.message === 'string' && data.message !== '') {
            return data.message;
        }
        const firstError = data?.errors ? Object.values(data.errors).flat()?.[0] : null;
        if (typeof firstError === 'string' && firstError !== '') {
            return firstError;
        }
    } catch {
        // ignore
    }

    return fallback;
}
