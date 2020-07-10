
;$(function () {
    $('.geshop-U000213-default').each(function(){
        var $elem = $(this);
        var isEditEnv = $(this).find('input[name=isEditEnv]').val();
        if(isEditEnv == 1){
            $(this).find('.layer-share').show();
        }

        /* 初始化加载addthis */
        if(GESHOP_PLATFORM == 'wap'){
            addLoadEvent(function() {
                var script = document.createElement('script');
                if(GESHOP_SITECODE === 'rg-wap'){
                    script.src = '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-54c2151b31fb2710';
                }else if (GESHOP_SITECODE === 'zf-wap'){
                    script.src = '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5a38671bb83b79fe';
                }
                document.getElementsByTagName('head')[0].appendChild(script);
            });
        }

        $('body').on('touchstart','.share-btn',function(){
          if(GESHOP_PLATFORM == 'wap'){
              var options = {
                  content: $('.layer-share'),
                  modalWidth: '9rem',
                  className: 'dialog-share',
              }
              typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.custom(options);
          }else if(GESHOP_PLATFORM == 'app'){
              var shareTitle =  $elem.find('input[name=share_data_title]').val();
              var shareDesc = $elem.find('input[name=share_data_desc]').val();
              var shareLink =  $elem.find('input[name=share_data_link]').val();
              var shareImg =  $elem.find('input[name=share_data_img]').val();
              window.location.href = "webAction://sharing?shareUrl="+ shareLink +"&shareTitle="+ shareTitle +"&shareContent="+ shareDesc +"&imageUrl=" + shareImg ;
          }
        });

        $('body').on('touchstart','.cancel-btn',function(){
            typeof GEShopSiteCommon !== 'undefined' && GEShopSiteCommon.dialog.unblock();
        });
    });

    function addLoadEvent (func) {
        var oldonload = window.onload;

        if (typeof window.onload != 'function') {
            window.onload = func;
        } else {
            window.onload = function() {
                oldonload();
                func();
            }
        }
    }

  });
