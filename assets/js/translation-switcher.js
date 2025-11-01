(function () {
    if (typeof window.gufteTranslation === 'undefined') {
        return;
    }

    var cfg = window.gufteTranslation;
    var contentSelector = (cfg.selectors && cfg.selectors.content) || '.lyrics-content .entry-content';
    var containerSelector = (cfg.selectors && cfg.selectors.container) || '.lyrics-content';
    var contentEl = document.querySelector(contentSelector);
    var containerEl = document.querySelector(containerSelector);

    if (!contentEl) {
        return;
    }

    var currentLang = cfg.currentLang || getLangFromLocation();
    var isLoading = false;
    var baseUrl = cfg.permalink || window.location.href;
    var basePath = new URL(baseUrl, window.location.origin).pathname;
    var loaderStylesInserted = false;

    function ensureLoaderStyles() {
        if (loaderStylesInserted || !containerEl) {
            return;
        }
        var style = document.createElement('style');
        style.textContent =
            '.is-loading-translation{position:relative;}' +
            '.is-loading-translation::after{' +
            'content:"";position:absolute;inset:0;background:rgba(255,255,255,0.6);' +
            'z-index:10;pointer-events:none;}' +
            '.is-loading-translation::before{' +
            'content:"";position:absolute;top:50%;left:50%;width:36px;height:36px;' +
            'margin:-18px 0 0 -18px;border:3px solid rgba(59,130,246,0.35);' +
            'border-top-color:rgba(59,130,246,0.85);border-radius:50%;' +
            'animation:gufte-spin 0.8s linear infinite;z-index:11;pointer-events:none;}' +
            '[data-translation-active="true"]{' +
            'background-color:rgba(59,130,246,0.1);color:#1d4ed8 !important;font-weight:600;' +
            'text-decoration:none;border-color:rgba(59,130,246,0.25);}' +
            '@keyframes gufte-spin{to{transform:rotate(360deg);}}';
        document.head.appendChild(style);
        loaderStylesInserted = true;
    }

    function getLangFromLocation() {
        var params = new URLSearchParams(window.location.search);
        return params.get('lang') || '';
    }

    function buildUrl(lang) {
        if (!lang) {
            return baseUrl;
        }
        var url = new URL(baseUrl, window.location.origin);
        url.searchParams.set('lang', lang);
        return url.toString();
    }

    function setLoading(state) {
        isLoading = state;
        if (!containerEl) {
            return;
        }
        containerEl.classList.toggle('is-loading-translation', state);
    }

    function setActiveLink(lang) {
        var links = document.querySelectorAll('a[href*="?lang="]');
        links.forEach(function (link) {
            link.removeAttribute('data-translation-active');
            var linkUrl = new URL(link.href, window.location.origin);
            if (linkUrl.pathname === basePath) {
                var linkLang = linkUrl.searchParams.get('lang') || '';
                if (linkLang === lang) {
                    link.setAttribute('data-translation-active', 'true');
                }
            }
        });
    }

    function bindLinks(scope) {
        var root = scope || document;
        var links = root.querySelectorAll('a[href*="?lang="]');
        links.forEach(function (link) {
            if (link.dataset.translationBound === '1') {
                return;
            }
            var linkUrl = new URL(link.href, window.location.origin);
            if (linkUrl.pathname !== basePath) {
                return;
            }
            link.dataset.translationBound = '1';
            link.addEventListener('click', function (event) {
                var lang = linkUrl.searchParams.get('lang') || '';
                if (lang === currentLang) {
                    event.preventDefault();
                    return;
                }
                event.preventDefault();
                fetchTranslation(lang, link);
            });
        });
    }

    function fetchTranslation(lang, triggerLink, options) {
        if (isLoading) {
            return;
        }

        options = Object.assign(
            {
                pushState: true,
                scrollIntoView: true
            },
            options || {}
        );

        setLoading(true);

        var requestUrl = cfg.restUrl + cfg.postId;
        if (lang) {
            requestUrl += '?lang=' + encodeURIComponent(lang);
        }

        fetch(requestUrl, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('request_failed');
                }
                return response.json();
            })
            .then(function (data) {
                if (typeof data.content !== 'undefined') {
                    contentEl.innerHTML = data.content;
                    bindLinks(contentEl);
                }

                currentLang = lang;
                setActiveLink(lang);

                if (options.pushState) {
                    var historyUrl = buildUrl(lang);
                    history.pushState({ lang: lang }, '', historyUrl);
                }

                if (options.scrollIntoView && containerEl) {
                    containerEl.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            })
            .catch(function () {
                if (triggerLink && triggerLink.href) {
                    window.location.href = triggerLink.href;
                }
            })
            .finally(function () {
                setLoading(false);
            });
    }

    function initHistory() {
        try {
            history.replaceState({ lang: currentLang }, '', window.location.href);
        } catch (err) {
            // eslint-disable-next-line no-console
            console.warn('History API unavailable', err);
        }

        window.addEventListener('popstate', function (event) {
            var lang =
                event.state && typeof event.state.lang !== 'undefined'
                    ? event.state.lang
                    : getLangFromLocation();
            if (lang === currentLang) {
                return;
            }
            fetchTranslation(lang, null, { pushState: false, scrollIntoView: false });
        });
    }

    function init() {
        ensureLoaderStyles();
        bindLinks(document);
        setActiveLink(currentLang);
        initHistory();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
