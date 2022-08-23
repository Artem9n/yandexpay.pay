export class MapsLoader {

	static instance;

	static defaults = {
		scriptUrl: 'https://api-maps.yandex.ru/2.1?lang=ru&wizard=bitrix&load=package.full&mode=release', // todo locale and wizard
		loadStep: 100,
		loadTimeout: 5000,
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

		if (apiKey == null || apiKey === '') {
			return Promise.reject(new Error (BX.message('YAPAY_FIELD_WAREHOUSE_MAPS_API_KEY_NOT_FOUND')));
		}

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
			reject(new Error(BX.message('YAPAY_FIELD_WAREHOUSE_CANT_LOAD_MAPS')));
		}

		setTimeout(
			() => { this.waitLoaded(resolve, reject) },
			this.options.loadStep
		);

		this._loadElapsed += this.options.loadStep;
	}

}