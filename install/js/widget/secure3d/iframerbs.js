import Base from "./base";
import Template from "../utils/template";

export default class IframeRbs extends Base {

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
		Promise.resolve()
			.then(() => this.renderIframe(node, data))
			.then(() => this.query(data));
	}

	query (data) {
		fetch(this.getOption('notifyUrl'), {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				service: this.getOption('requestSign'),
				accept: 'json',
				secure: data.params.secure,
				externalId: data.params.notify.externalId,
				paySystemId: data.params.notify.paySystemId
			})
		})
			.then(response => response.json())
			.then(result => {
				if (result.success === true) {
					this.widget.go(result.state, result);
				} else {
					this.widget.go('error', result);
				}
			})
			.catch(error => console.log(error) );
	}

	renderIframe(node, data) {
		let iframe = node.querySelector('iframe');
		let contentIframe = iframe.contentWindow || ( iframe.contentDocument.document || iframe.contentDocument);
		let html = `<form name="form" action="${data.action}" method="POST">`;

		contentIframe.document.open();
		contentIframe.document.write(html);
		contentIframe.document.close();

		contentIframe.document.querySelector('form').submit();
	}
}
