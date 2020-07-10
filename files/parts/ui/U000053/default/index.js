if (!gsGlobal) {
	var gsGlobal = function (my) {
		var $gsCountTarget = $('.gs-count-down .gs_component_countDown');
		my.initCountDown = function () {
			$gsCountTarget = $('.gs-count-down .gs_component_countDown');
			$gsCountTarget.each(function () {
				var $self = $(this),
					leftTime = 0,
					status = 0,	// ['0未开始', '1已开始', '2已结束']
					serverTime = new Date().getTime() || $self.prev('input[name=serverTime]').val(),
					dataStartTime = $self.prev('input[name=serverTime]').attr('data-start-time'),
					dataEndTime = $self.prev('input[name=serverTime]').attr('data-end-time'),
					dataTextArr = ($self.prev('input[name=serverTime]').attr('data-textobj')).split(',');

				serverTime = Math.round(serverTime / 1000);
				dataStartTime = Math.round(dataStartTime / 1000);
				dataEndTime = Math.round(dataEndTime / 1000);

				if (dataStartTime > serverTime) {
					leftTime = dataStartTime - serverTime;
				} else if (dataStartTime <= serverTime && dataEndTime > serverTime) {
					status = 1;
					leftTime = dataEndTime - serverTime;
				} else if (dataEndTime < serverTime) {
					status = 2;
					leftTime = 0;
				}
				// leftTime = Math.round(leftTime / 1000)
				$self.data({ 'leftTime': leftTime, 'status': status })
				$self.prev().prev().text(dataTextArr[status] + ':')

				/* 服务器倒计时时间模拟 */
				/* 				if (serverTimeInterval) clearTimeout(serverTimeInterval)
								var serverTimeInterval = setInterval(function () {
									serverTime = serverTime + 1;
									$self.prev('input[name=serverTime]').val(serverTime * 1000)
								}, 1000) */
			})

		}
		/* 倒计时组件 */
		my.compoentCountDown = function () {
			var _this = this;
			$gsCountTarget.each(function () {
				var $self = $(this)
				var leftTime = parseInt($self.data('leftTime'))
				var dataStatus = parseInt($self.data('status'))
				var seconds, minutes, hours, days, CDay, CHour, CMinute, CSecond
				if (!isNaN(leftTime) && leftTime >= 0) {
					seconds = leftTime
					minutes = Math.floor(seconds / 60)
					hours = Math.floor(minutes / 60)
					days = Math.floor(hours / 24)
					CDay = days
					CHour = hours % 24
					CMinute = minutes % 60
					CSecond = Math.floor(seconds % 60)

					CDay = CDay < 10 ? '0' + CDay : CDay
					CHour = CHour < 10 ? '0' + CHour : CHour
					CMinute = CMinute < 10 ? '0' + CMinute : CMinute
					CSecond = CSecond < 10 ? '0' + CSecond : CSecond

					$self.data('left-time', leftTime - 1)
					$self.html('<span><em>' + CDay + '</em>:<em>' + CHour + '</em>:<em>' + CMinute + '</em>:<em>' + CSecond + '</em></span>')
					// $self.html('<span><strong>' + CDay + 'DAY(S)</strong>' + CHour + ':' + CMinute + ':' + CSecond + '</span>')
				} else {
					$gsCountTarget = $gsCountTarget.not($self)
					// $self.html('<span><strong>00 DAY(S) </strong>00:00:00</span>')
					if (dataStatus !== 2) {
						gsGlobal.initCountDown();
					} else {
						$self.html('<span><em>00</em>:<em>00</em>:<em>00</em>:<em>00</em></span>')
					}
				}
			})
			if (0 !== $gsCountTarget.length) {
				setTimeout(gsGlobal.compoentCountDown, 1000)
			}
		}

		return my
	}(gsGlobal || {})
	gsGlobal.initCountDown();
	gsGlobal.compoentCountDown();
}


