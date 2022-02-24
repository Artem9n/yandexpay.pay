import AbstractStep from './abstractstep';

export default class Failure extends AbstractStep {

	static defaults = {
		template: '<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>'
	}

}