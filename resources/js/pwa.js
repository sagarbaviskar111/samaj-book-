// PWA: service worker, install prompt, Web Push opt-in.
let deferredPrompt = null;
const listeners = [];
const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;
const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

function emit() { listeners.forEach((fn) => fn(state())); }
function state() {
    return { canInstall: !!deferredPrompt, ios: isIos && !isStandalone(), installed: isStandalone() };
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    emit();
});
window.addEventListener('appinstalled', () => { deferredPrompt = null; emit(); });

function urlBase64ToUint8Array(b64) {
    const pad = '='.repeat((4 - (b64.length % 4)) % 4);
    const raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
}
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content
    || document.querySelector('input[name="_token"]')?.value || '';

async function post(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(body),
    });
}

const SamajPWA = {
    onChange(fn) { listeners.push(fn); fn(state()); },
    state,
    async install() {
        if (!deferredPrompt) return false;
        deferredPrompt.prompt();
        const choice = await deferredPrompt.userChoice;
        deferredPrompt = null;
        emit();
        return choice.outcome === 'accepted';
    },
    pushSupported() {
        return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window && !!window.SamajVapidPublic;
    },
    async enablePush() {
        if (!this.pushSupported()) throw new Error('Push is not available on this device.');
        const perm = await Notification.requestPermission();
        if (perm !== 'granted') throw new Error('Permission was not granted.');
        const reg = await navigator.serviceWorker.ready;
        let sub = await reg.pushManager.getSubscription();
        if (!sub) {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(window.SamajVapidPublic),
            });
        }
        const res = await post('/push/subscribe', sub.toJSON());
        if (!res.ok) throw new Error('Could not save subscription.');
        return true;
    },
    async disablePush() {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();
        if (sub) {
            await post('/push/unsubscribe', { endpoint: sub.endpoint });
            await sub.unsubscribe();
        }
    },
};
window.SamajPWA = SamajPWA;

// Opt-in buttons (only on pages that render them, after an explicit tap).
document.addEventListener('DOMContentLoaded', async () => {
    const cta = document.getElementById('push-cta');
    if (cta && SamajPWA.pushSupported() && Notification.permission !== 'denied') {
        let subscribed = false;
        try {
            const reg = await navigator.serviceWorker.ready;
            subscribed = !!(await reg.pushManager.getSubscription()) && Notification.permission === 'granted';
        } catch (e) { /* ignore */ }
        if (!subscribed || cta.querySelector('[data-disable-push]')) cta.classList.remove('hidden');
        if (subscribed) cta.querySelector('[data-enable-push]')?.classList.add('hidden');
    }
});

document.addEventListener('click', async (e) => {
    const on = e.target.closest('[data-enable-push]');
    const off = e.target.closest('[data-disable-push]');
    if (on) {
        on.disabled = true;
        try { await SamajPWA.enablePush(); on.textContent = 'Enabled'; }
        catch (err) { on.disabled = false; alert(err.message); }
    } else if (off) {
        await SamajPWA.disablePush();
        document.querySelector('[data-enable-push]')?.classList.remove('hidden');
    }
});
