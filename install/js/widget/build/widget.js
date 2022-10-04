this.BX=this.BX||{},function(t){"use strict";class e{static getFactory(t){var e,n,i,s,o;if(null==t)return null;const r=null==(e=window)||null==(n=e.BX)||null==(i=n.YandexPay)||null==(s=i.Solution)||null==(o=s[t])?void 0:o.factory;if(null!=r)return r;var a;null==(a=console)||a.warn(`cant find solution ${t}`)}static getPage(t,e){if(null==t||null==e)return null;const n=t+":"+e;return null==this.pages[n]&&(this.pages[n]=this.createPage(t,e)),this.pages[n]}static createPage(t,e){const n=this.getFactory(t);return null==n?null:n.create(e)}}e.pages={};class n{constructor(t,e={}){this.el=t,this.options=Object.assign({},this.constructor.defaults,e)}destroy(){}}n.defaults={check:null};class i extends n{constructor(t,e={}){super(t,e),this.loop=()=>{this.options.check()&&this.loopTimeout()},this.loopTimeout()}destroy(){this.loopCancel()}loopTimeout(){clearTimeout(this._loopTimeout),this._loopTimeout=setTimeout(this.loop,this.options.timeout)}loopCancel(){clearTimeout(this._loopTimeout)}}i.defaults=Object.assign({},n.defaults,{timeout:1e3});class s extends n{constructor(t,e={}){super(t,e),this.listener=t=>{for(const e of t)if(null!=e.removedNodes)for(const t of e.removedNodes)if(t instanceof HTMLElement&&(t===this.el||t.contains(this.el)))return void this.runCheck()},this.observe()}destroy(){this.disconnect()}observe(){const t=this.getAnchor();var e;null!=t?(this.observer=new window.MutationObserver(this.listener),this.observer.observe(t,{childList:!0,subtree:!0})):null==(e=console)||e.warn("cant find anchor for node preserver")}disconnect(){null!=this.observer&&(this.observer.disconnect(),this.observer=null)}runCheck(){const t=this.options.delay;null==t?this.options.check():(clearTimeout(this._checkTimeout),this._checkTimeout=setTimeout((()=>{this.options.check()}),t))}getAnchor(){return null==this.options.anchor?document.body:this.el.closest(this.options.anchor)}}s.defaults=Object.assign({},n.defaults,{anchor:null,delay:0});class o{static make(t={}){return new o(t)}constructor(t={}){this.config=t,this.callbackMap={}}on(t,e){this.matchEvent("bx")&&this.onBxEvent(t,e),this.matchEvent("jquery")&&this.onJQueryEvent(t,e),this.matchEvent("plain")&&this.onPlainEvent(t,e)}off(t,e){this.matchEvent("bx")&&this.offBxEvent(t,e),this.matchEvent("jquery")&&this.offJQueryEvent(t,e),this.matchEvent("plain")&&this.offPlainEvent(t,e)}fire(t,e={}){this.matchEvent("bx")&&this.fireBxEvent(t,e),this.matchEvent("jquery")&&this.fireJQueryEvent(t,e),this.matchEvent("plain")&&this.firePlainEvent(t,e)}matchEvent(t){return null!=this.config[t]?!!this.config[t]:!this.config.strict}onBxEvent(t,e){"undefined"!=typeof BX&&BX.addCustomEvent(t,e)}offBxEvent(t,e){"undefined"!=typeof BX&&BX.removeCustomEvent(t,e)}fireBxEvent(t,e){"undefined"!=typeof BX&&BX.onCustomEvent(t,[e])}onJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");if(this.canProxyCallback(n)){const t=e;e=(e,n)=>{var i;const s=null!=n?n:null==e||null==(i=e.originalEvent)?void 0:i.detail;t(s)},this.pushCallbackVariation("jquery",t,e)}jQuery(document).on(t,e)}offJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("jquery",e))||jQuery(document).off(t,e)}fireJQueryEvent(t,e){"undefined"!=typeof jQuery&&(this.hasPlainAndJQueryCollision()||jQuery(document).triggerHandler(new CustomEvent(t,{detail:e})))}onPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");if(this.canProxyCallback(n)){const t=e;e=e=>{t(e.detail)},this.pushCallbackVariation("plain",t,e)}document.addEventListener(t,e)}offPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("plain",e))||document.removeEventListener(t,e)}firePlainEvent(t,e){document.dispatchEvent(new CustomEvent(t,{detail:e}))}hasPlainAndJQueryCollision(){if(!this.matchEvent("plain")||!this.matchEvent("jquery"))return!1;return!0!==this.typeConfig("plain").force&&"undefined"!=typeof jQuery}canProxyCallback(t){return!1!==t.proxy}typeConfig(t){return"object"==typeof this.config[t]&&null!=this.config[t]?this.config:{}}pushCallbackVariation(t,e,n){null==this.callbackMap[t]&&(this.callbackMap[t]=new WeakMap),this.callbackMap[t].set(e,n)}popCallbackVariation(t,e){if(null==this.callbackMap[t])return null;const n=this.callbackMap[t],i=n.get(e);return null!=i&&n.delete(e),i}}class r{constructor(t,e={}){this.el=t,this.options=Object.assign({},this.constructor.defaults,e),this.eventProxy=new o(this.options.config),this.bind()}destroy(){this.unbind(),this.eventProxy=null,this.options={},this.el=null}bind(){this.bindOn(),this.bindEvent()}unbind(){this.unbindOff(),this.unbindEvent()}bindOn(){null!=this.options.on&&this.options.on(this.options.check)}unbindOff(){null!=this.options.off&&this.options.off(this.options.check)}bindEvent(){const t=this.options.event;if(null!=t)if("string"==typeof t)this.eventProxy.on(t,this.options.check);else if(Array.isArray(t))t.forEach((t=>{this.eventProxy.on(t,this.options.check)}));else{var e;null==(e=console)||e.warn("unknown event type "+typeof t)}}unbindEvent(){const t=this.options.event;if(null!=t)if("string"==typeof t)this.eventProxy.off(t,this.options.check);else if(Array.isArray(t))t.forEach((t=>{this.eventProxy.off(t,this.options.check)}));else{var e;null==(e=console)||e.warn("unknown event type "+typeof t)}}}r.defaults={check:null,event:null,config:{},on:null,off:null};class a{constructor(t,e={}){this.check=()=>{const t=document.body.contains(this.el);return t||this.options.restore(),t},this.el=t,this.options=Object.assign({},this.constructor.defaults,e),this.install()}destroy(){this.uninstall(),this.options={},this.el=null}install(){this.installMutation(),this.installSubscriber()}uninstall(){this.uninstallMutation(),this.uninstallSubscriber()}installMutation(){this.isEnabled("mutation")&&(this.mutation=class{static make(t,e){return"function"==typeof window.MutationObserver?new s(t,e):new i(t,e)}}.make(this.el,this.driverOptions("mutation")))}uninstallMutation(){null!=this.mutation&&this.mutation.destroy()}installSubscriber(){this.isEnabled("subscriber")&&(this.subscriber=new r(this.el,this.driverOptions("subscriber")))}uninstallSubscriber(){null!=this.subscriber&&(this.subscriber.destroy(),this.subscriber=null)}isEnabled(t){return!!this.options[t]}driverOptions(t){const e="object"==typeof this.options[t]?this.options[t]:{},n={check:this.check};return Object.assign({},e,n)}}a.defaults={restore:null,subscriber:null,mutation:!0};class l{static compile(t,e){let n,i,s,o=t;for(n in e)if(e.hasOwnProperty(n)){i="#"+n.toUpperCase()+"#",s=e[n];do{o=o.replace(i,s)}while(-1!==o.indexOf(i))}return o}static toElement(t){const e=document.createElement("div");return e.innerHTML=t,e.firstElementChild}static toElements(t){const e=document.createElement("div");return e.innerHTML=t,[...e.children]}}class h{static getInstance(){return null==this.instance&&(this.instance=new h),this.instance}load(){return null!=this._loadPromise||(this._loadPromise=new Promise(((t,e)=>{if(this.testGlobal())return void t();const n=document.createElement("script"),i=document.getElementsByTagName("script")[0]||document.body;n.type="text/javascript",n.async=!0,n.src="https://pay.yandex.ru/sdk/v1/pay.js",n.onload=()=>{this._loadPromise=null,t()},n.onerror=()=>{this._loadPromise=null,n.remove(),e(new Error("cant load yandex pay sdk library"))},i.parentNode.insertBefore(n,i)}))),this._loadPromise}testGlobal(){return null!=window.YaPay}}h.instance=null;class c{constructor(t,e={}){this.el=t,this.options=Object.assign({},this.constructor.defaults,e)}wait(){return new Promise((t=>{if("undefined"==typeof IntersectionObserver)return void t(this.el);new IntersectionObserver(((e,n)=>{e.forEach((e=>{if(e.isIntersecting){const i=e.target;n.unobserve(i),t(i)}}))})).observe(this.el)}))}}c.defaults={};class u{constructor(t={}){this.waitCount=0,this.defaults=Object.assign({},this.constructor.defaults),this.options={},this.setOptions(t),this.bootSolution(),this.bootLocal()}inject(t,e){return Promise.resolve().then((()=>this.waitElement(t))).then((t=>this.checkElement(t))).then((t=>this.renderElement(t,e))).then((t=>this.create(t))).then((n=>(this.getOption("preserve")&&this.preserve(t,e,n),n)))}create(t){return Promise.resolve(t).then((t=>this.insertLoader(t))).then((t=>this.intersection(t))).then((t=>h.getInstance().load().then((()=>t)))).then((t=>this.install(t)))}checkElement(t){const e=this.getOption("containerSelector");if(!!t.querySelector(e)||this.containsSiblingElement(t,e))throw new Error("the element already has a container");return t}containsSiblingElement(t,e){var n;let i=!1,s=null==(n=t.parentElement)?void 0:n.firstElementChild;for(;s;){if(s.matches(e)||s.querySelector(e)){i=!0;break}s=s.nextElementSibling}return i}intersection(t){return new c(t).wait()}preserve(t,e,n){const i=new a(n.el,Object.assign({},this.preserveOptions(),{restore:()=>{i.destroy(),this.restore(t,e,n)}}))}preserveOptions(){const t=this.getOption("preserve");return"object"==typeof t?t:{}}restore(t,e,n){return Promise.resolve().then((()=>this.waitElement(t))).then((i=>{const s=this.renderElement(i,e);return n.restore(s),this.getOption("preserve")&&this.preserve(t,e,n),n}))}install(t){return new BX.YandexPay.Widget(t)}insertLoader(t){const e=this.getOption("buttonWidth")||"AUTO";return t.innerHTML=l.compile(this.getOption("loaderTemplate"),{width:e.toLowerCase(),label:this.getOption("label")}),t}waitElement(t){return new Promise(((e,n)=>{this.waitCount=0,this.waitElementLoop(t,e,n)}))}waitElementLoop(t,e,n){const i=this.findElement(t);i?e(i):(++this.waitCount,this.waitCount>=this.getOption("waitLimit")?n("cant find element by selector "+t):setTimeout(this.waitElementLoop.bind(this,t,e,n),this.getOption("waitTimeout")))}findElement(t){var e,n;let i,s,o=t.trim();if(""===o)throw new Error("widget selector is empty");return i=null!=(e=null!=(n=this.searchBySelector(o))?n:this.searchById(t))?e:this.searchByClassName(t),null==i?null:(i.length>1&&(s=this.reduceVisible(i)),null==s&&(s=i[0]),s)}searchBySelector(t){try{const e=[];for(const n of t.split(",")){const t=n.trim();if(""===t||!this.isCssSelector(t))continue;const i=document.querySelectorAll(t);for(const t of i)e.push(t)}return e.length>0?e:null}catch(t){return null}}searchById(t){try{const e=document.getElementById(t);return null!=e?[e]:null}catch(t){return null}}searchByClassName(t){try{const e=document.getElementsByClassName(t);return e.length>0?e:null}catch(t){return null}}reduceVisible(t){let e=null;for(const n of t)if(this.testVisible(n)){e=n;break}return e}testVisible(t){return t.offsetWidth||t.offsetHeight||t.getClientRects().length}isCssSelector(t){return/^[.#]/.test(t)}renderElement(t,e){const n=this.getOption("containerSelector"),i=this.getOption("buttonWidth")||"AUTO",s=l.compile(this.getOption("template"),{label:this.getOption("label"),width:i.toLowerCase(),id:this.getOption("containerId")});let o=l.toElements(s),r=null;0===e.indexOf("after")&&(o=o.reverse());for(const i of o)t.insertAdjacentElement(e,i),null==r&&(r=i.matches(n)?i:i.querySelector(n));if(null==r)throw new Error(`cant find template container by selector ${n}`);return r}bootSolution(){const t=this.getOption("solution"),n=this.getOption("mode"),i=e.getPage(t,n);null!=i&&i.bootFactory(this)}bootLocal(){o.make().fire("bxYapayFactoryInit",{factory:this})}extendDefaults(t){this.defaults=Object.assign(this.defaults,t)}setOptions(t){this.options=Object.assign(this.options,t)}getOption(t){var e;return null!=(e=this.options[t])?e:this.defaults[t]}}u.defaults={solution:null,template:'<div id="#ID#" class="bx-yapay-drawer"></div>',containerSelector:".bx-yapay-drawer",loaderTemplate:'<div class="bx-yapay-skeleton-loading width--#WIDTH#"></div>',loaderSelector:".bx-yapay-skeleton-loading",preserve:!1,waitLimit:30,waitTimeout:1e3};class p{constructor(t,e={}){this.delayTimeouts={},this.widget=t,this.defaults=Object.assign({},this.constructor.defaults),this.options=Object.assign({},e)}getOption(t){var e,n;return null!=(e=null!=(n=this.options[t])?n:this.widget.getOption(t))?e:this.defaults[t]}render(t,e={}){t.innerHTML=this.compile(e)}compile(t){return l.compile(this.getOption("template"),t)}restore(t){}query(t,e){return fetch(t,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)}).then((t=>t.json()))}getTemplate(t){let e,n=t+"Template",i=this.options[n],s=i.substr(0,1);return e="."===s||"#"===s?this.getNode(i).innerHTML:i,e}getElement(t,e,n){let i=this.getElementSelector(t);return this.getNode(i,e,n||"querySelector")}getElementSelector(t){let e=t+"Element";return this.options[e]}getNode(t,e,n){return"#"===t.substr(0,1)?e=document:e||(e=this.el),e[n](t)}clearDelay(t){null!=this.delayTimeouts[t]&&(clearTimeout(this.delayTimeouts[t]),this.delayTimeouts[t]=null)}delay(t,e=[],n=300){this.clearDelay(t),this.delayTimeouts[t]=setTimeout(this[t].bind(this,...e),n)}showError(t,e,n=null){let i=t+" - "+e;n&&(i+=" "+n),alert(i)}}p.defaults={template:""};class d{constructor(t={}){this.options=Object.assign({},this.constructor.defaults,t),this.widget=null}render(t,e={}){t.innerHTML=this.compile(e)}compile(t){return l.compile(this.options.template,t)}setWidget(t){this.widget=t}getOption(t){return t in this.options?this.options[t]:this.widget.options[t]}}d.defaults={template:null};class m extends d{render(t,e){super.render(t,e),this.autosubmit(t)}compile(t){const e=this.options.template,n=Object.assign(t,{inputs:this.makeInputs(t)});return l.compile(e,n)}makeInputs(t){let e,n,i,s=t.params;if(0===Object.keys(s).length)return"";for(e in i=t.termUrl?'<input type="hidden" name="TermUrl" value="'+s.termUrl+'">':"",s)s.hasOwnProperty(e)&&(n=s[e],i+='<input type="hidden" name="'+e+'" value="'+n+'">');return i}makeTermUrl(){let t=this.getOption("notifyUrl"),e=window.location.href;return t+=(-1===t.indexOf("?")?"?":"&")+"backurl="+encodeURIComponent(e)+"&service="+this.getOption("requestSign")+"&paymentId="+this.getOption("externalId"),t}autosubmit(t){t.querySelector("form").submit()}}m.defaults={template:'<form name="form" action="#ACTION#" method="#METHOD#">#INPUTS#</form>'};class y extends d{render(t,e){this.insertIframe(t,e)}insertIframe(t,e){t.innerHTML=l.compile(this.options.template,e),this.compile(t,e)}compile(t,e){let n=t.querySelector("iframe"),i=n.contentWindow||n.contentDocument.document||n.contentDocument,s=e.params;i.document.open(),i.document.write(s),i.document.close()}}y.defaults={template:'<iframe style="display: none;"></iframe>'};class g extends d{render(t,e){this.insertIframe(t,e)}insertIframe(t,e){t.innerHTML=l.compile(this.options.template,e),this.compile(t,e)}compile(t,e){Promise.resolve().then((()=>this.renderIframe(t,e))).then((()=>this.query(e)))}query(t){fetch(this.getOption("notifyUrl"),{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({service:this.getOption("requestSign"),accept:"json",secure:t.params.secure,externalId:t.params.notify.externalId,paySystemId:t.params.notify.paySystemId})}).then((t=>t.json())).then((t=>{!0===t.success?this.widget.go(t.state,t):this.widget.go("error",t)})).catch((t=>console.log(t)))}renderIframe(t,e){let n=t.querySelector("iframe"),i=n.contentWindow||n.contentDocument.document||n.contentDocument,s=`<form name="form" action="${e.action}" method="POST">`;i.document.open(),i.document.write(s),i.document.close(),i.document.querySelector("form").submit()}}g.defaults={template:'<iframe style="display: none;"></iframe>'};class f extends p{render(t,e){const n=this.makeView(e);n.setWidget(this.widget),n.render(t,e)}makeView(t){let e=t.view;if("form"===e)return new m;if("iframe"===e)return new y;if("iframerbs"===e)return new g;throw new Error("view secure3d missing")}}class P extends p{}P.defaults={template:'<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>'};class w extends p{}w.defaults={template:'<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>'};class b{constructor(t){this.order=t}getOption(t){return this.order.getOption(t)}createPayment(t,e){}getPaymentData(t){}}class O extends b{getPaymentData(t){return{env:this.getOption("env"),version:3,currencyCode:YaPay.CurrencyCode.Rub,merchantId:this.getOption("merchantId"),orderId:t.id,cart:{items:t.items,total:{amount:t.total}},metadata:this.getOption("metadata")}}createPayment(t,e){YaPay.createPayment(e,{agent:{name:"CMS-Bitrix",version:"1.0"}}).then((e=>{e.mountButton(t,{type:YaPay.ButtonType.Pay,theme:this.getOption("buttonTheme")||YaPay.ButtonTheme.Black,width:this.getOption("buttonWidth")||YaPay.ButtonWidth.Auto}),e.on(YaPay.CheckoutEventType.Success,(n=>{t.remove(),this.authorize(n).then((t=>{"success"===t.status?setTimeout((function(){window.location.href=t.data.redirect}),1e3):this.order.showError("authorize",t.reasonCode,t.reason)})),e.complete(YaPay.CompleteReason.Success)})),e.on(YaPay.CheckoutEventType.Abort,(t=>{})),e.on(YaPay.CheckoutEventType.Error,(t=>{this.order.showError("yapayPayment","error",t)}))})).catch((t=>{this.order.showError("yapayPayment","payment not created",t)}))}authorize(t){let e={orderId:t.orderId,hash:t.metadata,successUrl:this.getOption("successUrl")};return this.order.query(this.getOption("restUrl")+"authorize",e)}}class v extends b{getPaymentData(t){return{env:this.getOption("env"),version:2,countryCode:YaPay.CountryCode.Ru,currencyCode:YaPay.CurrencyCode.Rub,merchant:{id:this.getOption("merchantId"),name:this.getOption("merchantName")},order:{id:t.id,total:{amount:t.total},items:t.items},paymentMethods:[{type:YaPay.PaymentMethodType.Card,gateway:this.getOption("gateway"),gatewayMerchantId:this.getOption("gatewayMerchantId"),allowedAuthMethods:[YaPay.AllowedAuthMethod.PanOnly],allowedCardNetworks:[YaPay.AllowedCardNetwork.UnionPay,YaPay.AllowedCardNetwork.Uzcard,YaPay.AllowedCardNetwork.Discover,YaPay.AllowedCardNetwork.AmericanExpress,YaPay.AllowedCardNetwork.Visa,YaPay.AllowedCardNetwork.Mastercard,YaPay.AllowedCardNetwork.Mir,YaPay.AllowedCardNetwork.Maestro,YaPay.AllowedCardNetwork.VisaElectron]}]}}createPayment(t,e){YaPay.createPayment(e,{agent:{name:"CMS-Bitrix",version:"1.0"}}).then((e=>{let n=e.createButton({type:YaPay.ButtonType.Pay,theme:this.getOption("buttonTheme")||YaPay.ButtonTheme.Black,width:this.getOption("buttonWidth")||YaPay.ButtonWidth.Auto});n.mount(t),n.on(YaPay.ButtonEventType.Click,(function(){e.checkout()})),e.on(YaPay.PaymentEventType.Process,(t=>{this.notify(e,t).then((t=>{})),e.complete(YaPay.CompleteReason.Success)})),e.on(YaPay.PaymentEventType.Error,(function(t){console.log({errors:t}),e.complete(YaPay.CompleteReason.Error)})),e.on(YaPay.PaymentEventType.Abort,(function(t){}))})).catch((function(t){console.log({"payment not create":t})}))}notify(t,e){return fetch(this.getOption("notifyUrl"),{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({service:this.getOption("requestSign"),accept:"json",yandexData:e,externalId:this.getOption("externalId"),paySystemId:this.getOption("paySystemId")})}).then((t=>t.json())).then((t=>{!0===t.success?this.order.widget.go(t.state,t):this.order.widget.go("error",t)})).catch((t=>console.log(t)))}}window.YaPay;class E extends p{render(t,e){this.proxy=this.getOption("isRest")?new O(this):new v(this);const n=this.getPaymentData(e);this.createPayment(t,n)}compile(t){return l.compile(this.options.template,t)}getPaymentData(t){return this.proxy.getPaymentData(t)}createPayment(t,e){this.proxy.createPayment(t,e)}}E.defaults={template:'<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>'};class C{constructor(t){this.cart=t}getOption(t){return this.cart.getOption(t)}bootstrap(){}createPayment(t,e){}getPaymentData(){}}class k extends C{bootstrap(){this.reflow()}getButtonData(){let t={productId:this.getOption("productId"),mode:this.getOption("mode"),currencyCode:this.getOption("currencyCode"),setupId:this.getOption("setupId")};return this.cart.query(this.getOption("restUrl")+"button/data",t)}getPaymentData(){return{env:this.getOption("env"),version:3,merchantId:this.getOption("merchantId"),cart:{externalId:"checkout-b2b-test-order-id"},currencyCode:this.getOption("currencyCode")}}createPayment(t,e){!0!==this._mounted&&YaPay.createCheckout(e,{agent:{name:"CMS-Bitrix",version:"1.0"}}).then((e=>{this._mounted=!0,this.cart.removeLoader(),this.mountButton(t,e),e.on(YaPay.CheckoutEventType.Success,(t=>{this.authorize(t).then((t=>{"success"===t.status?setTimeout((function(){window.location.href=t.data.redirect}),1e3):this.cart.showError("authorize",t.reasonCode,t.reason)})),e.complete(YaPay.CompleteReason.Success),console.log("Process",t)})),e.on(YaPay.CheckoutEventType.Error,(t=>{console.log("Process",t)}))})).catch((e=>{t.remove(),this.cart.showError("yapayPayment","payment not created",e)}))}authorize(t){let e={orderId:t.orderId,hash:t.metadata,successUrl:this.getOption("successUrl")};return this.cart.query(this.getOption("restUrl")+"authorize",e)}bindDebug(t){for(const e in YaPay.CheckoutEventType)YaPay.CheckoutEventType.hasOwnProperty(e)&&t.on(YaPay.CheckoutEventType[e],(function(){console.log(arguments)}))}mountButton(t,e){this.payment=e,e.mountButton(this.cart.element,{type:YaPay.ButtonType.Checkout,theme:this.getOption("buttonTheme")||YaPay.ButtonTheme.Black,width:this.getOption("buttonWidth")||YaPay.ButtonWidth.Auto})}restoreButton(t){null!=this.payment&&this.payment.mountButton(t,{type:YaPay.ButtonType.Checkout,theme:this.getOption("buttonTheme")||YaPay.ButtonTheme.Black,width:this.getOption("buttonWidth")||YaPay.ButtonWidth.Auto})}combineOrderWithData(t){const{cart:e}=this.cart.paymentData;let n={cart:{...e,items:t.items,total:{amount:t.total.amount}},metadata:t.metadata};Object.assign(this.cart.paymentData,n)}changeOffer(t){this.getOption("productId")!==t&&(this.cart.widget.setOptions({productId:t}),this.reflow())}changeBasket(){this.reflow()}setupPaymentCash(){}reflow(){this.getButtonData().then((t=>{var e;if("fail"===t.status)throw new Error(t.reason);this.combineOrderWithData(t.data),this.createPayment(this.cart.element,this.cart.paymentData),null==(e=this.payment)||e.update(this.cart.paymentData)})).catch((t=>{this.cart.removeLoader()}))}}class S extends C{bootstrap(){this.reflow()}getPaymentData(){return{env:this.getOption("env"),version:2,countryCode:YaPay.CountryCode.Ru,currencyCode:YaPay.CurrencyCode.Rub,merchant:{id:this.getOption("merchantId"),name:this.getOption("merchantName"),url:this.getOption("siteUrl")},order:{id:"0"},paymentMethods:[{type:YaPay.PaymentMethodType.Card,gateway:this.getOption("gateway"),gatewayMerchantId:this.getOption("gatewayMerchantId"),allowedAuthMethods:[YaPay.AllowedAuthMethod.PanOnly],allowedCardNetworks:[YaPay.AllowedCardNetwork.UnionPay,YaPay.AllowedCardNetwork.Uzcard,YaPay.AllowedCardNetwork.Discover,YaPay.AllowedCardNetwork.AmericanExpress,YaPay.AllowedCardNetwork.Visa,YaPay.AllowedCardNetwork.Mastercard,YaPay.AllowedCardNetwork.Mir,YaPay.AllowedCardNetwork.Maestro,YaPay.AllowedCardNetwork.VisaElectron]}],requiredFields:{billingContact:{email:this.getOption("useEmail")||!1},shippingContact:{name:this.getOption("useName")||!1,email:this.getOption("useEmail")||!1,phone:this.getOption("usePhone")||!1},shippingTypes:{direct:!0,pickup:!0}}}}getProducts(){let t={yapayAction:"getProducts",productId:this.getOption("productId"),mode:this.getOption("mode"),setupId:this.getOption("setupId")};return this.cart.query(this.getOption("purchaseUrl"),t)}getShippingOptions(t){let e={address:t,yapayAction:"deliveryOptions",items:this.cart.paymentData.order.items,setupId:this.getOption("setupId"),paySystemId:this.getOption("paySystemId")};return this.cart.query(this.getOption("purchaseUrl"),e)}getPickupOptions(t){let e={bounds:t,yapayAction:"pickupOptions",items:this.cart.paymentData.order.items,setupId:this.getOption("setupId"),paySystemId:this.getOption("paySystemId")};return this.cart.query(this.getOption("purchaseUrl"),e)}createPayment(t,e){!0!==this._mounted&&YaPay.createPayment(e,{agent:{name:"CMS-Bitrix",version:"1.0"}}).then((e=>{this._mounted=!0,this.cart.removeLoader(),this.cart.mountButton(t,e),e.on(YaPay.PaymentEventType.Process,(t=>{this.orderAccept(t).then((n=>{if(n.error)throw new Error(n.error.message,n.error.code);this.isPaymentTypeCash(t)?(e.complete(YaPay.CompleteReason.Success),null!=n.redirect&&(window.location.href=n.redirect)):this.notify(n,t).then((t=>{!0===t.success?(this.cart.widget.go(t.state,t),e.complete(YaPay.CompleteReason.Success)):(this.cart.widget.go("error",t),e.complete(YaPay.CompleteReason.Error))}))})).catch((t=>{this.cart.showError("yapayProcess","",t),e.complete(YaPay.CompleteReason.Error)}))})),e.on(YaPay.PaymentEventType.Error,(t=>{this.cart.showError("yapayError","service temporary unavailable"),e.complete(YaPay.CompleteReason.Error)})),e.on(YaPay.PaymentEventType.Change,(t=>{t.shippingAddress&&this.getShippingOptions(t.shippingAddress).then((t=>{e.update({shippingOptions:t})})),t.shippingOption&&e.update({order:this.combineOrderWithDirectShipping(t.shippingOption)}),t.pickupBounds&&this.getPickupOptions(t.pickupBounds).then((t=>{e.update({pickupPoints:t})})),t.pickupInfo&&this.getPickupDetail(t.pickupInfo.pickupPointId).then((t=>{e.update({pickupPoint:t})})),t.pickupPoint&&e.update({order:this.combineOrderWithPickupShipping(t.pickupPoint)})}))})).catch((t=>{this.cart.showError("yapayPayment","payment not created",t)}))}getPickupDetail(t){let e={pickupId:t,yapayAction:"pickupDetail",items:this.cart.paymentData.order.items,setupId:this.getOption("setupId"),paySystemId:this.getOption("paySystemId")};return this.cart.query(this.getOption("purchaseUrl"),e)}orderAccept(t){let e,n=t.shippingMethodInfo.shippingOption?"delivery":"pickup";e="pickup"===n?{address:t.shippingMethodInfo.pickupPoint.address,pickup:t.shippingMethodInfo.pickupPoint}:{address:t.shippingMethodInfo.shippingAddress,delivery:t.shippingMethodInfo.shippingOption};let i={...{setupId:this.getOption("setupId"),items:this.cart.paymentData.order.items,payment:t.paymentMethodInfo,contact:t.shippingContact,yapayAction:"orderAccept",deliveryType:n,paySystemId:this.isPaymentTypeCash(t)?this.getOption("paymentCash"):this.getOption("paySystemId"),orderAmount:t.orderAmount},...e};return this.cart.query(this.getOption("purchaseUrl"),i)}isPaymentTypeCash(t){return"CASH"===t.paymentMethodInfo.type}notify(t,e){let n={service:this.getOption("requestSign"),accept:"json",yandexData:e,externalId:t.externalId,paySystemId:t.paySystemId};return this.cart.query(this.getOption("notifyUrl"),n)}changeOffer(t){this.getOption("productId")!==t&&(this.cart.widget.setOptions({productId:t}),this.reflow())}changeBasket(){this.reflow()}reflow(){this.getProducts().then((t=>{if(t.error)throw new Error(t.error.message);this.combineOrderWithProducts(t),this.createPayment(this.cart.element,this.cart.paymentData)})).catch((t=>{this.cart.removeLoader()}))}combineOrderWithPickupShipping(t){const{order:e}=this.cart.paymentData;return{...e,items:[...e.items,{type:"SHIPPING",label:t.label,amount:t.amount}],total:{...e.total,amount:this.cart.amountSum(e.total.amount,t.amount)}}}combineOrderWithDirectShipping(t){const{order:e}=this.cart.paymentData;return{...e,items:[...e.items,{type:"SHIPPING",label:t.label,amount:t.amount}],total:{...e.total,amount:this.cart.amountSum(e.total.amount,t.amount)}}}combineOrderWithProducts(t){const{order:e}=this.cart.paymentData;let n={...e,items:t.items,total:{amount:t.amount}};Object.assign(this.cart.paymentData.order,n)}restoreButton(t){null!=this.cart.paymentButton&&this.cart.paymentButton.mount(t)}setupPaymentCash(){null!=this.getOption("paymentCash")&&this.cart.paymentData.paymentMethods.push({type:YaPay.PaymentMethodType.Cash})}}const I=window.YaPay;class T extends p{render(t,e){this.isBootstrap=!1,this.element=t,this.paymentButton=null,this.proxy=this.getOption("isRest")?new k(this):new S(this),this.paymentData=this.getPaymentData(),this.bootSolution(),this.setupPaymentCash(),this.delayBootstrap()}compile(t){return l.compile(this.options.template,t)}restore(t){this.element=t,this.restoreButton(t)}bootstrap(){this.isBootstrap=!0,this.proxy.bootstrap()}bootSolution(){const t=this.widget.getSolution();null!=t&&t.bootCart(this)}delayChangeBasket(){this.delay("changeBasket")}delayChangeOffer(t){this.delay("changeOffer",[t])}delayBootstrap(){var t;t=()=>{this.delay("bootstrap")},"complete"===document.readyState||"interactive"===document.readyState?setTimeout(t,1):document.addEventListener("DOMContentLoaded",t)}changeBasket(){var t;this.isBootstrap&&(null==(t=this.proxy)||t.changeBasket())}changeOffer(t){var e;this.isBootstrap?null==(e=this.proxy)||e.changeOffer(t):this.widget.setOptions({productId:t})}setupPaymentCash(){var t;null==(t=this.proxy)||t.setupPaymentCash()}getPaymentData(){return this.proxy.getPaymentData()}createPayment(t,e){this.proxy.createPayment(t,e)}mountButton(t,e){this.paymentButton=e.createButton({type:I.ButtonType.Checkout,theme:this.getOption("buttonTheme")||I.ButtonTheme.Black,width:this.getOption("buttonWidth")||I.ButtonWidth.Auto}),this.paymentButton.mount(this.element),this.paymentButton.on(I.ButtonEventType.Click,(()=>{e.checkout()}))}restoreButton(t){this.proxy.restoreButton(t)}amountSum(t,e){return(Number(t)+Number(e)).toFixed(2)}showError(t,e,n=null){let i=t+" - "+e;n&&(i+=" "+n),alert(i)}removeLoader(){const t=this.element.querySelector(this.getOption("loaderSelector"));null!=t&&t.remove()}}T.defaults={loaderSelector:".bx-yapay-skeleton-loading"};class B{constructor(t,e={}){this.defaults=Object.assign({},this.constructor.defaults),this.options={},this.el=t,this.setOptions(e),this.bootSolution()}payment(t){this.go("payment",t)}cart(t){this.go("cart",t)}restore(t){var e;this.el=t,null==(e=this.step)||e.restore(t)}go(t,e){this.step=this.makeStep(t),this.step.render(this.el,e)}makeStep(t){const e=this.getOption(t)||{};return class{static make(t,e,n={}){if("3ds"===t)return new f(e,n);if("finished"===t)return new P(e,n);if("error"===t)return new w(e,n);if("payment"===t)return new E(e,n);if("cart"===t)return new T(e,n);throw new Error("unknown step "+t)}}.make(t,this,e)}getSolution(){const t=this.getOption("solution"),n=this.getOption("mode");return e.getPage(t,n)}bootSolution(){const t=this.getSolution();null!=t&&t.bootWidget(this)}extendDefaults(t){this.defaults=Object.assign(this.defaults,t)}setOptions(t){this.options=Object.assign(this.options,t)}getOption(t){var e;return null!=(e=this.options[t])?e:this.defaults[t]}}B.defaults={},t.Factory=u,t.Widget=B}(this.BX.YandexPay=this.BX.YandexPay||{});
//# sourceMappingURL=widget.js.map
