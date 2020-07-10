;$(function () {
    $('.geshop-u000240-template1').each((index, item) => {
        const $wrap = $(item);
        const data = $wrap.attr('data-renderCoods');
        const openTarget = $wrap.attr('data-target');
        let renderData = {};
        if (!!data) {
            try {
                renderData = JSON.parse(data).renderData;
                renderData.forEach((item) => {
                    let html = '';
                    html = `<a href="${item.url == '' ? 'javascript:void(0)' : item.url}"
                        target="${item.url == '' ? '_self' : openTarget}" 
                       class="hotArea"
                       style="width: ${item.coords.width};height: ${item.coords.height};left: ${item.coords.left};top: ${item.coords.top};position: absolute;">
                    </a>`;
                    $wrap.find('.geshop-u000240-container').append($(html));
                });
            } catch (e) {

            }
        }
    });
});
