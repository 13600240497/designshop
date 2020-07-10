$(function () {
  var navigateTarget = $('#nav-ul');
  // 监听pageTo参数，滚动到对应的区域
  if (window.location.hash.split('#').length > 1) {
    var pageTo = window.location.hash.split('#')[1].split('=')[1];
    // 滑动目标对象
    var pageToTarget = $('.geshop-component-box[data-id=' + pageTo + ']');
    var pageToTargetTop = 0;
    // -1 则不执行滑动
    if (pageToTarget.length > 0) {
      pageToTargetTop = pageToTarget.offset().top - navigateTarget.height();
      $('html, body').animate({ scrollTop: pageToTargetTop }, 500);
    };
  }
 
  var timer = null;

  $(window).scroll(function () {

    // 是否固定导航
    var nav_is_fixed = false;

    if ($(this).scrollTop() >= navigateTarget.parent().offset().top - GESHOP_UTIL.getSiteFiedHeader()) {
        nav_is_fixed = true;
        navigateTarget.addClass('component-nav-fixed');

        // 站点导航栏处理
        if ($('.js-geshop-nav').length) {
            $('.js-geshop-nav').hide();
        }

    } else {
        nav_is_fixed = false;
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

                var pageTo = '#pageTo=' + target.data('id');
                window.location.replace(location.protocol + '//' + location.hostname + location.pathname + location.search + pageTo)
            
            }
            }
        }

        // 非固定 nav 的情况去除 pageTo 参数
        if (nav_is_fixed === false) {
            window.location.replace(location.protocol + '//' + location.hostname + location.pathname + location.search + '#pageTo=-1');
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
    var pageTo = '#pageTo=' + navigatedId;
    window.location.replace(location.protocol + '//' + location.hostname + location.pathname + location.search + pageTo)

    return false;
  });
});
