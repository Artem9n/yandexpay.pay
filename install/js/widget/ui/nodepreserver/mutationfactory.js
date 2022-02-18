import MutationLoop from "./mutationloop";
import MutationObserver from "./mutationobserver";

export default class MutationFactory {

	static make(element, options) {
		if (typeof window.MutationObserver === 'function') {
			return new MutationObserver(element, options);
		}

		return new MutationLoop(element, options);
	}

}