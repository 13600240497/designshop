;$(function () {
    $('.geshop-u000230-modal1').each((index, item) => {
        const $wrap = $(item);
        let timer = null;
        let data = null;
        let openType = null;

        function renderHotArea () {
            const dataPC = $wrap.attr('data-renderCoods');
            const dataM = $wrap.attr('data-renderCoodsM');
            const dataPad = $wrap.attr('data-renderCoodsIpad');
            const is_open_new = $wrap.attr('data-isopennew');
            const is_open_new_m = $wrap.attr('data-isopennewm');
            $wrap.find('.hotArea').remove();
            let renderData = {};
            let clientW = $('#design_view').width() ? $('#design_view').width() : $('body').width();

            if (clientW > 1024) {
                data = dataPC;
                openType = is_open_new;
            } else if (clientW <= 1024 && clientW >= 768) {
                data = dataPad;
                openType = is_open_new;
            } else {
                data = dataM;
                openType = is_open_new_m;
            }

            if (data && data != 'null') {
                try {
                    renderData = JSON.parse(data).renderData;
                    renderData.forEach((item) => {
                        let html = '';
                        html = `<a href="${item.url == '' ? 'javascript:void(0)' : item.url}" 
                       target="${openType}" 
                       class="hotArea"
                       style="width: ${item.coords.width};height: ${item.coords.height};left: ${item.coords.left};top: ${item.coords.top};position: absolute;">
                    </a>`;
                        $wrap.find('.geshop-u000230-container').append($(html));
                    });
                } catch (e) {
                }
            }
        }

        renderHotArea();
        window.onresize = () => {
            clearTimeout(timer);
            timer = setTimeout(renderHotArea, 100);
        };
    });
});
