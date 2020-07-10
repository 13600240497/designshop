setTimeout(function () {
	function checkTime (stime, etime) {
		//开始时间
		var arrs = stime.split("-");
		// var startTime = new Date(arrs[0], arrs[1], arrs[2]);

		// var startTime = new Date(stime);
		// var startTimes = startTime.getTime();
		//结束时间
		var arre = etime.split("-");
		// var endTime = new Date(arre[0], arre[1], arre[2]);

		// var endTime = new Date(etime);
		// var endTimes = endTime.getTime();

		var startTimes = Number(stime);
		var endTimes = Number(etime)
		//当前时间
		// var thisDate = new Date();
		// var thisDates = thisDate.getFullYear() + "-0" + (thisDate.getMonth() + 1) + "-" + thisDate.getDate();
		// var arrn = thisDates.split("-");
		// var nowTime = new Date(arrn[0], arrn[1], arrn[2]);
		var nowTimes = new Date().getTime();
		if (nowTimes < startTimes || nowTimes > endTimes) {
			return false;
		}
		return true;
	}
	var timeDateLenth = $(".js_geshopTimeZoneBanner").length;
	// .text().trim().split('#')

	for (let i = 0; i < timeDateLenth; i++) {
		var timeDate = $(".js_geshopTimeZoneBanner").eq(i).text().trim().split('#');
		var imgsrcList = [];
		var imgIndex = false;

		timeDate.forEach(function (item, index) {
			if (index === 0) {
				var itemInfo = item.trim().split('&');//
				imgsrcList.push(itemInfo[1]);
			} else {
				if (item != "") {
					var itemInfo = item.trim().split('&');//
					imgsrcList.push(itemInfo[1]);
					var temItem = itemInfo[0].trim().split(' - ');//
					var timebool = checkTime(temItem[0], temItem[1]);//注意：日期用“-”分隔
					if (timebool == true) {
						imgIndex = index;
					}
				}
			}

		})
		if (timeDate.toString() != "") {
			if (imgIndex !== false) {
				$(".js_geshopTimeZoneBanner").eq(i).prev(".initImgSrc").attr('src', imgsrcList[imgIndex]);
				// $(".initImgSrc").attr('src', imgsrcList[imgIndex])
			} else {
				if (imgsrcList[0] != "") {
					$(".js_geshopTimeZoneBanner").eq(i).prev(".initImgSrc").attr('src', imgsrcList[0]);
					// $(".initImgSrc").attr('src', imgsrcList[0])
				}
			}
		}
	}




}, 200);

