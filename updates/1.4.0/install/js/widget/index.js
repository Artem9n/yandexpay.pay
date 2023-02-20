import FactoryClass from './factory';
import WidgetClass from './widget';

const previousFactory = window.BX?.YandexPay?.Factory;
const previousWidget = window.BX?.YandexPay?.Widget;

export const Factory = previousFactory ?? FactoryClass;
export const Widget = previousWidget ?? WidgetClass;