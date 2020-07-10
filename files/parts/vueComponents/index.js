import Discount_zaful_m from './discount_float/zf-m-2.vue';
import Shopprice_zaful from './shop_price_2/zaful.vue';
import Marketprice_zaful from './market_price_2/zaful.vue';
import GoodsImage_zaful from './image_goods/zf-m-2.vue';
import loadMore_zaful from './load_more/zf-m.vue';
import fixedTop_zaful from './fixed_top/index.vue';
import progress_bar_zaful from './progress_bar/zf-m-2';

// 所有组件列表
const components = [
    Discount_zaful_m,
    Shopprice_zaful,
    Marketprice_zaful,
    GoodsImage_zaful,
    loadMore_zaful,
    fixedTop_zaful,
    progress_bar_zaful
];

// VUE 安装包, 注册所有插件
const install = (Vue) => {
    components.map(component => {
        Vue.component(component.name, component);
    });
};

export default {
    install,
    Discount_zaful_m,
    Shopprice_zaful,
    Marketprice_zaful,
    GoodsImage_zaful,
    loadMore_zaful
};
