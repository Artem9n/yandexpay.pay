export class Concurrency {

	static elements = [];

	static push(widget: Widget) : void {
		this.elements.push(widget);
	}

	static pop(widget: Widget) : void {
		const index = this.elements.indexOf(widget);

		if (index === -1) { return; }

		this.elements.splice(index, 1);
	}

	static same(widget: Widget) : Array {
		const targetType = widget.getOption('type');
		const result = [];

		for (const sibling of this.elements) {
			if (targetType === sibling.getOption('type')) {
				result.push(sibling);
			}
		}

		return result;
	}

}