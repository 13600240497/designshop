
;(function() {

  var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : '';
  $('.wrap-default-U000175').each(function (i, element) {
    var $ele = $(element);
    // 剔除sold out状态商品
    $ele.find('li.good_soldOut').remove();
  });

})();