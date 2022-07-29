export class MapsLoader {

	static instance;

	static defaults = {
		scriptUrl: 'https://enterprise.api-maps.yandex.ru/2.0/?load=package.full&mode=release&lang=ru&wizard=bitrix', // todo locale and wizard
		loadStep: 100,
		loadTimeout: 30000,
	};

	_loadPromise;
	_loadElapsed;

	static getInstance() : MapsLoader {
		if (this.instance == null) {
			this.instance = new this();
		}

		return this.instance;
	}

	constructor(options: Object = {}) {
		this.options = Object.assign({}, this.constructor.defaults, options);
	}

	load(apiKey: string) : Promise {
		if (this._loadPromise != null) { return this._loadPromise; }

		const loaded = this.loaded();

		if (loaded != null) { return Promise.resolve(loaded); }

		this._loadElapsed = 0;
		this._loadPromise = new Promise((resolve, reject) => {
			this.injectScript(apiKey);
			this.waitLoaded(resolve, reject);
		});

		this._loadPromise.finally(() => {
			this._loadPromise = null;
		})

		return this._loadPromise;
	}

	loaded() {
		if (window.ymaps?.Map == null) { return null; }

		return window.ymaps;
	}

	injectScript(apiKey: string) : void {
		const anchor = window.document.head || window.document.body || window.document.documentElement;
		const script = document.createElement('script');

		script.src = this.options.scriptUrl + '&apikey=' + encodeURIComponent(apiKey);

		anchor.appendChild(script);

		script.onload = () => {
			anchor.removeChild(script);
		};
	}

	waitLoaded(resolve, reject) : void {
		const loaded = this.loaded();

		if (loaded != null) {
			resolve(loaded);
			return;
		}

		if (this._loadElapsed > this.options.loadTimeout) {
			reject(new Error('cant load ymaps')); // todo lang message
		}

		setTimeout(
			() => { this.waitLoaded(resolve, reject) },
			this.options.loadStep
		);

		this._loadElapsed += this.options.loadStep;
	}

}