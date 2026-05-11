(function () {
    'use strict';

    var pingInterval = 5 * 60 * 1000;
    var baseElement = document.querySelector('base');
    var baseHref = baseElement ? baseElement.href : '/';
    var pingUrl = '/Security/ping';

    try {
        pingUrl = new URL('Security/ping', baseHref).toString();
    } catch (e) {
        // Use fallback URL
    }

    var pingSession = function () {
        if (typeof window.fetch === 'function') {
            window.fetch(pingUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(function () {});
            return;
        }

        var request = new XMLHttpRequest();
        request.open('POST', pingUrl, true);
        request.withCredentials = true;
        request.send();
    };

    pingSession();
    window.setInterval(pingSession, pingInterval);
})();
