
(function () {
  // Community list item hover
  $("[data-gid=U000063_link] .js_buyer_show .buyer-show-item").mouseenter(function () {
    $(this).find(".view-it").stop().fadeIn();
    var imgUrl = $(this).find('.view-it').find('.icon-camer').data('original');
    $(this).find('.view-it').find('.icon-camer').attr("src", imgUrl);
  }).mouseleave(function () {
    $(this).find(".view-it").stop().fadeOut();
  });

})();