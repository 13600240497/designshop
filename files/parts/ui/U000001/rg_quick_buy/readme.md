# 组件信息
组件名：商品列表 <br/>
模版名：RG快速购买 <br/>
文件夹：rg_quick_buy <br/>
创建人：邹立峰 <br/>
创建版本：v1.8.7 <br/>
[蓝湖地址](https://lanhuapp.com/web/#/item/project/board?pid=9d81fd77-34e1-40f9-ac94-d0e187fff514)      [原型地址](https://fnlf9g.axshare.com/#g=1&p=%E9%9C%80%E6%B1%82%E8%AF%B4%E6%98%8E_26)

 
   
# 修改记录：
   
> ### v1.8.9, 2019-05-15, 邹立峰
>>#### 添加分页
```js
scrollCalBackFn () {
    const _self = this;
    $(window).on('scroll', function () {
        const scrollTop = $(this).scrollTop();
        const gsTabOffset = _self.$box.find('.geshop-containr').offset();
        if (scrollTop > gsTabOffset.top + _self.$box.find('.geshop-containr').height() / 3) {
            if (_self.pageCount > _self.currentPage) {
                if (_self.ajx) {
                    _self.ajx = false;
                    _self.currentPage++;
                    _self.getSameList(_self.goodList);
                }
            }
            console.log(scrollTop);
        } else {

        }
    });
}
 ```          
 ---
 >>#### 处理懒加载JQ > 1.9 安全性转换
 > getSameList()
  ```js
let $div = $($.parseHTML(html, document, true));
  ```  


