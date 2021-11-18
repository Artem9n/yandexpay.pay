import Base from "./base";
import Template from "../utils/template";

export default class Iframe extends Base {

	static defaults = {
		template: '<iframe style="display: none;"></iframe>',
	}

	render(node, data) {
		this.insertIframe(node, data);
	}

	insertIframe(node, data) {
		node.innerHTML = Template.compile(this.options.template, data);
		this.compile(node, data);
	}

	compile(node, data) {
		let iframe = node.querySelector('iframe');
		let contentIframe = iframe.contentWindow || ( iframe.contentDocument.document || iframe.contentDocument);
		let html = data.params;

		contentIframe.document.open();
		contentIframe.document.write(html);
		contentIframe.document.close();
	}
}