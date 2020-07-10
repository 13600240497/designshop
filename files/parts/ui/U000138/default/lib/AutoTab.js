function AuToTab() {
    this.box = null;
    this.title = null;
    this.content = null;
    this.addCallback = null;
    this.tabClass = 'layui-this';
    this.contentClass = 'layui-show';
    this.current = 0;
    this.titleText = '';
    this.max = 99;
    this.dom = {
        add: null
    };
    this.showCloseButton = false;

    this.init = function(params) {
        this.box = $(params.el);
        this.title = this.box.find('>ul.layui-tab-title');
        this.content = this.box.find('>div.layui-tab-content');
        this.addCallback = params.added;
        this.titleText = params.titleText || '时段';
        this.dom.add = this.box.find('.li-add');
        this.max = params.max || 10;
        this.showCloseButton = params.showCloseButton || false;
        this.setTab();
        this.bindEvents();
        return this;
    };
};

AuToTab.prototype.setTab = function(index) {
    if (index!=null && index >= 0) this.current = index;
    this.title.children().removeClass(this.tabClass).eq(this.current).addClass(this.tabClass);
    this.content.children().removeClass(this.contentClass).eq(this.current).addClass(this.contentClass);
    this.resetPosition();
};
AuToTab.prototype.bindEvents = function() {
    var that = this;
    /* 切换 */
    this.box.on('click', 'li', function(event) {
        event.stopPropagation();
        var index = $(this).index();
        that.setTab(index);
    });
    this.dom.add.bind('click', function() {
        var len = that.title.children().length;
        if (len>that.max) {
            layer.msg('最多创建'+(that.max+1)+'个时段');
            return false;
        };
        var randomId = parseInt(Math.random()*10000)+10000;
        that.createTab(len, randomId);
        that.createPanel(len, randomId);
        that.setTab(len);
    });
    this.box.on('click', '.layui-icon-close', function() {
        var key = $(this).attr('data-key');
        that.remove(key);
    });
};

AuToTab.prototype.createTab = function(index, randomId) {
    var that = this;
    var newLi = $('<li>').attr('data-key', randomId);
    var span = $('<span>').addClass('tab-item').html(this.titleText+index);
    var i = $('<i>').addClass('layui-icon layui-icon-close').attr('data-key', randomId);
    newLi.append(span);
    this.showCloseButton == true && newLi.append(i);
    this.title.append(newLi);
    /* rename all the tab by sort */
    this.title.children().each(function(i, el){
        $(el).find('span').html(that.titleText+i);
    });
};

AuToTab.prototype.createPanel = function(index, randomId) {
    var item = $('<div>').addClass('layui-tab-item').addClass(this.contentClass);
    this.content.append(item);
    this.addCallback(item, randomId);
};

AuToTab.prototype.remove = function(randomId) {
    var key = '[data-key='+randomId+']';
    this.title.find(key).remove();
    this.content.find(key).remove();
    this.setTab(0);
};

AuToTab.prototype.resetPosition = function() {
    var totalWidth = 280;
    var li = this.title.children();
    var eachMargin = 0;
    if (li.length * 80 > totalWidth) {
        var outWidth = totalWidth - li.length * 80;
        eachMargin = parseInt(outWidth / (li.length - 1));
        li.css('margin-left', eachMargin+'px');
    }
    li.eq(this.current).next().css('margin-left', '-1px');
};