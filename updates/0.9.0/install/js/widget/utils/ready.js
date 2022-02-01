export function ready(callback) {
	if (document.readyState === 'complete' || document.readyState === 'interactive') {
		setTimeout(callback, 1);
	} else {
		document.addEventListener('DOMContentLoaded', callback);
	}
}