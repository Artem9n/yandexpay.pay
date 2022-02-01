import Template from '../utils/template';
import AbstractStep from './abstractstep';

export default class Finish extends AbstractStep {

	static optionPrefix = 'finish';

	static defaults = {
		template: '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>'
	}

}