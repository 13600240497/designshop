if (GESHOP_PLATFORM === 'app') {
    $('[name="pecent_coupon_link"]').val('')
}

var $box = $('#{{ formData.id }}-{{ formData.theme }}');
var $couponid = $box.find('input[name="coupons_id"]');
var couponId = '{{ data.coupons_id }}';
var _inputFirm = function _inputFirm(text){
    var _index = layer.confirm(text || '优惠券ID错误，请正确填写优惠券ID', {
          btn: ['取消', '确定'],
          area: '420px',
          icon: 3,
          skin: 'element-ui-dialog-class'
    },function(){
        $couponid.focus();
        layer.close(_index);
    },function(){
        $couponid.focus();
    });
};
var _jsonp = function _jsonp(param){
        param = param || {};
        return $.ajax($.extend({
            dataType: "jsonp",
            jsonp: 'callback',
        }, param));
    };
var getCouponDetail = function getCouponDetail(_value, callback){
  var _url = GESHOP_INTERFACE.coupondetail.url;
  return _jsonp({
      url: _url,
      data: {
          content : JSON.stringify({
              lang : '{{ lang }}',
              couponid : _value,
              pipeline: (typeof GESHOP_PIPELINE != 'undefined' ? GESHOP_PIPELINE : '')
          })
      },
      success :function(res){
          if(res.code === 0){
              var couponInfo = res.data.couponInfo || {};
              if(!couponInfo.id){
                  _inputFirm();
                  _clearInput();
              } else {
                  callback && callback()
              }
          }else{
              _inputFirm();
              _clearInput();
          }
      }
  });
};
var _clearInput = function _clearInput(){
    $couponid.val('');
};

if(couponId){
   getCouponDetail(couponId);
}

function onSubmit(progress) {
    var $val = $couponid.val();

    if ($.trim($val) == '') {
        $couponid.val('');
        progress.next();
    } else if (!isNaN(+$val) && $val > 0) {
        progress.next();
    } else {
        $couponid.val('');
        _inputFirm('请填写优惠券ID');
    }
};

function skuValidConfig() {
    return {
        check_type: 'coupon',
        check_rules: 'COUPON_VALIDATE_ID_EXITS'
    }
}
