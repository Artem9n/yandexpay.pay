export function ready(callback) {
	if (typeof BX !== 'undefined' && BX.ready) {
		BX.ready(callback);
	} else if (typeof jQuery !== 'undefined' && jQuery.ready) {
		jQuery.ready(callback);
	} else if (document.readyState === 'complete' || document.readyState === 'interactive') {
		setTimeout(callback, 1);
	} else {
		document.addEventListener('DOMContentLoaded', callback);
	}
}