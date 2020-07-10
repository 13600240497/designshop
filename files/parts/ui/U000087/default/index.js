
(function(){
  // $('.js_copyBtn').click(function () {
  //   var _this = $(this)
  //   var value = _this.prev('input').val();
  //   var inputElement = document.createElement('input');
  //   document.body.appendChild(inputElement);
  //   inputElement.setAttribute('value', value);
  //   inputElement.setAttribute('readonly', 'readonly');
  //   inputElement.select();
  //   inputElement.focus();
  //   inputElement.setSelectionRange(0, inputElement.value.length);
  //   if (document.execCommand('copy')) {
  //     document.execCommand('copy');
  //     document.body.removeChild(inputElement);
  //     _this.text(_this.data('visitedtext'));
  //     _this.css({
  //       color: _this.data('visitedtextcolor'),
  //       backgroundColor: _this.data('visitedbgcolor')
  //     })
  //   }
  // });

 var staticDomain = $('[data-static-domain]:eq(0)').attr('data-static-domain') ? $('[data-static-domain]:eq(0)').attr('data-static-domain') : "";

  $LAB
		.script(staticDomain + "/resources/javascripts/clipboard.min.js").wait(function () {
      var clipboard = new ClipboardJS('.js_copyBtn');
      clipboard.on('success', function(e) {
        
        var _this = $(e.trigger);
        _this.text(_this.data('visitedtext'));
        _this.css({
          color: _this.data('visitedtextcolor'),
          backgroundColor: _this.data('visitedbgcolor')
        })
        e.clearSelection();
      });
		});

})();