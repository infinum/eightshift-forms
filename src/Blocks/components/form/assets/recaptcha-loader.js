let pending;
let retryBootstrap = false;

const getApi = (isEnterprise) => (isEnterprise ? window.grecaptcha?.enterprise : window.grecaptcha);

export function loadRecaptcha(state) {
	if (pending) {
		return pending;
	}

	const isEnterprise = state.getStateCaptchaIsEnterprise();
	let script;
	let timeout;

	pending = new Promise((resolve, reject) => {
		const ready = () => {
			const api = getApi(isEnterprise);

			if (!api?.ready) {
				reject(new Error('reCAPTCHA API unavailable'));

				return;
			}
			api.ready(() => resolve(api));
		};

		timeout = setTimeout(() => reject(new Error('reCAPTCHA load timed out')), 15000);

		if (getApi(isEnterprise)?.ready && (!retryBootstrap || getApi(isEnterprise)?.execute)) {
			ready();

			return;
		}

		script = document.createElement('script');
		script.src = state.getStateCaptchaScriptUrl();
		script.async = true;
		script.addEventListener('load', ready, { once: true });
		script.addEventListener('error', () => reject(new Error('reCAPTCHA failed to load')), { once: true });
		document.head.append(script);
	})
		.catch((error) => {
			script?.remove();
			// A bootstrap can expose ready() even if its secondary script failed.
			retryBootstrap = true;
			pending = undefined;

			throw error;
		})
		.finally(() => clearTimeout(timeout));

	return pending;
}

const observedForms = new WeakSet();

export function preloadRecaptcha(state, form) {
	if (!form || observedForms.has(form)) {
		return;
	}
	observedForms.add(form);

	const preload = () => {
		controller.abort();
		observer?.disconnect();
		// If preloading fails, submission can retry and report the error.
		loadRecaptcha(state).catch(() => {});
	};
	const controller = new AbortController();
	let observer;

	if (state.getStateCaptchaLoadOnInit() || !state.getStateCaptchaLazyLoad()) {
		preload();

		return;
	}

	for (const event of ['focusin', 'pointerdown', 'keydown']) {
		form.addEventListener(event, preload, { signal: controller.signal });
	}

	if ('IntersectionObserver' in window) {
		observer = new IntersectionObserver(
			(entries) => {
				if (entries.some(({ isIntersecting }) => isIntersecting)) {
					preload();
				}
			},
			{ rootMargin: '200px' },
		);
		observer.observe(form);
	}
}
