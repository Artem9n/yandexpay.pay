(function() {

	const AdminList = BX.namespace('YandexMarket.AdminList');

	AdminList.ListExtension = function(options) {
		this.options = Object.assign({}, this.defaults, options);
		this.initialize();
	};

	Object.assign(AdminList.ListExtension.prototype, {

		defaults: {
			limitTop: null,
			disabledRows: null,
			loadMore: false,
			reloadEvents: [],
		},

		initialize: function() {
			this.applyLimitTop();
			this.applyDisabledIndexes();
			this.applyLoadMore();
			this.applyReloadEvents();
		},

		applyLimitTop: function() {
			if (this.options.limitTop == null) { return; }

			const element = this.getPageSizeElement();
			const items = this.getPageSizeItems(element);
			const newItems = this.filterPageSizeItems(items, this.options.limitTop);

			if (newItems != null) {
				this.setPageSizeItems(element, newItems);
			}
		},

		getPageSizeElement: function() {
			const grid = this.getGrid();
			const pageSizeId = grid.getContainerId() + '_' + grid.settings.get('pageSizeId');

			return document.getElementById(pageSizeId);
		},

		getPageSizeItems: function(element) {
			const itemsAttribute = BX.data(element, 'items');

			return this.parsePageSizeItems(itemsAttribute);
		},

		setPageSizeItems: function(element, items) {
			BX.data(element, 'items', JSON.stringify(items));
		},

		parsePageSizeItems: function(attributeValue) {
			let result;

			try {
				result = eval(attributeValue);
			} catch (e) {
				result = [];
			}

			return result;
		},

		filterPageSizeItems: function(items, top) {
			const result = [];
			let isChanged = false;

			for (let itemIndex = 0; itemIndex < items.length; itemIndex++) {
				let item = items[itemIndex];
				let itemValue = parseInt(item.VALUE, 10);

				if (!isNaN(itemValue) && itemValue > top) {
					isChanged = true;
				} else {
					result.push(item);
				}
			}

			return isChanged ? result : null;
		},

		applyDisabledIndexes: function() {
			if (this.options.disabledRows == null) { return; }

			const disabled = this.options.disabledRows;
			const rows = this.getGrid().getRows();

			for (let disabledIndex = 0; disabledIndex < disabled.length; disabledIndex++) {
				let rowId = disabled[disabledIndex];
				let row = rows.getById(rowId);

				if (row == null) { continue; }

				let checkbox = row.getCheckbox();

				checkbox.disabled = true;
				BX.data(checkbox, 'disabled', '1');
			}
		},

		applyLoadMore: function() {
			if (!this.options.loadMore) { return; }

			const nextUrl = this.getNextPageUrl();

			if (!nextUrl) { return; }

			const moreButton = this.getGrid().getMoreButton();
			const moreButtonElement = moreButton.getNode();

			moreButtonElement.href = nextUrl;
			moreButtonElement.style.display = '';
		},

		getNextPageUrl: function() {
			const pagination = this.getGrid().getPagination();
			const nextLink = pagination.getContainer().querySelector('.main-ui-pagination-next');
			let result;

			if (nextLink && nextLink.href) {
				result = nextLink.href;
			}

			return result;
		},

		applyReloadEvents: function() {
			const events = this.options.reloadEvents;

			if (!events || events.length === 0) { return; }

			for (let event of events) {
				BX.addCustomEvent(event, BX.proxy(this.onReloadEvent, this));
			}
		},

		onReloadEvent: function() {
			this.getGrid().reloadTable();
		},

		getGrid: function() {
			return BX.Main.gridManager.getById(this.options.grid).instance;
		}

	});

})();