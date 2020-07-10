;$(function () {
	if (!GBRating) {
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
				var decimal = this.value % 1;
				var index = Math.floor(this.value);

				if (!this.editable) {
					if (decimal >= 0.25) index += 1;
					if (decimal >= 0.25 && decimal < 0.75) this.shapeClass = 'gs-icon-star-half';
				}

				//rating_star> gs-icon-star
				var html = '<i class="rating_before ' + (index === 0 ? this.shapeClass : '') + '" data-index="0"></i>';
				for (var i = 1; i <= 5; i += 1) {
					html += '<i class="gs-iconfont gs-icon-star ' + (index === i ? this.shapeClass : '') + '" data-index="' + i + '"></i>';
				}

				return html;
			};


			if (!element.classList.contains('rating-initialized')) {
				element.classList.add('rating-initialized');
				this.element = element;
				this.shapeClass = 'rating_full';
				this.value = Number(element.value || element.dataset.value) || 0;
				this.value = this.value >= 0 && this.value <= 5 ? this.value : 0;
				this.init();
			}
		}


	}
	var rating = new GBRating();
});









