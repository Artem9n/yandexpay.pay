export class SuggestWidget extends BX.ui.autoComplete {

	downloadBundle(request, onLoad, onComplete, onError) {
		ymaps.geocode(request['QUERY'])
			.then((response) => {
				console.log(response);
			});
	}

}
