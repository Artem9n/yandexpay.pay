import "./suggestwidget.css";

export class SuggestWidget extends BX.ui.autoComplete {

	constructor(options) {
		super(options.widget);

		this.onSelect = options.onSelect;
		this.search = options.search;
	}
	handleInitStack(nf, owner, opts) {
		this.setOptions();
		super.handleInitStack(nf, owner, opts);
	}
	setOptions() {
		BX.merge(this, {
			opts: {
				messages: {
					nothingFound: 'Ничего не найдено',
					error: 'Произошла ошибка',
				},
			},
			sys: {
				code: 'yapay-suggest'
			}
		});
	}

	downloadBundle(request, onLoad, onComplete, onError) {
		this.search(request, onLoad, onComplete, onError);
	}

	// invokes when user selects value
	selectItem = (value) => {
		super.selectItem(value);
		this.onSelect(value);
	}

	showLoading() {
		this.vars.loader.show();
	}
	hideLoading() {
		this.vars.loader.hide();
	}

	showError(errorLabel, messages, sysDesc) {
		if (errorLabel === '') {
			errorLabel = this.opts.messages.error;
		}
		super.showError(errorLabel, messages, sysDesc);
	}
}
