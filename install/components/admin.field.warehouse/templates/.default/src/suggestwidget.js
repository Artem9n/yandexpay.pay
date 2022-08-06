export class SuggestWidget extends BX.ui.autoComplete {

	constructor(options) {
		super(options.widget);
		this.onSelect = options.onSelect;
		this.search = options.search;
	}

	downloadBundle(request, onLoad, onComplete, onError) {
		this.search(request, onLoad, onComplete, onError);
	}

	// invokes when user selects value
	selectItem = (value) => {
		super.selectItem(value);
		this.onSelect(value);
	}

}
