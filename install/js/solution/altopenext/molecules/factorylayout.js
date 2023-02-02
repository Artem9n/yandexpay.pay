export default (factory, options) => {

	const template = {
		useDivider: true,
	};

	factory.extendDefaults(Object.assign({}, options, template));
}