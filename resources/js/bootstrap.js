import axios from 'axios';

function readCookie(name) {
    const escaped = name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1');
    const match = document.cookie.match(new RegExp(`(?:^|; )${escaped}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : null;
}

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

const csrf = document.querySelector('meta[name="csrf-token"]')?.content || readCookie('XSRF-TOKEN');
if (csrf) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
}
