$(function() {

    /* 判断时间是否重叠 */
    function checkReCover(timeRange, currentDom) {
        var startTime = new Date(timeRange.split(" - ")[0]).getTime();
        var endTime = new Date(timeRange.split(" - ")[1]).getTime();
        var response = false;
        var checkTargets = $('.down-timestamp');
            checkTargets.each(function(i, el) {
                if ($(this).attr('name') != $(currentDom).attr('name')) {
                    var checkRange = $(this).val();
                    var checkStartTime = new Date(checkRange.split(" - ")[0]).getTime();
                    var checkEndTime = new Date(checkRange.split(" - ")[1]).getTime();
                    if ((startTime>=checkStartTime && startTime<=checkEndTime) || (endTime>=checkStartTime&&endTime<=checkEndTime)) {
                        response = true;
                    }
                }
            });
        return response;
    };
        
    /* min,max close*/
    function downDateInit (target) {
        var laydate = layui.laydate;
        laydate.render({
            elem: target
            , type: 'datetime'
            , range: true
            , done: function (value, date, endDate) {
                if (checkReCover(value, target) == true) {
                    layer.msg('时间重叠');
                    setTimeout(function(){
                        $(target).val('');
                    }, 100);
                }
            }
        })
    };

    var Box = $('#tab-for-dataSet2');

    /* 表单配置项的模板 */ 
    var formTemplate = $('#tab-for-dataSet2').find('[data-template]').clone().html();

    /* */
    var tab1 = new AuToTab().init({
        el: '#tab-for-dataSet2',
        max: 6,
        showCloseButton: true,
        added: function(el, id) {
            var template = formTemplate.replace(/{replaceKey}/g, id);
            $(el).append(template);
            downDateInit('[name=downDateRange'+id+"]");
        }
    });
    
    tab1.title.children().each(function(i, el) {
        var randomId = $(el).attr('data-key');
        downDateInit('[name=downDateRange'+randomId+"]");
    });

    /* 提交事件 */
    $('#custom_submit').on('click', function () {

        function _getValueByName(name, randomId, panel) {
            return Box.find('[name=' + name + randomId + ']').val();
        }

        var dataSet = [];
        Box.find('.layui-tab-title > li').each(function(index, el) {
            var randomId = $(el).attr('data-key');
            var timeRange = _getValueByName('downDateRange', randomId).split(" - ");
            var dataRow = {
                key: parseInt(Math.random()*10000)+10000,
                startTime: new Date(timeRange[0]).getTime(),
                startTimeFormate:  timeRange[0],
                endTime: new Date(timeRange[1]).getTime(),
                endTimeFormate:  timeRange[1],
                bgColor: _getValueByName('bgColor', randomId),
                textColor: _getValueByName('textColor', randomId),
                gridBgColor: _getValueByName('gridBgColor', randomId),
                gridTextColor: _getValueByName('gridTextColor', randomId),
                text1: _getValueByName('text1', randomId),
                text2: _getValueByName('text2', randomId),
                text3: _getValueByName('text3', randomId),
            };
            dataSet.push(dataRow);
        });
        $('[name=dataSet]').val(JSON.stringify(dataSet));
        $(this).next().click();
    });

})



