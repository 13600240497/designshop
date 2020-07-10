


	(function() {//\u6dfb\u52a0addthis\u5206\u4eab\u7ec4\u4ef6
		function addLoadEvent (func) {
			var oldonload = window.onload;

			if (typeof window.onload != 'function') {
				window.onload = func;
			} else {
				window.onload = function() {
					oldonload();
					func();
				}
			}
		}

		addLoadEvent(function() {
			var script = document.createElement('script');
			if(GESHOP_SITECODE === 'rg-pc'){
				script.src = '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-54c2151b31fb2710';
			}else if (GESHOP_SITECODE === 'zf-pc'){
				script.src = '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5a38671bb83b79fe'
			}

			document.getElementsByTagName('head')[0].appendChild(script);
		});
	})();
