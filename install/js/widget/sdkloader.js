export class Sdkloader {

	static instance = null;

	static getInstance() {
		if (this.instance == null) {
			this.instance = new Sdkloader();
		}

		return this.instance;
	}

	load() : Promise {
		if (this._loadPromise != null) { return this._loadPromise; }

		this._loadPromise = new Promise((resolve, reject) => {
			if (this.testGlobal()) {
				resolve();
				return;
			}

			const script = document.createElement('script');
			const anchor = document.getElementsByTagName("script")[0] || document.body;

			script.type = "text/javascript";
			script.async = true;
			script.src = 'https://pay.yandex.ru/sdk/v1/pay.js';

			script.onload = () => {
				this._loadPromise = null;
				resolve();
			};
			script.onerror = () => {
				this._loadPromise = null;
				script.remove();

				reject(new Error('cant load yandex pay sdk library'));
			};

			anchor.parentNode.insertBefore(script, anchor);
		});

		return this._loadPromise;
	}

	testGlobal() : boolean {
		return window.YaPay != null;
	}
}