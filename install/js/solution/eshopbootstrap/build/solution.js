this.BX=this.BX||{},this.BX.YandexPay=this.BX.YandexPay||{},this.BX.YandexPay.Solution=this.BX.YandexPay.Solution||{},function(t){"use strict";class n{static make(t={}){return new n(t)}constructor(t={}){this.config=t,this.callbackMap={}}on(t,n){this.matchEvent("bx")&&this.onBxEvent(t,n),this.matchEvent("jquery")&&this.onJQueryEvent(t,n),this.matchEvent("plain")&&this.onPlainEvent(t,n)}off(t,n){this.matchEvent("bx")&&this.offBxEvent(t,n),this.matchEvent("jquery")&&this.offJQueryEvent(t,n),this.matchEvent("plain")&&this.offPlainEvent(t,n)}fire(t,n={}){this.matchEvent("bx")&&this.fireBxEvent(t,n),this.matchEvent("jquery")&&this.fireJQueryEvent(t,n),this.matchEvent("plain")&&this.firePlainEvent(t,n)}matchEvent(t){return null!=this.config[t]?!!this.config[t]:!this.config.strict}onBxEvent(t,n){"undefined"!=typeof BX&&BX.addCustomEvent(t,n)}offBxEvent(t,n){"undefined"!=typeof BX&&BX.removeCustomEvent(t,n)}fireBxEvent(t,n){"undefined"!=typeof BX&&BX.onCustomEvent(t,[n])}onJQueryEvent(t,n){if("undefined"==typeof jQuery)return;const e=this.typeConfig("jquery");if(this.canProxyCallback(e)){const t=n;n=(n,e)=>{var a;const i=null!=e?e:null==n||null==(a=n.originalEvent)?void 0:a.detail;t(i)},this.pushCallbackVariation("jquery",t,n)}jQuery(document).on(t,n)}offJQueryEvent(t,n){if("undefined"==typeof jQuery)return;const e=this.typeConfig("jquery");this.canProxyCallback(e)&&null==(n=this.popCallbackVariation("jquery",n))||jQuery(document).off(t,n)}fireJQueryEvent(t,n){"undefined"!=typeof jQuery&&(this.hasPlainAndJQueryCollision()||jQuery(document).triggerHandler(new CustomEvent(t,{detail:n})))}onPlainEvent(t,n){if(this.hasPlainAndJQueryCollision())return;const e=this.typeConfig("plain");if(this.canProxyCallback(e)){const t=n;n=n=>{t(n.detail)},this.pushCallbackVariation("plain",t,n)}document.addEventListener(t,n)}offPlainEvent(t,n){if(this.hasPlainAndJQueryCollision())return;const e=this.typeConfig("plain");this.canProxyCallback(e)&&null==(n=this.popCallbackVariation("plain",n))||document.removeEventListener(t,n)}firePlainEvent(t,n){document.dispatchEvent(new CustomEvent(t,{detail:n}))}hasPlainAndJQueryCollision(){if(!this.matchEvent("plain")||!this.matchEvent("jquery"))return!1;return!0!==this.typeConfig("plain").force&&"undefined"!=typeof jQuery}canProxyCallback(t){return!1!==t.proxy}typeConfig(t){return"object"==typeof this.config[t]&&null!=this.config[t]?this.config:{}}pushCallbackVariation(t,n,e){null==this.callbackMap[t]&&(this.callbackMap[t]=new WeakMap),this.callbackMap[t].set(n,e)}popCallbackVariation(t,n){if(null==this.callbackMap[t])return null;const e=this.callbackMap[t],a=e.get(n);return null!=a&&e.delete(n),a}}class e{bootFactory(t){}bootWidget(t){}bootCart(t){}onEvent(t,e,a={}){n.make(a).on(t,e)}}const a=new class{constructor(t){this.classMap={},this.classMap=t}create(t){const n=this.classMap[t];return null==n?null:new n}}({element:class extends e{bootCart(t){this.onEvent("onCatalogElementChangeOffer",(n=>{let e=parseInt(null==n?void 0:n.newId,10);isNaN(e)||t.delayChangeOffer(e)}))}},basket:class extends e{bootFactory(t){t.extendDefaults({preserve:{mutation:{anchor:'[data-entity="basket-total-block"]',delay:null}}})}bootCart(t){"undefined"!=typeof BX&&this.onEvent("OnBasketChange",(()=>{t.delayChangeBasket()}))}},order:class extends e{bootFactory(t){t.extendDefaults({preserve:{mutation:{anchor:".bx-soa-cart-total",delay:null}}})}}});t.factory=a}(this.BX.YandexPay.Solution.EshopBootstrap=this.BX.YandexPay.Solution.EshopBootstrap||{});
//# sourceMappingURL=solution.js.map
