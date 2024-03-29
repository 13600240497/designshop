# 前端相关

### 关键目录：
1. ```htdocs``` 是存放后台系统的前端代码目录
2. ```files/parts```是存放前台页面的组件
3. ```node-server```是存放编译前台页面组件的目录

### 后台系统页面相关
```
# 本地开发模式
cd /htdocs/
npm run dev

# 生产模式编译
cd /htdpcs/
npm run build
```

### 前台页面组件相关
```
# vue组件开发命令
cd /node-server/
npm run dev:client

# 生产模式编译
cd /node-server/
npm run build:client
```


[1.0.0][2018-05-18] 大事件，大事件，版本发布啦！ 


[1.0.1][2018-05-24]简介:new1213
一、对商品的管理
包括商品列表的排序调整，商品的替换，以及库存的管理
二、新增组件
Banner图文分离组件的增加
三、功能新增
1、新增【同步英文样式】操作
2、新增【页面模板管理】、【模板管理】的【删除】操作
3、新增活动URL的自定义填写
四、功能的优化
1、活动时间做表单验证
2、【组件管理】编辑权限的扩展
3、RW活动头部达到与网站保持到一致
4、组件复制操作的优化，实现跨组件复制
五、流程优化
装修界面【发布】操作将静态页面的生成、子页面的上线合为一步，提高用户的体验感



[1.0.2][2018-6-06]简介:
一、功能优化
1、优化【专题与活动管理】中【查看】隐藏项太多的问题
2、优化M端折扣标转化导致字体变小和显示位置问题
3、优化【新增活动】中简介内容填写中用户体验感问题
4、优化组件【样式设置】体验感不强的问题
二、功能修改
1、修改权限管理：角色管理、部门管理、用户管理中的流程
2、修改菜单管理中功能的权限使用控制
三、为接入RG站做准备
1、RG站头部、尾部数据接口对接
2、RG站商品详情数据接口对接


[1.1.0][2018-6-21]简介:
一 功能修改
1 存为“页面模板”时，分为存为私有和公有的页面模板
2 活动权限管理,创建人可以切换活动是否可以操作编辑
3 添加子页面时，增加一个是否五分钟刷新页面的按钮，默认不勾选，增加一键更新站点头尾
4 倒计时（倒计时组件、倒计时banner模版）
5 M端活动增加一键转APP的操作
6 组件配置拆分为常用配置和非常用配置
二 功能优化
1 渲染模板时把模板中的内联样式全部合并到头部中
2 子页面上线或发布时增加对商品SKU和头尾部的校验
3 涉及到用时间插件的，对开始时间和结束时间做默认值改变

[1.1.1][2018-6-28]简介:
一 功能修改
1 对头部和尾部的方案做调整
2 对装修的页面做草稿处理，【自动刷新】或【一键刷新头尾部】的操作不针对“草稿”状态的页面
3 “广告图+商品轮播组合“的组件
4 M端和PC端各新增一个“虚拟”站点，用来做日常的测试和用户体验

二 功能优化
1 专题与活动管理的界面布局优化（目前按钮太多）
2 【存为模板】操作，保存的模板在相同端各个站点下都有


[1.2.0][2018-7-18]简介:
活动页
1、增加【商品数据管理】模块，包括对商品数据、标题栏内容进行统一的新增、修改以及对整体页面sku的查看
2、增加PC一键转M版功能
3、新增两个组件：商品列表组件（信息显示左对齐）、组合商品循环组件
首页
1、新增首页管理、页面模板管理、组件管理、组件模板管理、组件分类管理来实现对首页的装修，实现对个语种的首页进行装修
2、针对首页提供如下组件：热图组件、轮播图组件、商品轮播组件（单品和四个循环）、四个商品展示组件、纯文字跑马灯组件、博客组件、社区组件、标题栏组件、100%布局
3、针对首页的装修界面、组件的样式配置采用新的交互方式
4、装修界面可以切换页面面板
