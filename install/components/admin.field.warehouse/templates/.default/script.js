this.BX=this.BX||{},this.BX.YandexPay=this.BX.YandexPay||{},this.BX.YandexPay.Ui=this.BX.YandexPay.Ui||{},function(t){"use strict";class e{static getInstance(){return null==this.instance&&(this.instance=new this),this.instance}constructor(t={}){this.options=Object.assign({},this.constructor.defaults,t)}load(t){if(null!=this._loadPromise)return this._loadPromise;const e=this.loaded();return null!=e?Promise.resolve(e):(this._loadElapsed=0,this._loadPromise=new Promise(((e,s)=>{this.injectScript(t),this.waitLoaded(e,s)})),this._loadPromise.finally((()=>{this._loadPromise=null})),this._loadPromise)}loaded(){var t;return null==(null==(t=window.ymaps)?void 0:t.Map)?null:window.ymaps}injectScript(t){const e=window.document.head||window.document.body||window.document.documentElement,s=document.createElement("script");s.src=this.options.scriptUrl+"&apikey="+encodeURIComponent(t),e.appendChild(s),s.onload=()=>{e.removeChild(s)}}waitLoaded(t,e){const s=this.loaded();null==s?(this._loadElapsed>this.options.loadTimeout&&e(new Error("cant load ymaps")),setTimeout((()=>{this.waitLoaded(t,e)}),this.options.loadStep),this._loadElapsed+=this.options.loadStep):t(s)}}e.defaults={scriptUrl:"https://enterprise.api-maps.yandex.ru/2.0/?load=package.full&mode=release&lang=ru&wizard=bitrix",loadStep:100,loadTimeout:3e4};class s extends BX.ui.autoComplete{constructor(t){super(t.widget),this.selectItem=t=>{super.selectItem(t),console.log(this);let e=new ymaps.Placemark(t,{balloonContentHeader:"test",balloonContent:"test",balloonContentFooter:"test"},{preset:"islands#blueDotIcon"});this.options.map.geoObjects.add(e),this.options.map.setBounds(t,{checkZoomRange:!0,zoomMargin:9})},this.options=t}downloadBundle(t,e,s,o){ymaps.geocode(t.QUERY).then((t=>{this.opts;let o=this.vars;this.ctrls;o.loader.show();let i,a,n=t.geoObjects.getLength(),l=[];for(let e=0;e<n;e++)i=t.geoObjects.get(e).properties.get("metaDataProperty").GeocoderMetaData.text,a=t.geoObjects.get(e).properties.get("boundedBy"),l[e]={VALUE:a,DISPLAY:i};console.log(l),e.apply(this,[l]),s.call(this),console.log(t)}))}}class o{constructor(t,e={}){this.$el=t,this.el=this.$el[0],this.options=Object.assign({},this.constructor.defaults,e),this.initialize()}initialize(){this.widget=new s({widget:{scope:this.el},map:this.options.map}),console.log(this.widget)}}o.defaults={};class i extends BX.YandexPay.Plugin.Base{constructor(...t){super(...t),this.searchStop=t=>{},this.searchEnd=t=>{console.log(t)}}initialize(){super.initialize(),e.getInstance().load(this.options.apiKey).then((()=>{this.bootMap(),this.bootSuggest()}))}bootMap(){const t=this.getElement("map");return this._map=new ymaps.Map(t[0],{center:[55.76,37.64],controls:["zoomControl"],zoom:10}),this._map}bootSuggest(){const t=this.getElement("suggest");this._suggest=new o(t,{map:this._map})}search(){const t=this.getElement("suggest").val();this._mapLibrary.geocode(t).then(this.searchEnd,this.searchStop)}}i.defaults={mapElement:".js-field-warehouse__map",suggestElement:".js-field-warehouse__suggest",apiKey:null},i.dataName="uiFieldWarehouse",t.Warehouse=i}(this.BX.YandexPay.Ui.Field=this.BX.YandexPay.Ui.Field||{});
//# sourceMappingURL=script.js.map
