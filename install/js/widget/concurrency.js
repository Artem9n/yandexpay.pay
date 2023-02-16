export class Concurrency {

	static elements = {};

	static push(mode: string, widget: Widget) : void {
		if (this.elements[mode] == null) {
			this.elements[mode] = [];
		}

		this.elements[mode].push(widget);
	}

	static pop(mode: string, widget: Widget) : void {
		const index = this.elements[mode].indexOf(widget);

		if (index === -1) { return; }

		this.elements[mode].splice(index, 1);
	}

	static same(mode: string) : Array {
		return this.elements[mode] ?? [];
	}

}