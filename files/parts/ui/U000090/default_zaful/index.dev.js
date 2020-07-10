loadCss(GESHOP_STATIC+'/resources/javascripts/library/swiper/swiper.min.css');


function init_U000090_default_zaful(id, type, cateid, a_analytics, img_analytics, loop_hour, last_save_time) {
	const _lang = GESHOP_LANG || 'en';
	const _pipeline = (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '');
	const _type = type;
	const _cateid_array = cateid.split(','); //  活动ID，支持多ID
	const _loop_hour = loop_hour; // 循环的小时
    const _last = parseInt(last_save_time);
	// 根据[循环小时]数，获取对应的[活动ID]
	const get_cateid_with_loop_hour = (loop_hour) => {
		if (loop_hour != '') {
			let now = new Date().getTime();
			let hour = (now - _last) / 1000 / 3600;
			let array_index = parseInt(((hour / loop_hour) % _cateid_array.length));
			// console.log(hour, _loop_hour, array_index);
			return array_index;
		} else {
			return 0
		}
	}
	
	const _languages = {
		1: {
			en: '<strong><% item.sale_number %></strong> Pieces Sold After <strong><% item.sale_days %></strong> Days Released',
			fr: '<strong><% item.sale_number %></strong> pièces vendues après <strong><% item.sale_days %></strong> jours de sortie',
			es: '<strong><% item.sale_number %></strong> prendas vendidas después de <strong><% item.sale_days %></strong> días desde su lanzamiento',
			de: 'Nach <strong><% item.sale_days %></strong> Tagen werden <strong><% item.sale_number %></strong> Stücke verkauft',
			pt: '<strong><% item.sale_number %></strong> peças vendidas após <strong><% item.sale_days %></strong> dias lançados',
			it: '<strong><% item.sale_number %></strong> Pezzo Venduti Dopo <strong><% item.sale_days %></strong> giorni dal Lancio',
			default: '<strong><% item.sale_number %></strong> Pieces Sold After <strong><% item.sale_days %></strong> Days Released',
		},
		2: {
			en: 'Already Sold <strong><% item.sale_number %></strong> Pieces',
			fr: '<strong><% item.sale_number %></strong> pièces vendues',
			es: 'Ya se han vendido <strong><% item.sale_number %></strong> piezas',
			de: '<strong><% item.sale_number %></strong> Stücke werden bereits verkauft',
			pt: 'Já Vendi <strong><% item.sale_number %></strong> Peças',
			it: '<strong><% item.sale_number %></strong> Pezzi Sono Stati Venduti',
			default: 'Already Sold <strong><% item.sale_number %></strong> Pieces',
		},
		3: {
			en: '<strong><% item.discount %></strong>% OFF',
			fr: '<strong><% item.discount %></strong>% de réduction',
			es: '<strong><% item.discount %></strong>% de descuento',
			de: '<strong><% item.discount %></strong>% Rabatt',
			pt: '<strong><% item.discount %></strong>% de desconto',
			it: 'Sconto del <strong><% item.discount %></strong>%',
			default: '<strong><% item.discount %></strong>% OFF',
		}
	};

    new Vue({
        el: id,
        delimiters: ["<%", "%>"],
        data() {
            return {
                loading: true,
                config: {
                    element_id: id,
                },
                a_analytics: a_analytics,
                // 图片曝光的logsss参数 {goods_sn, columnIndex}
                img_analytics_template: img_analytics,
                /* 默认的商品数据 */
                goodsInfo: [
                    {
                        shop_price: '0.00',
                        market_price: '0.00',
                        goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                        goods_img: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png'
                    },
                    {
                        shop_price: '0.00',
                        market_price: '0.00',
                        goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                        goods_img: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png'
                    },
                    {
                        shop_price: '0.00',
                        market_price: '0.00',
                        goods_title: 'ZAFUL Fleece Vest And Corduroy Jack',
                        goods_img: 'https://geshopimg.logsss.com/uploads/nxQshzC1wXTYy8BmHD6GE39vLWjciKaR.png'
                    },
                ],
            }
        },
        computed: {
            show() {
                return this.loading ? 'none' : 'block';
            }
        },
        methods: {
            /* 输出多语言的按钮文案 */
            output_selling_point(product) {
                const str = _languages[type][_lang] || '';
                return str
                .replace('<% item.sale_number %>', product.sale_number || 0)
                .replace('<% item.sale_days %>', product.sale_days || 0)
                .replace('<% item.discount %>', product.sale_number || 0);
            },
            /* 初始化SWIPER */
            init_swiper() {
                this.$nextTick(() => {
                    var _self = this;
                    $LAB.script(GESHOP_STATIC + '/resources/javascripts/library/swiper/swiper.3.4.spec.min.js').wait(function() {
                        var swiperContainer = $(id).find('.swiper-container');
                        var prevButtonElement = $(id).find('.button-swiper-prev');
                        var nextButtonElement = $(id).find('.button-swiper-next');
                        new Swiper3(swiperContainer, {
                            cssWidthAndHeight: true,
                            slidesPerView: 3,
                            spaceBetween: 16,
                            autoResize: false,
                            slidesPerGroup: 3,
                            preventClicks: false,
                            followFinger: false,
                            autoplay: 5000,
                            loop: true,
                            paginationClickable: true,
                            prevButton: prevButtonElement,
                            nextButton: nextButtonElement,
                            lazyLoading: true,
                            onLazyImageLoad: function (swiper, slide, image) {
                                if (typeof gbLogsss != 'undefined') {
                                    gbLogsss.getsku($(image));
                                    gbLogsss.sendsku();
                                }
                            }
                        });
                    });
                });
            },
            // 渲染数据
            setdatas() {
                this.loading = false;
                // dom 操作
                this.$nextTick(function() {
                    // 货币符号切换
                    window.GLOBAL && window.GLOBAL.currency && window.GLOBAL.currency.change_html();
                    window.FUN && window.FUN.currency && window.FUN.currency.change_html;
                });
            }
        },
        mounted() {
            // 根据时间获取下标
            let index = get_cateid_with_loop_hour(_loop_hour);
            // 获取对应的[活动ID]
            let cateid = _cateid_array[index] || '';
            // AJAX参数
            const ajax_params = {
                type: _type,
                lang: _lang,
                cateid,
                pipeline: _pipeline,
                pageno: 1,
                pagesize: 12,
            };
            const url = GESHOP_INTERFACE.getrankdetail.url;
            const pid = $(id).parents('.geshop-component-box').eq(0).attr('data-id');
            window.GEShopCommonFn_Vue.$jsonp(url, ajax_params, {pid: pid}).done(res => {
                this.loading = false;
                if (res.data.goodsInfo.length > 0) {
                    this.goodsInfo = res.data.goodsInfo || [];
                }
                // create image logsss params
                this.goodsInfo.map((item, index) => {
                    item['a_analytics'] = this.a_analytics.replace('%sku%', item.goods_sn).replace('%sku%', item.goods_sn).replace('%warehousecode%', item.warehousecode).replace('%cateid%', item.cateid).replace('%columnIndex%', index)
                    item['img_analytics'] = this.img_analytics_template.replace('%sku%', item.goods_sn).replace('%columnIndex%', index)
                })
                this.init_swiper();
                this.setdatas();
            })
        }
    });
}

