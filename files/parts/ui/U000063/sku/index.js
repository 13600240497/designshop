(function () {

  window.share = {

    fackbook: {
      init: function (callback) {
        (function (d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) {
            return;
          }
          js = d.createElement(s);
          js.id = id;
          js.src = "//connect.facebook.net/en_US/sdk.js";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        window.fbAsyncInit = function () {
          FB.init({
            appId: '188043401576047',
            cookie: true,
            oauth: true,
            xfbml: true,
            version: 'v2.5'
          });
        };
      },
      start: function (cfg, callback) {
        var _this = this;
        cfg = cfg || {};
        FB.ui({
          method: 'share',
          mobile_iframe: true,
          href: cfg.link,
          picture: cfg.picture,
          description: cfg.description
        }, function (res) {
          if (!(res != 'undefined' && res instanceof Array)) return;
          if (typeof callback == 'function') {
            callback();
          }
        });
      }
    },
    twitter: {
      init: function (callback) {
        var _this = this;
        $("body").append('<div id="twitter-root"></div>');
        window.twttr = (function (d, s, id) {
          var t, js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s);
          js.id = id;
          js.src = "https://platform.twitter.com/widgets.js";
          fjs.parentNode.insertBefore(js, fjs);
          return window.twttr || (t = {
            _e: [],
            ready: function (f) {
              t._e.push(f)
            }
          });
        }(document, "script", "twitter-wjs"));

        if (typeof callback == 'function') {
          callback();
        }
      },
      start: function (cfg, callback) {
        var _this = this;
        cfg = cfg || {};

        var iWidth = 650,
          iHeight = 430,
          iTop = (window.screen.availHeight - 30 - iHeight) / 2,
          iLeft = (window.screen.availWidth - 10 - iWidth) / 2,
          url = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(cfg.title) + '&url=' + encodeURIComponent(cfg.link);
        window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);

        if (typeof callback == 'function') {
          callback();
        }
      }
    },
    pinterest: {
      start: function (cfg, callback) {
        var _this = this;
        cfg = cfg || {};

        var iWidth = 800,
          iHeight = 650,
          iTop = (window.screen.availHeight - 30 - iHeight) / 2,
          iLeft = (window.screen.availWidth - 10 - iWidth) / 2,
          url = 'https://www.pinterest.com/pin/create/button/?url=' + encodeURIComponent(cfg.link) + '&media=' + encodeURIComponent(cfg.image) + '&description=' + encodeURIComponent(cfg.description);
        window.open(url, '', 'width=' + iWidth + ',height=' + iHeight + ',top=' + iTop + ',left=' + iLeft);
        if (typeof callback == 'function') {
          callback();
        }
      }
    }
  };

  // Community list item hover
  $(".js_buyer_show .buyer-show-item").mouseenter(function () {
    $(this).find(".view-it").stop().fadeIn();
    var imgUrl = $(this).find('.view-it').find('.icon-camer').data('original');
    $(this).find('.view-it').find('.icon-camer').attr("src", imgUrl);
  }).mouseleave(function () {
    $(this).find(".view-it").stop().fadeOut();
  });

  // Global variable index
  var index = 1;

  // Trigger
  $('body').on('click', '[data-gid=U000063_sku] .js_buyer_show .buyer-show-item', function () {
    // Current trigger tag
    $(this).addClass('current-show-item');
    var i = $(this).data('index');
    var data = $('[data-gid=U000063_sku] #goodsSKUContainer li:eq(' + (i - 1) + ')').text();
    data = JSON.parse(data);
    data.ins_img = $('.js_buyer_show .buyer-show-item[data-index='+i+']').children('img').attr('src');
    dialogAlert(data);
    if (window.GLOBAL && window.GLOBAL.currency) {
      window.GLOBAL.currency.change_html();
    }
    getCurrentIndex();
  });

  /**
   * Get current show item index
   */
  function getCurrentIndex() {
    var i;
    $('.js_buyer_show .buyer-show-item').each(function () {
      if ($(this).hasClass('current-show-item')) {
        i = $(this).data('index');
      }
    });
    index = i;
  }

  // Switch:next
  $('body').on('click', '.js_viewIt_alert .next', function () {
    if (index == 7) {
      return false;
    }
    index++;
    getDialogData();
  });

  // Switch:prev
  $('body').on('click', '.js_viewIt_alert .prev', function () {
    if (index == 1) {
      return false;
    }
    index--;
    getDialogData();
  });

  /**
   * Get data when carousel
   */
  function getDialogData() {
    $('.js_viewIt_alert').hide();

    var data = $('#goodsSKUContainer li:eq(' + (index - 1) + ')').text();
    data = JSON.parse(data);
    data.ins_img = $('.js_buyer_show .buyer-show-item[data-index='+index+']').children('img').attr('src');
    if (data) {
      setData(data);
    }

    // show content
    setTimeout(function () {
      $('.js_viewIt_alert').show();
    }, 500);
  }

  function dialogAlert (data) {
    GEShopSiteCommon.dialog.content($('#view-it-dialog-container').html(), 830, 570, function () {
      setData(data);
    })

    // show content
    setTimeout(function () {
      $('.js_viewIt_alert').show();
    }, 500);

    // close
    $('body').on('click', '.js_viewIt_alert .js_close_btn', function () {
      GEShopSiteCommon.dialog.unblock();
      $('.js_buyer_show .buyer-show-item').removeClass('current-show-item');
    });
  }

  /**
   * set data
   * @param {Object}
   */
  function setData(data) {
    var db;
    if (data) {
      db = data;
    } else {
      db = {};
    }
    $(".js_viewIt_alert .js_ins_img").attr("src", db.ins_img);
    $(".js_viewIt_alert .js_goods_img").attr("src", db.goods_img);
    $(".js_viewIt_alert .js_goods_title").html(db.goods_title);
    $(".js_viewIt_alert .js_new_price").attr("data-orgp", db.shop_price);
    $(".js_viewIt_alert .js_old_price").attr("data-orgp", db.market_price);
    $(".js_viewIt_alert .js_new_price").text("$" + db.shop_price);
    $(".js_viewIt_alert .js_old_price").text("$" + db.market_price);
    $(".js_viewIt_alert .js_goods_link").attr("href", db.url_title);

  }

  window.share.fackbook.init();
  window.share.twitter.init();

  $(document).on("click", ".js_share_btn", function() {
    var type = $(this).data("share-type");
    var shareParam = {
      name: $(".js_viewIt_alert .js_goods_title").html(),
      picture: $(".js_viewIt_alert .js_ins_img").attr("src"),
      link: $(".js_viewIt_alert .js_goods_link").attr("href"),
      title: $(".js_viewIt_alert .js_goods_title").html(),
      description: $(".js_viewIt_alert .js_goods_title").html()
    }
    switch (type) {
      case 'facebook':
        window.share.fackbook.start(shareParam);
        break;
      case 'twitter':
        window.share.twitter.start(shareParam);
        break;
      case 'pinterest':
        window.share.pinterest.start(shareParam);
        break;
    }
  });
})();