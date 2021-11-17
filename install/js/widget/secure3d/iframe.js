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
		let html = this.makeHtml(data);

		contentIframe.document.open();
		contentIframe.document.write(html);
		contentIframe.document.close();
	}

	makeHtml(data) {
		let template = data.params;

		template = template.replace('<form', '<form target="_top"');
		template = template.replace('<head', '<head><base href="https://pay.best2pay.net/"/>');

		return template;
	}
}