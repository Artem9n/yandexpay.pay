this.BX=this.BX||{},this.BX.YandexPay=this.BX.YandexPay||{},this.BX.YandexPay.Solution=this.BX.YandexPay.Solution||{},function(t){"use strict";class e{static make(t={}){return new e(t)}constructor(t={}){this.config=t,this.callbackMap={}}on(t,e){this.matchEvent("bx")&&this.onBxEvent(t,e),this.matchEvent("jquery")&&this.onJQueryEvent(t,e),this.matchEvent("plain")&&this.onPlainEvent(t,e)}off(t,e){this.matchEvent("bx")&&this.offBxEvent(t,e),this.matchEvent("jquery")&&this.offJQueryEvent(t,e),this.matchEvent("plain")&&this.offPlainEvent(t,e)}fire(t,e={}){this.matchEvent("bx")&&this.fireBxEvent(t,e),this.matchEvent("jquery")&&this.fireJQueryEvent(t,e),this.matchEvent("plain")&&this.firePlainEvent(t,e)}matchEvent(t){return null!=this.config[t]?!!this.config[t]:!this.config.strict}onBxEvent(t,e){"undefined"!=typeof BX&&BX.addCustomEvent(t,e)}offBxEvent(t,e){"undefined"!=typeof BX&&BX.removeCustomEvent(t,e)}fireBxEvent(t,e){"undefined"!=typeof BX&&BX.onCustomEvent(t,[e])}onJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");if(this.canProxyCallback(n)){const t=e;e=(e,n)=>{var a;const i=null!=n?n:null==e||null==(a=e.originalEvent)?void 0:a.detail;t(i)},this.pushCallbackVariation("jquery",t,e)}jQuery(document).on(t,e)}offJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("jquery",e))||jQuery(document).off(t,e)}fireJQueryEvent(t,e){"undefined"!=typeof jQuery&&(this.hasPlainAndJQueryCollision()||jQuery(document).triggerHandler(new CustomEvent(t,{detail:e})))}onPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");if(this.canProxyCallback(n)){const t=e;e=e=>{t(e.detail)},this.pushCallbackVariation("plain",t,e)}document.addEventListener(t,e)}offPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("plain",e))||document.removeEventListener(t,e)}firePlainEvent(t,e){document.dispatchEvent(new CustomEvent(t,{detail:e}))}hasPlainAndJQueryCollision(){if(!this.matchEvent("plain")||!this.matchEvent("jquery"))return!1;return!0!==this.typeConfig("plain").force&&"undefined"!=typeof jQuery}canProxyCallback(t){return!1!==t.proxy}typeConfig(t){return"object"==typeof this.config[t]&&null!=this.config[t]?this.config:{}}pushCallbackVariation(t,e,n){null==this.callbackMap[t]&&(this.callbackMap[t]=new WeakMap),this.callbackMap[t].set(e,n)}popCallbackVariation(t,e){if(null==this.callbackMap[t])return null;const n=this.callbackMap[t],a=n.get(e);return null!=a&&n.delete(e),a}}class n{bootFactory(t){}bootWidget(t){}bootCart(t){}onEvent(t,n,a={}){e.make(a).on(t,n)}}var a=(t,e)=>{const n={template:'<div class="bx-yapay-divider-container width--#WIDTH#"><div class="bx-yapay-divider width--#WIDTH#"><span class="bx-yapay-divider__corner"></span><span class="bx-yapay-divider__text">#LABEL#</span><span class="bx-yapay-divider__corner at--right"></span></div>'+t.getOption("template")+"</div>"};t.extendDefaults(Object.assign({},e,n))};class i extends n{bootFactory(t){t.extendDefaults({preserve:{mutation:{anchor:'[data-entity="basket-total-block"]',delay:null}}})}bootCart(t){"undefined"!=typeof BX&&this.onEvent("OnBasketChange",(()=>{t.delayChangeBasket()}))}}const o=new class{constructor(t){this.classMap={},this.classMap=t}create(t){const e=this.classMap[t];return null==e?null:new e}}({element:class extends n{bootFactory(t){a(t)}bootCart(t){"undefined"!=typeof BX&&"undefined"!=typeof JCCatalogElement&&BX.addCustomEvent("onAsproSkuSetPrice",(e=>{var n;let a=parseInt(null==e||null==(n=e.offer)?void 0:n.ID,10);isNaN(a)||t.delayChangeOffer(a)}))}},basket:class extends i{bootFactory(t){a(t,{preserve:{mutation:{anchor:'[data-entity="basket-total-block"]',delay:null}}})}},order:class extends n{bootFactory(t){a(t,{preserve:{mutation:{anchor:"#bx-soa-total, #bx-soa-total-mobile",delay:null}}})}}});t.factory=o}(this.BX.YandexPay.Solution.AsproNext=this.BX.YandexPay.Solution.AsproNext||{});
//# sourceMappingURL=solution.js.map
