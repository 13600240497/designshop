;$(function () {
    var rating;
	if (typeof GBRating === 'undefined') {
		/**
		 * array contain polyfill
		 */
		if (!Array.prototype.contain) {
			Array.prototype.contain = function (val) {
				for (var i = 0; i < this.length; i++) {
					if (this[i] == val) {
						return true;
					}
				}
				return false;
			};
		}

		/**
		 * classList 兼容IE
		 */
		if (!("classList" in document.documentElement)) {
			Object.defineProperty(HTMLElement.prototype, 'classList', {
				get: function () {
					var self = this;

					function update (fn) {
						return function (value) {
							var classes = self.className.split(/\s+/g),
								index = classes.indexOf(value);

							fn(classes, index, value);
							self.className = classes.join(" ");
						};
					}

					return {
						add: update(function (classes, index, value) {
							if (!~index) classes.push(value);
						}),

						remove: update(function (classes, index) {
							if (~index) classes.splice(index, 1);
						}),

						toggle: update(function (classes, index, value) {
							if (~index)
								classes.splice(index, 1);
							else
								classes.push(value);
						}),

						contains: function (value) {
							return !!~self.className.split(/\s+/g).indexOf(value);
						},

						item: function (i) {
							return self.className.split(/\s+/g)[i] || null;
						}
					};
				}
			});
		}

		/**
		 * gb rating 评分
		 * @param selector
		 * @constructor
		 */
		function GBRating (selector) {
			try {
				if ($('.gs-rating').length > 0) {
					var arr = [];
					$('.gs-rating').each(function (index, item) {
						arr.push(item);
					});
					this.elements = arr;
					arr.forEach(function (item) {
						if (item && item.nodeType === 1) {
							new GBRaging_Single(item);
						}
					});
				}

			} catch (e) {
				console.error('评分初始化失败！');
			}
		}

		function GBRaging_Single (element) {
			this.init = function () {
				this.element.style.display = 'none';
				var model = this.getElementOfModel();
				this.element.parentNode.insertBefore(model, this.element.nextSibling);

			};

			this.getElementOfModel = function () {
				var dom = document.createElement('span');
				dom.classList.add('rating_model');
				dom.innerHTML = this.getHtmlOfModel();
				return dom;
			};

			this.getHtmlOfModel = function () {
				var isTrue = (Math.floor(this.value * 100)) / 100 > Math.floor(this.value);
				var decimal = isTrue ? (Math.floor(this.value * 100)) / 100 + 1 : (Math.floor(this.value * 100)) / 100;
				var index = Math.floor(this.value);
				var html = '<i class="rating_before ' + (index === 0 ? this.shapeClass : '') + '" data-index="0"></i>';
				for (var i = 1; i <= 5; i ++) {
					html += '<i class="gs-iconfont iconfont-start ' + (decimal - i >= 1 || decimal - i == 0 ? "gs-icon-star" : decimal - i > 0 && decimal - i < 1 ? 'gs-icon-star-half' : 'gs-icon-star1') + '" data-index="' + i + '"></i>';
				}
				return html;
			};

			if (!element.classList.contains('rating-initialized')) {
				element.classList.add('rating-initialized');
				this.element = element;
				this.shapeClass = 'rating_full';
				this.value = Number(element.value || element.dataset.value) || 0;
				this.value = this.value >= 0 && this.value <= 5 ? this.value : 5;
				this.init();
			}
		}
        rating = new GBRating();
	}else{
        rating = new GBRating();
    }


	$('[data-gid=U000160_model2] .view-more-btn').on('click',function(){
		var $this = $(this);
		var $ul = $(this).parent().siblings('.gb-list-default').eq(0);
		var shownum = $ul.data('shownum');
		var Lis = $ul.children().each(function(index,item){
			if(index>shownum-1){
				if( index ==$ul.children().length-1){
					if(!$(item).hasClass('isHide')){ // 收缩
						$('html').scrollTop($ul.data('scrolltop'))
						$this.children('span').text($this.data('moretext'))
						$this.children('i').removeClass('gs-iconfont gs-icon-up icon-arrow_up').addClass('gs-iconfont gs-icon-down1 icon-arrow_down')
					}else{ // 展开
						$ul.data('scrolltop',$('html').scrollTop())
						$this.children('span').text($this.data('lesstext'))
						$this.children('i').removeClass('gs-iconfont gs-icon-down1 icon-arrow_down').addClass('gs-iconfont gs-icon-up icon-arrow_up')
					}
				}
				$(item).toggleClass('isHide')
			}
		})
	})
});










