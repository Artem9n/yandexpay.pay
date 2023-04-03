import Button from "./button";
import FactoryWidget from "./widget/factory";

export default class Factory {

	static make(type, widget, options) {
		if (type === 'Button') {
			return new Button(widget, options);
		} else if (type === 'Widget') {
			const widgetType = options['displayParameters'].TYPE_WIDGET;
			return FactoryWidget.make(widgetType, widget, options);
		}

		throw new Error('unknown loader ' + type);
	}

}