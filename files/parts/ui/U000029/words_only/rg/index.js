$(function () {
  var navigateTarget = $('#nav-vertical-ul');
  var navId = navigateTarget.find('li:first').data('id') || 0;
  var closestTarget = navigateTarget.closest('.component-vertical-nav');
  var navigateOffsetTop = $('.geshop-component-box[data-id='+ navId +']:first').offset().top;
  var timer = null;

  if (navigateTarget.data('isedit') == 0) {
    closestTarget.addClass('component-vertical-nav-online');
  }

  var windowWidth = $(window).width();
  $("#js-backToTop,#backToTop").remove();
  var navCtrolFlag = $('.component-vertical-nav-ctronlFlag');
  $('.wrap-u000029-words_oly-nav-vertical-ul-wrap').width($('.wrap-u000029-words_oly-nav-vertical-ul>li').outerWidth());

  var resizeTimeout = null;
  $(window).resize(function(){
    resizeTimeout && clearTimeout(resizeTimeout)
    resizeTimeout = setTimeout(function(){
      windowWidth = $(window).width();
      //smallWindowNavShowStyleObject.isShowed = false;
      if(windowWidth < 1700){
        navCtrolFlag.show();
        smallWindowNavSlideHide(navCtrolFlag);
          if(closestTarget.hasClass('component-show-always')){
              smallWindowNavShowStyle(navCtrolFlag);
          }
      }else{
        navCtrolFlag.hide();
        smallWindowNavSlideShow(navCtrolFlag);
      }
    },1000);
  });


  var smallWindowNavShowStyleObject = {
    isFirst:true,
    isShowed : false,
    closestTargetOffsetValue:0,
    navCtrolFlagOffsetValue:0,
    timeFn:null
  }
    /**
     * always show slideInitial
     */
    if(closestTarget.hasClass('component-show-always')){
        slideInitial(navCtrolFlag);
    }

    function slideInitial(navCtrolFlag){
        windowWidth = $(window).width();
        //smallWindowNavShowStyleObject.isShowed = false;
        if(windowWidth < 1700){
            navCtrolFlag.show();
            smallWindowNavSlideHide(navCtrolFlag);
            if(closestTarget.hasClass('component-show-always')){
                smallWindowNavShowStyle(navCtrolFlag);
            }else{
                navCtrolFlag.hide();
                smallWindowNavSlideShow(navCtrolFlag);}
        }
    }
  function smallWindowNavShowStyle(navCtrolFlag){
      if(smallWindowNavShowStyleObject.isFirst){
        smallWindowNavShowStyleObject.isFirst = false;
        if(!smallWindowNavShowStyleObject.rcodePosition){
          smallWindowNavShowStyleObject.rcodePosition = true;
          if(navCtrolFlag.data('position')=='left'){
            smallWindowNavShowStyleObject.closestTargetOffsetValue = closestTarget.offset().left;
            smallWindowNavShowStyleObject.navCtrolFlagOffsetValue = navCtrolFlag.offset().left;
          }else if(navCtrolFlag.data('position')=='right'){
            smallWindowNavShowStyleObject.closestTargetOffsetValue = $(window).width() - closestTarget.offset().left - closestTarget.outerWidth();
            smallWindowNavShowStyleObject.navCtrolFlagOffsetValue  = $(window).width() - closestTarget.offset().left ;
          }
        }

        clearTimeout(smallWindowNavShowStyleObject.timeFn);
        smallWindowNavShowStyleObject.timeFn = setTimeout(function (params) {
          smallWindowNavSlideHide(navCtrolFlag);

        },500)

      }
  }


  function smallWindowNavSlideHide(navCtrolFlag){

      closestTarget.css("transition","all 0.3s");
      smallWindowNavShowStyleObject.isShowed = false;

      if(navCtrolFlag.data('position')=='left'){
        var animateWidth = 0-smallWindowNavShowStyleObject.navCtrolFlagOffsetValue;
        navCtrolFlag.removeClass('component-vertical-nav-ctronlFlag-narrowLeft').addClass('component-vertical-nav-ctronlFlag-narrowRight');
        closestTarget.css("left",animateWidth);
      }else if(navCtrolFlag.data('position')=='right'){
        var navCtrolFlagOffsetValue = smallWindowNavShowStyleObject.navCtrolFlagOffsetValue ;
        var animateWidth = 0-navCtrolFlagOffsetValue;
        navCtrolFlag.removeClass('component-vertical-nav-ctronlFlag-narrowRight').addClass('component-vertical-nav-ctronlFlag-narrowLeft');
        closestTarget.css("right",animateWidth);
      }
      closestTarget.css("transition",null)
  }
  function smallWindowNavSlideShow(navCtrolFlag){
     smallWindowNavShowStyleObject.isShowed = true;
    if(navCtrolFlag.data('position')=='left'){

      closestTarget.css("left",smallWindowNavShowStyleObject.closestTargetOffsetValue+'px');
    }else if(navCtrolFlag.data('position')=='right'){
      closestTarget.css("right",smallWindowNavShowStyleObject.closestTargetOffsetValue+'px');

    }
  }

  navCtrolFlag.on("click",function(){
    if(navCtrolFlag.hasClass('component-vertical-nav-ctronlFlag-narrowLeft')){
      navCtrolFlag.removeClass('component-vertical-nav-ctronlFlag-narrowLeft').addClass('component-vertical-nav-ctronlFlag-narrowRight');
    }else if(navCtrolFlag.hasClass('component-vertical-nav-ctronlFlag-narrowRight')){
      navCtrolFlag.removeClass('component-vertical-nav-ctronlFlag-narrowRight').addClass('component-vertical-nav-ctronlFlag-narrowLeft');
    }
    if(smallWindowNavShowStyleObject.isShowed){
      smallWindowNavSlideHide(navCtrolFlag);
    }else{
      smallWindowNavSlideShow(navCtrolFlag);
    }
  });

  $(window).scroll(function () {
    if ($(window).scrollTop() > navigateOffsetTop) {
      closestTarget.show();
      if(windowWidth < 1700){
        navCtrolFlag.show();
        smallWindowNavShowStyle(navCtrolFlag);

      }else{
        navCtrolFlag.hide();
        smallWindowNavSlideShow(navCtrolFlag);
      }
    } else {
      closestTarget.hide();
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


          if(navigatedId){
            var titleTarget = $('.geshop-component-box[data-id=' + navigatedId + ']');

            // 站点导航高度
            var top_nav_height = $('.js-geshop-nav').height() || 0;
            // 水平导航高度
            var geshop_nav_height = $('.component-nav-fixed').height() || top_nav_height;

            if (scrollTopAfter >= parseInt(titleTarget.offset().top) - parseInt(geshop_nav_height)) {
              target.addClass('vertical-current');
              target.siblings().removeClass('vertical-current');
            }
          }

        }
      }
    }, 50);
  });

  navigateTarget.on('click', 'li', function () {
    var target = $(this);
    var navigatedId = target.data('id');

    target.addClass('vertical-current').siblings().removeClass('vertical-current');

    // 站点导航高度
    var top_nav_height = $('.js-geshop-nav').height() || 0;
    // 水平导航高度
    var geshop_nav_height = $('.component-nav-fixed').height() || top_nav_height;
    var title_target_top = parseInt($('.geshop-component-box[data-id=' + navigatedId + ']').offset().top);
    
    $('html, body').animate({
      scrollTop: title_target_top - geshop_nav_height
    }, 500);
  });
});
