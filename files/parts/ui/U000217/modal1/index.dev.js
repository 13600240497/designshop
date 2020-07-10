;$(function () {
    $('.geshop-u000217-modal1').each((index, item) => {
        const $wrap = $(item);
        let timer = null;

        let data = null;

        function renderHotArea () {
            const dataPC = $wrap.attr('data-renderCoods');
            const dataM = $wrap.attr('data-renderCoodsM');
            const dataPad = $wrap.attr('data-renderCoodsIpad');
            $wrap.find('.hotArea').remove();
            let renderData = {};
            let clientW = $('#design_view').width() ? $('#design_view').width() : $('body').width();

            if (clientW > 1024) {
                data = dataPC;
            } else if (clientW <= 1024 && clientW >= 768) {
                data = dataPad;
            } else {
                data = dataM;
            }

            if (data && data != 'null') {
                try {
                    renderData = JSON.parse(data).renderData;
                    renderData.forEach((item) => {
                        let html = '';
                        html = `<a href="${item.url == '' ? 'javascript:void(0)' : item.url}" 
                       target="${item.url == '' ? '_self' : '_blank'}" 
                       class="hotArea"
                       style="width: ${item.coords.width};height: ${item.coords.height};left: ${item.coords.left};top: ${item.coords.top};position: absolute;">
                    </a>`;
                        $wrap.find('.geshop-u000217-container').append($(html));
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
