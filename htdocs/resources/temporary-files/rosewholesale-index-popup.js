$(function() {
  var subscribe_email = {
      checkCookie: function() {
          if ($.cookie("subscribe_email") == undefined) {
              $.cookie('subscribe_email', true, {
                  expires: 7,
                  path: '/',
                  domain: COOKIESDIAMON
              });
              return false;
          } else {
              return true;
          }
      },
      showSubscribeDialog: function() {
        var index = GLOBAL.PopObj.openPop({
            shade: [1, 'rgba(28, 28, 28, 0.6)', true],
            shadeClose: false,
            area: ['auto', 'auto'],
            page: { dom: '.js_subscribe_dialog' },
            offset: [$(window).height() / 2 - 300 + "px", '50%'],
            border: [0],
            closeBtn: false
        });

        $(".xubox_main").css({ overflow: 'visible' });

        $(document).on('click', '.js_subscribe_dialog .js_close_btn', function() {
          $('#xubox_layer' + index + ', #xubox_shade' + index).remove();
        });

          var sub_img = $(".js_subscribe_dialog").find(".js_sub_banner");
          sub_img.attr("src", sub_img.data("lazy-img"));
      },
      subscribe_request: function(email, callback) {
          var encodestr = String.fromCharCode(email.charCodeAt(0) + email.length);
          var source = 5;
          for (var i = 1; i < email.length; i++) {
              encodestr += String.fromCharCode(email.charCodeAt(i) + email.charCodeAt(i - 1));
          }
          encodestr = encodeURIComponent(encodestr);
          $.ajax({
              url: "https://www.rosewholesale.com/fun/ajax.php?act=check_email",
              type: "POST",
              dataType: "json",
              data: {
                  email: email,
                  lang: $("html").attr("lang")
              },
              success: function(data) {
                  if (data.sta == 2) {
                      location.href = DOMAIN_USER + JS_LANG + "m-promotion-active-151.html?source=subscribe_email";
                  } else if (data.sta == 3) {
                      window.location.href = DOMAIN + 'email-sign-up.html?email=' + encodestr + '&source=' + source;
                  } else if (data.sta == 4) {
                      $(".js_err_msg").html(data.msg);
                  } else if (data.sta == 5) {
                      $(".js_err_msg").html(data.msg);
                  } else {
                      $(".js_err_msg").html(data.msg);
                  }
                  callback(data);
              },
              error: function(err) {
                  callback(err);
              }
          });
      },
      bindEvent: function() {
          var _self = this;
          $("#js_subscribe_submit").on("click", function() {
              var reg = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
              var email = $(".js_subscribe_email").val().trim();
              if (email == "") {
                  $(".js_err_msg").html(jsLg.formMsg.email_require_msg);
                  return;
              } else if (reg.test(email) == false) {
                  $(".js_err_msg").html(jsLg.formMsg.email_require_msg);
                  return;
              }
              if ($(this).hasClass("loading") == true) return;
              $(this).addClass("loading");
              _self.subscribe_request(email, function() {
                  $("#js_subscribe_submit").removeClass('loading');
              });

          });
      },
      init: function() {
          if (this.checkCookie() == false) {
              this.showSubscribeDialog();
              this.bindEvent();
          }
      }
  };

  subscribe_email.init();
});