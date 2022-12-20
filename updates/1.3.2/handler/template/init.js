let ready = false;

if (test() && false) {
	run();
} else {
	waitTimeout();
	waitReady();
}

function waitReady() {
	// BX

	if (typeof BX !== 'undefined' && BX.ready != null) {
		BX.ready(onReady);
	}

	// jQuery

	if (typeof $ === 'function' && $.fn != null && $.fn.ready != null) {
		$(onReady);
	}

	// plain

	if (document.readyState === 'complete' || document.readyState === 'interactive') {
		setTimeout(onReady, 1);
	} else {
		document.addEventListener('DOMContentLoaded', onReady);
	}
}

function onReady() {
	if (ready) { return; }

	if (test()) {
		ready = true;
		run();
	}
}

function waitTimeout() {
	if (ready) { return; }

	if (test()) {
		ready = true;
		run();
		return;
	}

	setTimeout(waitTimeout, 500);
}

function test() {
	return (
		typeof BX !== 'undefined'
		&& BX.YandexPay != null
		&& BX.YandexPay.Factory != null
	);
}