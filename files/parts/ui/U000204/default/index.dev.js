;$(function() {
  var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

  $LAB
		.script(staticDomain + "/resources/javascripts/clipboard.min.js").wait(function () {
      var clipboard = new ClipboardJS('.js_copyBtn');
      clipboard.on('success', function(e) {
        
        var _this = $(e.trigger);
        _this.text(_this.data('copyedtext'));
        _this.css({
          color: _this.data('visitedtextcolor'),
          backgroundColor: _this.data('visitedbgcolor')
        })
        e.clearSelection();
      });
		});
});

