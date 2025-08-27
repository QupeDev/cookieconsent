document.addEventListener('DOMContentLoaded', function () {
    let cookieconsentLoad = 0;

    const cookieConfig = document.getElementById('cookie-config');
    if (cookieConfig) {
        const cookieConfigColor = cookieConfig.dataset.textColor;
        const cookieConfigBorderRadius = cookieConfig.dataset.borderRadius;
        const cookieConfigBorder = cookieConfig.dataset.border;
        const cookieConfigPadding = cookieConfig.dataset.padding;
        const cookieConfigTitleColor = cookieConfig.dataset.titleColor;

        if (cookieConfigColor) {
            document.body.style.setProperty('--cookie-text-color', cookieConfigColor);
        }
        if (cookieConfigBorderRadius) {
            document.body.style.setProperty('--cookie-border-radius', cookieConfigBorderRadius);
        }
        if (cookieConfigBorder) {
            document.body.style.setProperty('--cookie-border', cookieConfigBorder);
        }
        if (cookieConfigPadding) {
            document.body.style.setProperty('--cookie-padding', cookieConfigPadding);
        }
        if (cookieConfigTitleColor) {
            document.body.style.setProperty('--cookie-title-color', cookieConfigTitleColor);
        }
    }

    const handleFirstInteraction = function() {
        if (cookieconsentLoad === 0) {
            cookieconsentLoad = 1;

            if (sessionStorage.getItem('cookie_allow') !== '1') {
                const configEl = document.getElementById('cookie-config');
                if (!configEl) return;

                const palettes = {
                    site: {
                        popup: { 
                            background: configEl.dataset.popupBg,
                        },
                        button: {
                            background: configEl.dataset.btnBg,
                            border: configEl.dataset.btnBg,
                            text: configEl.dataset.btnText,
                            padding: '5px 20px'
                        }
                    }
                };

                const messageHTML = document.getElementById('cookie-message').innerHTML;
                const CookieConsent = window.CookieConsent;
                const cookie_show = new CookieConsent({
                    type: 'opt-in',
                    theme: 'classic',
                    palette: palettes.site,
                    position: 'bottom',
                    content: {
                        header: configEl.dataset.header,
                        message: messageHTML,
                        dismiss: 'Отклонить',
                        allow: 'Соглашаюсь',
                        deny: 'Отклонить',
                        link: 'Подробнее',
                        href: configEl.dataset.href,
                        close: '❌',
                        policy: 'Политика конфиденциальности',
                        target: '_blank'
                    },
                    layout: 'basic-header',
                    cookie: {
                        domain: configEl.dataset.siteUrl,
                        secure: true,
                        name: 'cookieconsent_status',
                        path: '/',
                        expiryDays: 365
                    },
                    showLink: true,
                    revokable: false,
                    revoke: {
                        expires: 365,
                        days: 365,
                        all: true
                    },
                    law: {
                        regionalLaw: false,
                        countryCode: 'RU'
                    },
                    location: false,
                    dismissOnScroll: false,
                    dismissOnTimeout: false,
                    dismissOnWindowClick: false,
                    dismissOnLinkClick: false,
                    dismissOnKeyPress: false
                });

                document.querySelector('.cc-ALLOW').addEventListener('click', function(){
                    if (typeof cookie_show !== 'undefined' && cookie_show && typeof CookieConsent !== 'undefined' && CookieConsent) {
                        cookie_show.setStatuses(CookieConsent.ALLOW);
                        cookie_show.close();
                        cookie_show.destroy();
                    } else {
                        console.error('cookie_show or CookieConsent is not defined. Cannot set statuses or close.');
                    }
                    sessionStorage.setItem('cookie_allow', '1');
                    console.log('sessionStorage: cookie_allow установлен в 1');
                });

                cookie_show.on("initialized", (status) => console.log('cookieconsent initialized'));
                cookie_show.on("statusChanged", function (cookieName, status, chosenBefore) {
                    console.log('statusChanged ' + status + 'for cookie' + cookieName);
                });
                cookie_show.on("error", console.error);
            }
        }
        
        ['mousedown', 'keydown', 'touchstart', 'scroll', 'mousemove', 'wheel'].forEach(function(eventName) {
            document.removeEventListener(eventName, handleFirstInteraction, false);
        });
    };

    ['mousedown', 'keydown', 'touchstart', 'scroll', 'mousemove', 'wheel'].forEach(function(eventName) {
        document.addEventListener(eventName, handleFirstInteraction, false);
    });
});