import AbstractStep from "./abstractstep";
import SecureForm from "../secure3d/form";
import SecureIframe from "../secure3d/iframe";
import SecureRbs from "../secure3d/iframerbs";

export default class Step3ds extends AbstractStep {

	render(node, data) {
		const view = this.makeView(data);
		view.setWidget(this.widget);
		view.render(node, data);
	}

	makeView(data) {
		let view = data.view;

		if (view === 'form') {
			return new SecureForm();
		} else if (view === 'iframe') {
			return new SecureIframe();
		} else if (view === 'iframerbs') {
			return new SecureRbs();
		}

		throw new Error('view secure3d missing')
	}
}