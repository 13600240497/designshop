$(function () {
  var navigateTarget = $('#nav-ul');
  if(window.location.hash.split('#').length > 1){
    var pageTo = window.location.hash.split('#')[1].split('=')[1];
    $('html, body').animate({
      scrollTop: $('.geshop-component-box[data-id=' + pageTo + ']').offset().top - navigateTarget.height()
    }, 500);
  }

  var navigateOffsetTop = navigateTarget.offset().top;
  var timer = null;

  $(window).scroll(function () {
    if ($(window).scrollTop() > navigateTarget.parent().offset().top - GESHOP_UTIL.getSiteFiedHeader()) {
        navigateTarget.addClass('component-nav-fixed');
        // 站点导航栏处理
        if ($('.js-geshop-nav').length) {
            $('.js-geshop-nav').hide();
        }

    } else {
        navigateTarget.removeClass('component-nav-fixed');

        // 站点导航栏处理
        if ($('.js-geshop-nav').length) {
            $('.js-geshop-nav').show();
        }
    }

    var scrollTopBefore = $(window).scrollTop();
    
    clearTimeout(timer);
    timer = setTimeout(function () {
      var scrollTopAfter = $(window).scrollTop();
      
      if (scrollTopBefore == scrollTopAfter) {
        var navigations = navigateTarget.find('li');
        var length = navigations.length;

        for (var index = 0; index < length; index = index + 1) {
          var target = $(navigations.get(index));
          var navigatedId = target.data('id');
          var titleTarget = $('.geshop-component-box[data-id=' + navigatedId + ']');
          
          if (scrollTopAfter > titleTarget.offset().top - titleTarget.height()) {
            target.addClass('current');
            target.siblings().removeClass('current');
            // window.location.href = 
            var pageTo = '#pageTo='+ target.data('id');
            window.location.replace(location.protocol + '//' + location.hostname + location.pathname + location.search + pageTo)
          }
        }
      }
    }, 50);
  });

  navigateTarget.on('click', 'li', function () {
    var target = $(this);
    var navigatedId = target.data('id');

    target.addClass('current').siblings().removeClass('current');
    $('html, body').animate({
      scrollTop: $('.geshop-component-box[data-id=' + navigatedId + ']').offset().top - navigateTarget.height()
    }, 500);

    // window.location.href = '#pageTo='+navigatedId;
    var pageTo = '#pageTo='+ navigatedId;
    window.location.replace(location.protocol + '//' + location.hostname + location.pathname + location.search + pageTo)

    return false;
  });
});
