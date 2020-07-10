# 重点

### RG支持多个优惠券ID配置，接口（站点）根据需求返回对应的1个优惠券信息
需求地址：https://ho1ibt.axshare.com/  
时间：2020-02-06

### 3. 表单配置差异化
rg: 支持多个优惠券ID，表单配置抽离 form-couponId.twig  
zf: 仅支持单个ID

### 2. JS文件拆分
rg/index.js
zf/index.js

### 1. 优惠券详情的接口差异
rg: 调用的是 GESHOP_INTERFACE.couponlist.url  
ZF: 调用的是 GESHOP_INTERFACE.coupondetail.url
