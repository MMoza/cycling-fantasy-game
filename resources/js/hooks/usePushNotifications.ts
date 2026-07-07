import { useEffect, useState, useCallback } from 'react';

function urlBase64ToUint8Array(base64String: string): ArrayBuffer {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray.buffer;
}

export function usePushNotifications() {
    const [isSupported, setIsSupported] = useState(false);
    const [permission, setPermission] = useState<NotificationPermission>('default');
    const [subscription, setSubscription] = useState<PushSubscription | null>(null);
    const [loading, setLoading] = useState(false);
    const [swRegistration, setSwRegistration] = useState<ServiceWorkerRegistration | null>(null);

    useEffect(() => {
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            setIsSupported(true);
            setPermission(Notification.permission);
            registerServiceWorker();
        }
    }, []);

    async function registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            await navigator.serviceWorker.ready;
            setSwRegistration(registration);
            const sub = await registration.pushManager.getSubscription();
            setSubscription(sub);
        } catch (error) {
            console.error('[Push] Failed to register service worker:', error);
        }
    }

    async function fetchVapidKey(): Promise<string | null> {
        try {
            const res = await fetch('/push/vapid-key', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            return data.public_key || null;
        } catch {
            return null;
        }
    }

    const subscribe = useCallback(async () => {
        if (!isSupported) {
            console.warn('[Push] Not supported');
            return;
        }

        setLoading(true);
        try {
            const vapidKey = await fetchVapidKey();
            if (!vapidKey) {
                console.error('[Push] VAPID public key not available from server');
                setLoading(false);
                return;
            }

            const perm = await Notification.requestPermission();
            setPermission(perm);

            if (perm !== 'granted') {
                console.warn('[Push] Permission denied');
                setLoading(false);
                return;
            }

            const reg = swRegistration || await navigator.serviceWorker.ready;
            const sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey),
            });

            setSubscription(sub);

            const subscriptionJson = sub.toJSON();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const res = await fetch('/push-subscriptions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    endpoint: subscriptionJson.endpoint,
                    keys: subscriptionJson.keys,
                }),
            });

            if (!res.ok) {
                console.error('[Push] Server error:', res.status, await res.text());
            }
        } catch (error) {
            console.error('[Push] Failed to subscribe:', error);
        } finally {
            setLoading(false);
        }
    }, [isSupported, swRegistration]);

    const unsubscribe = useCallback(async () => {
        if (!subscription) return;

        setLoading(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            await fetch('/push-subscriptions', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                }),
            });

            await subscription.unsubscribe();
            setSubscription(null);
        } catch (error) {
            console.error('[Push] Failed to unsubscribe:', error);
        } finally {
            setLoading(false);
        }
    }, [subscription]);

    return {
        isSupported,
        permission,
        isSubscribed: !!subscription,
        loading,
        subscribe,
        unsubscribe,
    };
}
