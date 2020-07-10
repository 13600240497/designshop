function beforeSubmit(progress) {
    var _val = $('#U0000164-coupon input[name="active_id"]').val();

    if (_val) {
        getCouponDetail(_val, function() {
            progress.next()
        });
    } else {
        layer.msg('请填写活动id');
        progress.cancel()
    }
   
}

$('input[name=bg_img]').on('input',function(){
    var image = new Image();
    image.src = $(this).val();
    image.onload = function(){
        $('input[name=bg_width]').val(image.width);
        $('input[name=bg_height]').val(image.height);
    }
    
});

var $activeId = $('input[name="active_id"]');

var getCouponDetail = function getCouponDetail(_value, callback){
  var _url = GESHOP_INTERFACE.cmsgoods_pointsProductDetail.url;
  return _jsonp({
      url: _url,
      data: {
          content : JSON.stringify({
              lang : GESHOP_LANG,
              id : _value,
              platform: GESHOP_PLATFORM,
              pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
          })
      },
      success :function(res){
        
          if(res.code === 0){
              if(res.exist == 1 && res.data.is_open == 1 ){
                  window.promo_code = res.promo_code;
                  callback()
              }else{
                layer.msg('活动id不存在或不生效，请重新输入');
                _clearInput();
                $activeId.focus();
              }

          }
      }
  });
};


var _jsonp = function _jsonp(param){
    param = param || {};
    return $.ajax($.extend({
        dataType: "jsonp",
        jsonp: 'callback',
    }, param));
};



var _clearInput = function _clearInput(){
    $activeId.val('');
};

/** 清空无效的 */
function cleanPromotionID(invalid) {
    var arr1 = invalid.split(',');
    var arr2 = $('[name="promotion_ids"]').val().split(',');
    var reduce = arr2.filter(function(x) {
        return arr1.indexOf(x) < 0;
    });
    return reduce.join(',');
}

/** 展示弹窗 */
function showConfirm(content, next) {
    layer.confirm(content, {
        title: '提示',
        btn: ['否', '是'],
        area: '420px',
        icon: 3,
        skin: 'element-ui-dialog-class'
    }, function (index) {
        layer.close(index);
    }, function (index) {
        next();
    });
}
