/*
Navicat MySQL Data Transfer

Source Server         : 预发布环境
Source Server Version : 50724
Source Host           : 169.60.238.165:3306
Source Database       : geshop_db

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-07-09 18:07:55
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for activity
-- ----------------------------
DROP TABLE IF EXISTS `activity`;
CREATE TABLE `activity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `group_id` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动分组ID|activity_group.id',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `lang` varchar(255) NOT NULL DEFAULT '' COMMENT '活动所属语言，多个语系以英文逗号分隔，如zh,en,de',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '活动名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '活动简介',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态|1待上线,2已上线,3待下线,4已下线',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否，1是',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否加锁 0：否 1：是|夏人杰|20180609',
  `is_frequently` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用活动(0 - 不是； 1 - 是)|田海深|20190904',
  `mold` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动类型 1 普通活动 2 活动推广',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_site` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2416 DEFAULT CHARSET=utf8 COMMENT='活动表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for activity_data
-- ----------------------------
DROP TABLE IF EXISTS `activity_data`;
CREATE TABLE `activity_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '数据记录ID',
  `buyer_identity` tinyint(4) NOT NULL DEFAULT '0' COMMENT '新老客标识:0 , 1 , 2 [说明:2是整体的数据,1 代表新客,0 代表老客]',
  `page_name` varchar(20) NOT NULL DEFAULT '' COMMENT '页面名称：专题页，促销页，列表页，商详页，搜索页等',
  `page_type` varchar(20) NOT NULL DEFAULT '' COMMENT '页面类型：同上,如 b03',
  `sub_id` int(11) NOT NULL DEFAULT '0' COMMENT '专题ID等',
  `sub_ie_pv` int(11) NOT NULL DEFAULT '0' COMMENT '专题曝光PV',
  `sub_ic_pv` int(11) NOT NULL DEFAULT '0' COMMENT '专题点击PV',
  `sub_uv` int(11) NOT NULL DEFAULT '0' COMMENT '专题访问人数',
  `sub_cl_rate` double(16,2) NOT NULL DEFAULT '0.00' COMMENT '专题点击率',
  `sub_pur_numb` int(11) NOT NULL DEFAULT '0' COMMENT '专题购买用户数',
  `sub_pay_amount` double(16,2) NOT NULL DEFAULT '0.00' COMMENT '专题购买金额',
  `location` int(11) NOT NULL DEFAULT '0' COMMENT '位置',
  `module_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID',
  `module_ie_pv` int(11) NOT NULL DEFAULT '0' COMMENT '组件曝光PV',
  `module_ic_pv` int(11) NOT NULL DEFAULT '0' COMMENT '组件点击PV',
  `module_pur_numb` int(11) NOT NULL DEFAULT '0' COMMENT '组件购买用户数',
  `module_pay_amount` double(16,2) NOT NULL DEFAULT '0.00' COMMENT '组件购买金额',
  `pit_id` int(11) NOT NULL DEFAULT '0' COMMENT '坑位',
  `pit_ie_pv` int(11) NOT NULL DEFAULT '0' COMMENT '坑位曝光PV',
  `pit_ic_pv` int(11) NOT NULL DEFAULT '0' COMMENT '坑位点击PV',
  `pit_cl_rate` double(16,2) NOT NULL DEFAULT '0.00' COMMENT '坑位点击率',
  `pit_pur_numb` int(11) NOT NULL DEFAULT '0' COMMENT '坑位购买用户数',
  `pit_pay_amount` double(16,2) NOT NULL DEFAULT '0.00' COMMENT '坑位购买金额',
  `platform` varchar(20) NOT NULL DEFAULT '' COMMENT '终端维度：pc,m,ios,android,others,all',
  `update_time` date NOT NULL DEFAULT '1970-01-01' COMMENT '日期(YYYY-MM-DD)',
  `site` varchar(20) NOT NULL DEFAULT '' COMMENT '网站',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_site_platform_buyer_identity` (`buyer_identity`,`platform`,`site`) USING BTREE,
  KEY `idx_sub_id` (`sub_id`) USING BTREE,
  KEY `idx_module_id` (`module_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=61827775 DEFAULT CHARSET=utf8 COMMENT='专题页流量数据统计表';

-- ----------------------------
-- Table structure for activity_group
-- ----------------------------
DROP TABLE IF EXISTS `activity_group`;
CREATE TABLE `activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` varchar(200) NOT NULL COMMENT '支持语言列表',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=674 DEFAULT CHARSET=utf8 COMMENT='活动分组|田海深|20180913';

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员id|自增',
  `department_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属部门id|department.id',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `realname` varchar(20) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `is_leader` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否负责人|0否1是',
  `is_super` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否超级管理员|0否，1是',
  `user_no` varchar(32) NOT NULL DEFAULT '' COMMENT '员工编号',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '启用状态|0未启用,1已启用,2禁用(UC锁定)',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `last_login_ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录ip',
  `logins` smallint(4) unsigned NOT NULL DEFAULT '1' COMMENT '登陆次数',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `realname` (`realname`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=26158 DEFAULT CHARSET=utf8 COMMENT='管理员|朱国强|2018-03-19';

-- ----------------------------
-- Table structure for admin_log
-- ----------------------------
DROP TABLE IF EXISTS `admin_log`;
CREATE TABLE `admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `request_id` varchar(32) NOT NULL DEFAULT '' COMMENT '请求ID|sys_request_log.request_id',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员id|admin.id',
  `admin_name` varchar(30) NOT NULL DEFAULT '' COMMENT '管理员姓名',
  `request_route` varchar(60) NOT NULL DEFAULT '' COMMENT '请求路由',
  `record_table` varchar(30) NOT NULL DEFAULT '' COMMENT '操作记录表',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作记录id',
  `record_name` varchar(255) NOT NULL DEFAULT '' COMMENT '操作记录名称',
  `detail` mediumtext NOT NULL COMMENT '详情',
  `detail2diff` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '以比较差异格式显示详情',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ip|ip2long',
  `is_insert` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为insert新增|0否,1是',
  `labels` text NOT NULL COMMENT '差异字段对应的名称|json_encode',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`) USING BTREE,
  KEY `admin_name` (`admin_name`) USING BTREE,
  KEY `record_name` (`record_name`,`record_id`) USING BTREE,
  KEY `idx_request_id` (`request_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=5075032 DEFAULT CHARSET=utf8 COMMENT='管理员操作日志|朱国强|2018-03-16'';';

-- ----------------------------
-- Table structure for admin_login_history
-- ----------------------------
DROP TABLE IF EXISTS `admin_login_history`;
CREATE TABLE `admin_login_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员id|admin.id',
  `admin_name` char(30) NOT NULL DEFAULT '' COMMENT '管理员姓名',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登陆时间',
  `login_ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登陆ip',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员登陆历史|朱国强|2018-03-16'';';

-- ----------------------------
-- Table structure for admin_relation
-- ----------------------------
DROP TABLE IF EXISTS `admin_relation`;
CREATE TABLE `admin_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL COMMENT '用户账号id|admin.id',
  `role_id` int(10) unsigned NOT NULL COMMENT '角色id|role.id',
  `site_code` varchar(10) NOT NULL COMMENT '站点code（该字段有冗余）',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `role_id` (`role_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=10573 DEFAULT CHARSET=utf8 COMMENT='用户角色关系表|朱国强|2018-03-19';

-- ----------------------------
-- Table structure for admin_site_privilege
-- ----------------------------
DROP TABLE IF EXISTS `admin_site_privilege`;
CREATE TABLE `admin_site_privilege` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT '管理用户ID|admin.id',
  `website_code` varchar(5) DEFAULT NULL COMMENT '站点简码；如：zf/rg',
  `home_permissions` text COMMENT 'json格式用户拥有的首页活动渠道/语言权限',
  `special_permissions` text COMMENT '用户拥有的专题页活动渠道/语言权限json格式',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id` (`user_id`,`website_code`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8 COMMENT='管理员站点数据权限|田海深|20190131';

-- ----------------------------
-- Table structure for component_category
-- ----------------------------
DROP TABLE IF EXISTS `component_category`;
CREATE TABLE `component_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '组件分类名称',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '组件类型|1布局组件,2ui组件',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '''是否删除|0否,1是'',',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COMMENT='组件分类表|朱国强|2018-04-22';

-- ----------------------------
-- Table structure for component_site_relation
-- ----------------------------
DROP TABLE IF EXISTS `component_site_relation`;
CREATE TABLE `component_site_relation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '组件类型|1布局组件,2ui组件',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组件ID',
  `site_code` varchar(32) NOT NULL DEFAULT '' COMMENT '站点siteCode',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_site_code_component_id_type` (`site_code`,`component_id`,`type`) USING BTREE,
  KEY `idx_site_code` (`component_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=6248 DEFAULT CHARSET=utf8 COMMENT='组件站点配置表|滕家顺|2018-09-03';

-- ----------------------------
-- Table structure for component_tpl_site_relation
-- ----------------------------
DROP TABLE IF EXISTS `component_tpl_site_relation`;
CREATE TABLE `component_tpl_site_relation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '组件类型|1布局组件,2ui组件',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组件ID',
  `site_code` varchar(32) NOT NULL DEFAULT '' COMMENT '站点siteCode',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_site_code_tpl_id_type` (`site_code`,`tpl_id`,`type`) USING BTREE,
  KEY `idx_site_code` (`tpl_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=5435 DEFAULT CHARSET=utf8 COMMENT='组件模板站点配置表|滕家顺|2018-09-03';

-- ----------------------------
-- Table structure for department
-- ----------------------------
DROP TABLE IF EXISTS `department`;
CREATE TABLE `department` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '部门id|自增',
  `parent_id` int(11) unsigned NOT NULL COMMENT '父ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '部门名称',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `enable` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可用|0否，1是',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后更新人',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `name` (`name`) USING BTREE,
  KEY `update_user` (`update_user`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3776 DEFAULT CHARSET=utf8 COMMENT='部门表|朱国强|2018-03-19';

-- ----------------------------
-- Table structure for department_relation
-- ----------------------------
DROP TABLE IF EXISTS `department_relation`;
CREATE TABLE `department_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `department_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID|department.id',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点ID',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'create time',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'update time',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_site_code` (`department_id`,`site_code`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3846 DEFAULT CHARSET=utf8 COMMENT='部门|站点数据关系表|朱国强|2018-03-19';

-- ----------------------------
-- Table structure for dl_activity
-- ----------------------------
DROP TABLE IF EXISTS `dl_activity`;
CREATE TABLE `dl_activity` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `group_id` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动分组ID|activity_group.id',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道简码,多个渠道以英文逗号分隔',
  `lang` varchar(2047) NOT NULL DEFAULT '' COMMENT '活动所属语言，多个语系以英文逗号分隔，如zh,en,de',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '活动名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '活动简介',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态|1待上线,2已上线,3待下线,4已下线',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否，1是',
  `is_lock` tinyint(2) unsigned NOT NULL COMMENT '是否加锁 0：否 1：是|夏人杰|20180609',
  `is_frequently` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用活动(0 - 不是； 1 - 是)|田海深|20190904',
  `mold` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动类型 1 普通活动 2 活动推广',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_site` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=315 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for dl_activity_group
-- ----------------------------
DROP TABLE IF EXISTS `dl_activity_group`;
CREATE TABLE `dl_activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` text NOT NULL COMMENT '支持语言列表',
  `support_list` text NOT NULL COMMENT '支持端口渠道语言列表',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动分组|田海深|20180913';

-- ----------------------------
-- Table structure for dl_page
-- ----------------------------
DROP TABLE IF EXISTS `dl_page`;
CREATE TABLE `dl_page` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `group_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为在链接中展示使用，区别于其它表的page_id，此处取名pid',
  `activity_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联的活动ID|activity.id',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180709',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面状态|1待上线,2已上线,3待下线,4已下线',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端,3app端|夏人杰|20180709',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '加解锁 1：解锁 2：加锁|夏人杰|20180706',
  `auto_refresh` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要自动刷新',
  `refresh_time` int(11) NOT NULL DEFAULT '0' COMMENT '自动刷新时间间隔（0-不自动刷新）',
  `end_time` int(11) unsigned NOT NULL COMMENT '子页面活动下架时间|田海深|20180723',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `is_blog` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否博客页面|田海深|20190321',
  `home_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '首页类型 0：A首页 1：B首页|夏人杰|20190610',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
  `default_lang` varchar(6) NOT NULL DEFAULT '' COMMENT '默认语言简称，如：zh/en/de',
  `is_redirect_country` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否国家站自动跳转(0 - 不跳转； 1 - 跳转)|田海深|20190427',
  `is_native` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否原生专题 0：否 1：是|夏人杰|20190821',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '页面数据版本',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_pid` (`pid`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2254 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for dl_page_convert_relation
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_convert_relation`;
CREATE TABLE `dl_page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面转换关系表|滕家顺|2018-07-04';

-- ----------------------------
-- Table structure for dl_page_group
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_group`;
CREATE TABLE `dl_page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2198 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动子页面分组|田海深|20180913';

-- ----------------------------
-- Table structure for dl_page_language
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_language`;
CREATE TABLE `dl_page_language` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID|page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '页面title',
  `tpl_id` int(10) NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id|夏人杰|20180709',
  `seo_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO标题|滕家顺|20180814',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '页面keywords',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '页面描述',
  `background_color` varchar(12) NOT NULL DEFAULT '' COMMENT '背景颜色',
  `background_image` varchar(128) NOT NULL DEFAULT '' COMMENT '背景图片',
  `background_position` varchar(32) NOT NULL DEFAULT '' COMMENT '背景位置',
  `background_repeat` varchar(32) NOT NULL DEFAULT '' COMMENT '背景定位',
  `style_type` tinyint(2) NOT NULL COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903',
  `multi_time_style` text COMMENT '多时段样式|田海深|201800903',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830',
  `page_url` varchar(255) NOT NULL DEFAULT '' COMMENT '页面访问URL',
  `end_url` varchar(255) NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723',
  `redirect_url` varchar(255) NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806',
  `custom_css` text COMMENT '自定义CSS样式',
  `statistics_code` text COMMENT '统计代码',
  `local_files` text COMMENT '本地文件存储路径json格式（可能有多个文件）',
  `s3_files` text COMMENT '文件在S3上存储路径json格式（可能有多个文件）',
  `goods_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '推送网站状态0未推送 1推送成功',
  `share_image` varchar(255) NOT NULL DEFAULT '' COMMENT '分享图片',
  `share_title` varchar(255) NOT NULL DEFAULT '' COMMENT '分享标题',
  `share_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '分享描述',
  `share_link` varchar(255) NOT NULL DEFAULT '' COMMENT '分享链接',
  `share_place` tinytext COMMENT '分享渠道',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '页面数据版本',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2254 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面语言配置项表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for dl_page_layout_component
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_layout_component`;
CREATE TABLE `dl_page_layout_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言|夏人杰|20180709',
  `page_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`,`next_id`) USING BTREE,
  KEY `idx_next_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=11763 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for dl_page_layout_data
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_layout_data`;
CREATE TABLE `dl_page_layout_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text COMMENT '组件内容数据，json格式',
  `custom_css` text COMMENT '自定义CSS',
  `background_color` varchar(10) NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  `style_data` text COMMENT '配置样式数据',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_component_id` (`component_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=11760 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='layout组件数据表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for dl_page_publish_cache
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_publish_cache`;
CREATE TABLE `dl_page_publish_cache` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `html` mediumtext COMMENT '不包含网采商品的html',
  `html_network` mediumtext COMMENT '包含网采商品的html',
  `layout` mediumtext COMMENT '排好序的Layout组件列表',
  `uilist` mediumtext COMMENT 'UI组件列表',
  `js` mediumtext COMMENT '页面打包js',
  `css` mediumtext COMMENT '页面打包css',
  `customJs` mediumtext COMMENT '自定义Js',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0-未启用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面内容缓存记录|滕家顺|2018-07-30';

-- ----------------------------
-- Table structure for dl_page_publish_log
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_publish_log`;
CREATE TABLE `dl_page_publish_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `log_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '日志类型，1-缓存文件生成日志，2-发布S3日志',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180727',
  `action_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '操作类型（1-上线，2-下线）',
  `file_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `file_type` varchar(8) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `file_size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小，单位B',
  `file_hash` varchar(32) NOT NULL DEFAULT '' COMMENT '文件hash',
  `include_network` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否包含网采商品',
  `local_path` varchar(255) NOT NULL DEFAULT '' COMMENT '本地文件相对路径',
  `s3_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'S3上存储绝对路径',
  `diff` varchar(2000) NOT NULL DEFAULT '' COMMENT '差异对比json结果',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ip|ip2long|滕家顺|20180917',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `rollback_user` varchar(32) NOT NULL DEFAULT '' COMMENT '回滚操作人|夏人杰|20190610',
  `rollback_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回滚时间|夏人杰|20190610',
  `online_user` varchar(32) NOT NULL DEFAULT '' COMMENT '上线操作人|夏人杰|20190610',
  `online_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上线时间|夏人杰|20190610',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`,`file_type`,`file_hash`) USING BTREE,
  KEY `idx_file_hash_type` (`file_hash`,`file_type`) USING BTREE,
  KEY `idx_version_page_id` (`version`,`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=75436 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面发布日志|滕家顺|2018-05-03';

-- ----------------------------
-- Table structure for dl_page_template
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_template`;
CREATE TABLE `dl_page_template` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '页面模板名称',
  `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704 3.推广页',
  `platform_type` tinyint(2) NOT NULL COMMENT '平台类型 1:PC; 2:Wap; 3:App|田海深|20180817',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '模板预览图',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `range` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '适用范围|1PC端,2WAP端,3响应式',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `custom_css` text COMMENT '页面自定义样式',
  `layout` text COMMENT '模板布局组件信息',
  `layout_data` text COMMENT '模板布局数据',
  `ui` text COMMENT '模板UI组件信息',
  `ui_data` mediumtext COMMENT '模板UI组件数据',
  `tpl_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板类型：1-公用模板；2-私有模板',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认|0否,1是',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '更信任',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `idx_site_code` (`site_code`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=223 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面模板';

-- ----------------------------
-- Table structure for dl_page_ui_component
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_ui_component`;
CREATE TABLE `dl_page_ui_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  `bind_relation` varchar(100) NOT NULL DEFAULT '' COMMENT '三端绑定关系',
  `async_data_format` text COMMENT '组件自动刷新数据格式|田海深|20190627',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_layout_id_next_id_position` (`layout_id`,`next_id`,`position`,`lang`) USING BTREE,
  KEY `idx_pred_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=24970 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件与UI组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for dl_page_ui_component_data
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_ui_component_data`;
CREATE TABLE `dl_page_ui_component_data` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=622266 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='UI组件数据表|黄志辉|2018-07-04';

-- ----------------------------
-- Table structure for dl_page_ui_template
-- ----------------------------
DROP TABLE IF EXISTS `dl_page_ui_template`;
CREATE TABLE `dl_page_ui_template` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `website_code` varchar(5) NOT NULL COMMENT '网站简码，如： zf/rg/dl',
  `platform_code` varchar(5) NOT NULL COMMENT '平台简码，如： pc/wap/app',
  `page_id` bigint(11) unsigned NOT NULL COMMENT '原页面ID',
  `place_type` tinyint(2) NOT NULL COMMENT '应用场景1：活动页 2：首页| 3.推广页',
  `lang` varchar(5) NOT NULL COMMENT '语言代码简称，如：en/zh/de',
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `pic_url` varchar(255) DEFAULT NULL COMMENT '模板预览图',
  `ui_key` varchar(20) NOT NULL COMMENT '组件key,如：U00001',
  `tpl_id` bigint(11) unsigned NOT NULL COMMENT '组件模板id',
  `ui` mediumtext COMMENT '组件|田海深|20190627',
  `ui_data` mediumtext COMMENT '组件数据',
  `view_type` tinyint(2) unsigned NOT NULL COMMENT '查看类型：1-公用模板；2-私有模板',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除(0否,1是)',
  `create_user` varchar(50) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(50) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `used_count` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_platform_place` (`platform_code`,`place_type`) USING BTREE,
  KEY `idx_create_user` (`create_user`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8 COMMENT='组件模板';

-- ----------------------------
-- Table structure for dl3_activity
-- ----------------------------
DROP TABLE IF EXISTS `dl3_activity`;
CREATE TABLE `dl3_activity` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `group_id` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动分组ID|activity_group.id',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `lang` varchar(255) NOT NULL DEFAULT '' COMMENT '活动所属语言，多个语系以英文逗号分隔，如zh,en,de',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '活动名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '活动简介',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态|1待上线,2已上线,3待下线,4已下线',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否，1是',
  `is_lock` tinyint(2) unsigned NOT NULL COMMENT '是否加锁 0：否 1：是|夏人杰|20180609',
  `is_frequently` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用活动(0 - 不是； 1 - 是)|田海深|20190904',
  `mold` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动类型 1 普通活动 2 活动推广',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_site` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8 COMMENT='活动表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for dl3_activity_group
-- ----------------------------
DROP TABLE IF EXISTS `dl3_activity_group`;
CREATE TABLE `dl3_activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` varchar(200) NOT NULL COMMENT '支持语言列表',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8 COMMENT='活动分组|田海深|20180913';

-- ----------------------------
-- Table structure for dl3_page
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page`;
CREATE TABLE `dl3_page` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为在链接中展示使用，区别于其它表的page_id，此处取名pid',
  `activity_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联的活动ID|activity.id',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180709',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面状态|1待上线,2已上线,3待下线,4已下线',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端,3app端|夏人杰|20180709',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '加解锁 1：解锁 2：加锁|夏人杰|20180706',
  `auto_refresh` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要自动刷新',
  `refresh_time` int(11) NOT NULL DEFAULT '0' COMMENT '自动刷新时间间隔（0-不自动刷新）',
  `end_time` int(11) unsigned NOT NULL COMMENT '子页面活动下架时间|田海深|20180723',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pid` (`pid`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_url_name` (`url_name`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=647 DEFAULT CHARSET=utf8 COMMENT='页面表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for dl3_page_convert_relation
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_convert_relation`;
CREATE TABLE `dl3_page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8 COMMENT='页面转换关系表|滕家顺|2018-07-04';

-- ----------------------------
-- Table structure for dl3_page_group
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_group`;
CREATE TABLE `dl3_page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=565 DEFAULT CHARSET=utf8 COMMENT='活动子页面分组|田海深|20180913';

-- ----------------------------
-- Table structure for dl3_page_language
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_language`;
CREATE TABLE `dl3_page_language` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID|page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '页面title',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id|夏人杰|20180709',
  `seo_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO标题|滕家顺|20180814',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '页面keywords',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '页面描述',
  `background_color` varchar(12) NOT NULL DEFAULT '' COMMENT '背景颜色',
  `background_image` varchar(128) NOT NULL DEFAULT '' COMMENT '背景图片',
  `background_position` varchar(32) NOT NULL DEFAULT '' COMMENT '背景位置',
  `background_repeat` varchar(32) NOT NULL DEFAULT '' COMMENT '背景定位',
  `background_attachment` char(10) NOT NULL COMMENT '背景固定|夏人杰|20181214',
  `goods_component_style` text COMMENT '商品类组件样式设置|夏人杰|20181218',
  `style_type` tinyint(2) NOT NULL COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903',
  `multi_time_style` text COMMENT '多时段样式|田海深|201800903',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830',
  `page_url` varchar(255) NOT NULL DEFAULT '' COMMENT '页面访问URL',
  `end_url` varchar(255) NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723',
  `redirect_url` varchar(255) NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806',
  `custom_css` text COMMENT '自定义CSS样式',
  `statistics_code` text COMMENT '统计代码',
  `local_files` text COMMENT '本地文件存储路径json格式（可能有多个文件）',
  `s3_files` text COMMENT '文件在S3上存储路径json格式（可能有多个文件）',
  `goods_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '推送网站状态0未推送 1推送成功',
  `share_image` varchar(255) NOT NULL DEFAULT '' COMMENT '分享图片',
  `share_title` varchar(255) NOT NULL DEFAULT '' COMMENT '分享标题',
  `share_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '分享描述',
  `share_link` varchar(255) NOT NULL DEFAULT '' COMMENT '分享链接',
  `share_place` tinytext COMMENT '分享渠道',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1202 DEFAULT CHARSET=utf8 COMMENT='页面语言配置项表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for dl3_page_layout_component
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_layout_component`;
CREATE TABLE `dl3_page_layout_component` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言|夏人杰|20180709',
  `page_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`,`next_id`) USING BTREE,
  KEY `idx_next_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=10926 DEFAULT CHARSET=utf8 COMMENT='页面布局组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for dl3_page_layout_data
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_layout_data`;
CREATE TABLE `dl3_page_layout_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text COMMENT '组件内容数据，json格式',
  `custom_css` text COMMENT '自定义CSS',
  `background_color` varchar(10) NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_component_id` (`component_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=10923 DEFAULT CHARSET=utf8 COMMENT='layout组件数据表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for dl3_page_publish_cache
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_publish_cache`;
CREATE TABLE `dl3_page_publish_cache` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `html` mediumtext COMMENT '页面html',
  `html_network` mediumtext COMMENT '包含网采商品的html',
  `layout` mediumtext COMMENT '排好序的Layout组件列表',
  `uilist` mediumtext COMMENT 'UI组件列表',
  `js` mediumtext COMMENT '页面js',
  `css` mediumtext COMMENT '页面css',
  `customJs` mediumtext COMMENT '自定义Js',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0-未启用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3286 DEFAULT CHARSET=utf8 COMMENT='页面内容缓存记录|滕家顺|2018-07-30';

-- ----------------------------
-- Table structure for dl3_page_publish_log
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_publish_log`;
CREATE TABLE `dl3_page_publish_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `log_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '日志类型，1-缓存文件生成日志，2-发布S3日志',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180727',
  `action_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '操作类型（1-上线，2-下线）',
  `file_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `file_type` varchar(8) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `file_size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小，单位B',
  `file_hash` varchar(32) NOT NULL DEFAULT '' COMMENT '文件hash',
  `include_network` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否包含网采商品',
  `local_path` varchar(255) NOT NULL DEFAULT '' COMMENT '本地文件相对路径',
  `s3_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'S3上存储绝对路径',
  `diff` varchar(2000) NOT NULL DEFAULT '' COMMENT '差异对比json结果',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ip|ip2long|滕家顺|20180917',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_page_id_lang_file` (`page_id`,`lang`,`file_type`,`file_hash`) USING BTREE,
  KEY `idx_file_hash_type` (`file_hash`,`file_type`) USING BTREE,
  KEY `idx_version_page_id` (`version`,`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=67113 DEFAULT CHARSET=utf8 COMMENT='页面发布日志|滕家顺|2018-05-03';

-- ----------------------------
-- Table structure for dl3_page_template
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_template`;
CREATE TABLE `dl3_page_template` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '页面模板名称',
  `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704 3.推广页',
  `platform_type` tinyint(2) NOT NULL COMMENT '平台类型 1:PC; 2:Wap; 3:App|田海深|20180817',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '模板预览图',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `range` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '适用范围|1PC端,2WAP端,3响应式',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `custom_css` text COMMENT '页面自定义样式',
  `layout` text COMMENT '模板布局组件信息',
  `layout_data` text COMMENT '模板布局数据',
  `ui` text COMMENT '模板UI组件信息',
  `ui_data` text COMMENT '模板UI组件数据',
  `tpl_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板类型：1-公用模板；2-私有模板',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '''是否默认|0否,1是'',',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '''是否删除|0否,1是'',',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '更信任',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8 COMMENT='页面模板';

-- ----------------------------
-- Table structure for dl3_page_ui_component
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_ui_component`;
CREATE TABLE `dl3_page_ui_component` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  `async_data_format` text COMMENT '组件自动刷新数据格式|田海深|20190627',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_layout_id_next_id_position` (`layout_id`,`next_id`,`position`,`lang`) USING BTREE,
  KEY `idx_pred_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=21912 DEFAULT CHARSET=utf8 COMMENT='页面布局组件与UI组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for dl3_page_ui_component_data
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_ui_component_data`;
CREATE TABLE `dl3_page_ui_component_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=527565 DEFAULT CHARSET=utf8 COMMENT='UI组件数据表|黄志辉|2018-07-04';

-- ----------------------------
-- Table structure for dl3_page_ui_template
-- ----------------------------
DROP TABLE IF EXISTS `dl3_page_ui_template`;
CREATE TABLE `dl3_page_ui_template` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `website_code` varchar(5) NOT NULL COMMENT '网站简码，如： zf/rg/dl',
  `platform_code` varchar(5) NOT NULL COMMENT '平台简码，如： pc/wap/app',
  `page_id` bigint(11) unsigned NOT NULL COMMENT '原页面ID',
  `place_type` tinyint(2) NOT NULL COMMENT '应用场景1：活动页 2：首页| 3.推广页',
  `lang` varchar(5) NOT NULL COMMENT '语言代码简称，如：en/zh/de',
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `pic_url` varchar(255) DEFAULT NULL COMMENT '模板预览图',
  `ui_key` varchar(20) NOT NULL COMMENT '组件key,如：U00001',
  `tpl_id` bigint(11) unsigned NOT NULL COMMENT '组件模板id',
  `ui` mediumtext COMMENT '组件|田海深|20190627',
  `ui_data` mediumtext COMMENT '组件数据',
  `view_type` tinyint(2) unsigned NOT NULL COMMENT '查看类型：1-公用模板；2-私有模板',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除(0否,1是)',
  `create_user` varchar(50) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(50) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `used_count` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_platform_place` (`platform_code`,`place_type`) USING BTREE,
  KEY `idx_create_user` (`create_user`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8 COMMENT='组件模板';

-- ----------------------------
-- Table structure for gb_activity
-- ----------------------------
DROP TABLE IF EXISTS `gb_activity`;
CREATE TABLE `gb_activity` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `group_id` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动分组ID|activity_group.id',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道简码,多个渠道以英文逗号分隔',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '活动名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '活动简介',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态|1待上线,2已上线,3待下线,4已下线',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否，1是',
  `is_lock` tinyint(2) unsigned NOT NULL COMMENT '是否加锁 0：否 1：是|夏人杰|20180609',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `theme_name` varchar(100) NOT NULL DEFAULT '' COMMENT '板块名称',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_site` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=813 DEFAULT CHARSET=utf8 COMMENT='活动表';

-- ----------------------------
-- Table structure for gb_activity_group
-- ----------------------------
DROP TABLE IF EXISTS `gb_activity_group`;
CREATE TABLE `gb_activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` mediumtext COMMENT '支持渠道语言列表',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=utf8 COMMENT='活动分组';

-- ----------------------------
-- Table structure for gb_page
-- ----------------------------
DROP TABLE IF EXISTS `gb_page`;
CREATE TABLE `gb_page` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为在链接中展示使用，区别于其它表的page_id，此处取名pid',
  `activity_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联的活动ID|activity.id',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180709',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面状态|1待上线,2已上线,3待下线,4已下线',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端,3app端|夏人杰|20180709',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '加解锁 1：解锁 2：加锁|夏人杰|20180706',
  `auto_refresh` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要自动刷新',
  `refresh_time` int(11) NOT NULL DEFAULT '0' COMMENT '自动刷新时间间隔（0-不自动刷新）',
  `end_time` int(11) unsigned NOT NULL COMMENT '子页面活动下架时间|田海深|20180723',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
  `defaultLanguage` varchar(6) NOT NULL DEFAULT '' COMMENT '默认语言简称，如：zh/en/de',
  `group_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pid` (`pid`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_url_name` (`url_name`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=17835 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面表';

-- ----------------------------
-- Table structure for gb_page_convert_relation
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_convert_relation`;
CREATE TABLE `gb_page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=738 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面转换关系表';

-- ----------------------------
-- Table structure for gb_page_group
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_group`;
CREATE TABLE `gb_page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `special_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '专题ID|夏人杰|20190103',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=17823 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动子页面分组';

-- ----------------------------
-- Table structure for gb_page_language
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_language`;
CREATE TABLE `gb_page_language` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID|page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '页面title',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id|夏人杰|20180709',
  `seo_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO标题|滕家顺|20180814',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '页面keywords',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '页面描述',
  `background_color` varchar(12) NOT NULL DEFAULT '' COMMENT '背景颜色',
  `background_image` varchar(128) NOT NULL DEFAULT '' COMMENT '背景图片',
  `background_position` varchar(32) NOT NULL DEFAULT '' COMMENT '背景位置',
  `background_repeat` varchar(32) NOT NULL DEFAULT '' COMMENT '背景定位',
  `background_attachment` char(10) NOT NULL DEFAULT '' COMMENT '背景固定|夏人杰|20181213',
  `goods_component_style` text NOT NULL COMMENT '商品类组件样式设置|夏人杰|20181218',
  `style_type` tinyint(2) NOT NULL COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903',
  `multi_time_style` text COMMENT '多时段样式|田海深|201800903',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830',
  `page_url` varchar(255) NOT NULL DEFAULT '' COMMENT '页面访问URL',
  `end_url` varchar(255) NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723',
  `redirect_url` varchar(255) NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806',
  `custom_css` text COMMENT '自定义CSS样式',
  `statistics_code` text COMMENT '统计代码',
  `local_files` text COMMENT '本地文件存储路径json格式（可能有多个文件）',
  `s3_files` text COMMENT '文件在S3上存储路径json格式（可能有多个文件）',
  `group_id` varchar(32) DEFAULT '0' COMMENT '渠道聚合ID',
  `share` mediumtext COMMENT '分享信息(json)',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=22359 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面语言配置项表';

-- ----------------------------
-- Table structure for gb_page_layout_component
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_layout_component`;
CREATE TABLE `gb_page_layout_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言|夏人杰|20180709',
  `page_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`,`next_id`) USING BTREE,
  KEY `idx_next_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=9966 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件关联表';

-- ----------------------------
-- Table structure for gb_page_layout_data
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_layout_data`;
CREATE TABLE `gb_page_layout_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text COMMENT '组件内容数据，json格式',
  `custom_css` text COMMENT '自定义CSS',
  `background_color` varchar(10) NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_component_id` (`component_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=9960 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='layout组件数据表';

-- ----------------------------
-- Table structure for gb_page_publish_cache
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_publish_cache`;
CREATE TABLE `gb_page_publish_cache` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `html` mediumtext COMMENT '页面html',
  `layout` mediumtext COMMENT '排好序的Layout组件列表',
  `uilist` mediumtext COMMENT 'UI组件列表',
  `js` mediumtext COMMENT '页面js',
  `css` mediumtext COMMENT '页面CSS',
  `customJs` mediumtext COMMENT '自定义Js',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0-未启用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=257000 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面内容缓存记录';

-- ----------------------------
-- Table structure for gb_page_publish_log
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_publish_log`;
CREATE TABLE `gb_page_publish_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `log_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '日志类型，1-缓存文件生成日志，2-发布S3日志',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180727',
  `action_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '操作类型（1-上线，2-下线）',
  `file_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `file_type` varchar(8) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `file_size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小，单位B',
  `file_hash` varchar(32) NOT NULL DEFAULT '' COMMENT '文件hash',
  `local_path` varchar(255) NOT NULL DEFAULT '' COMMENT '本地文件相对路径',
  `s3_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'S3上存储绝对路径',
  `diff` varchar(2000) NOT NULL DEFAULT '' COMMENT '差异对比json结果',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ip|ip2long|滕家顺|20180917',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_file_hash_type` (`file_hash`,`file_type`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`,`file_type`,`file_hash`) USING BTREE,
  KEY `idx_version_page_id` (`version`,`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1667684 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面发布日志';

-- ----------------------------
-- Table structure for gb_page_service_tag
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_service_tag`;
CREATE TABLE `gb_page_service_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID|gb_page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `tag_config` text COMMENT '服务标配置',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面服务标配置表|田海深|2018-11-03';

-- ----------------------------
-- Table structure for gb_page_special
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_special`;
CREATE TABLE `gb_page_special` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID|夏人杰|20190104',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8 COMMENT='专题活动页扩展表|夏人杰|20190104';

-- ----------------------------
-- Table structure for gb_page_ui_component
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_ui_component`;
CREATE TABLE `gb_page_ui_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_layout_id_next_id_position` (`layout_id`,`next_id`,`position`,`lang`) USING BTREE,
  KEY `idx_pred_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=48812 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件与UI组件关联表';

-- ----------------------------
-- Table structure for gb_page_ui_component_data
-- ----------------------------
DROP TABLE IF EXISTS `gb_page_ui_component_data`;
CREATE TABLE `gb_page_ui_component_data` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=558291 DEFAULT CHARSET=utf8 COMMENT='UI组件数据表';

-- ----------------------------
-- Table structure for goods_manage_data_block_bak
-- ----------------------------
DROP TABLE IF EXISTS `goods_manage_data_block_bak`;
CREATE TABLE `goods_manage_data_block_bak` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `gmp_id` bigint(11) NOT NULL COMMENT '商品管理页面ID|goods_manage_page.id',
  `lang` varchar(6) NOT NULL COMMENT '语言简称，如：zh/en/de',
  `category_title` varchar(255) NOT NULL COMMENT '商品分类的标题名称',
  `more_url` varchar(255) DEFAULT NULL COMMENT 'view more 链接',
  `sku_from` tinyint(2) NOT NULL COMMENT 'SKU来源(1:手动输入; 2:选品系统)|田海深|201800901',
  `goods_sku` text COMMENT '商品sku列表，多个用英文逗号分隔',
  `sort_num` tinyint(3) NOT NULL COMMENT '商品UI组件显示顺序',
  `data_md5` varchar(32) NOT NULL COMMENT '数据签名,包含标题|链接|sku|顺序',
  `is_same` tinyint(1) NOT NULL COMMENT '不同端数据是否一致|0 不一致 1 一致',
  `component_id` int(11) NOT NULL COMMENT '组件ID（page_ui_component.id）',
  `create_user` varchar(20) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_gmp_id` (`gmp_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=319 DEFAULT CHARSET=utf8 COMMENT='商品管理页面数据块表|田海深|2018-07-06';

-- ----------------------------
-- Table structure for goods_manage_page_bak
-- ----------------------------
DROP TABLE IF EXISTS `goods_manage_page_bak`;
CREATE TABLE `goods_manage_page_bak` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` varchar(32) NOT NULL COMMENT '分组ID',
  `site_code` varchar(10) NOT NULL COMMENT '站点简称，如：rw-pc/rw-wap',
  `activity_id` int(11) unsigned NOT NULL COMMENT '关联的活动ID|activity.id',
  `page_id` int(11) unsigned NOT NULL COMMENT '页面ID|page.id',
  PRIMARY KEY (`id`),
  KEY `idx_site_code` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8 COMMENT='商品管理页面表|田海深|2018-07-06';

-- ----------------------------
-- Table structure for index_activity_data
-- ----------------------------
DROP TABLE IF EXISTS `index_activity_data`;
CREATE TABLE `index_activity_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '数据记录ID',
  `buyer_identity` tinyint(4) NOT NULL DEFAULT '0' COMMENT '新老客标识:0 , 1 , 2 [说明:2是整体的数据,1 代表新客,0 代表老客]',
  `page_name` varchar(20) NOT NULL DEFAULT '' COMMENT '页面名称：专题页，促销页，列表页，商详页，搜索页等',
  `page_type` varchar(20) NOT NULL DEFAULT '' COMMENT '页面类型：同上,如 b03',
  `sub_id` int(11) NOT NULL DEFAULT '0' COMMENT '专题ID等',
  `sub_ie_pv` int(11) NOT NULL DEFAULT '0' COMMENT '专题曝光PV',
  `sub_ic_pv` int(11) NOT NULL DEFAULT '0' COMMENT '专题点击PV',
  `sub_uv` int(11) NOT NULL DEFAULT '0' COMMENT '专题访问人数',
  `sub_cl_rate` double(16,2) NOT NULL DEFAULT '0.00' COMMENT '专题点击率',
  `location` int(11) NOT NULL DEFAULT '0' COMMENT '位置',
  `module_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID',
  `module_ie_pv` int(11) NOT NULL DEFAULT '0' COMMENT '组件曝光PV',
  `module_ic_pv` int(11) NOT NULL DEFAULT '0' COMMENT '组件点击PV',
  `pit_id` int(11) NOT NULL DEFAULT '0' COMMENT '坑位',
  `pit_ie_pv` int(11) NOT NULL DEFAULT '0' COMMENT '坑位曝光PV',
  `pit_ic_pv` int(11) NOT NULL DEFAULT '0' COMMENT '坑位点击PV',
  `pit_cl_rate` double(16,2) NOT NULL DEFAULT '0.00' COMMENT '坑位点击率',
  `platform` varchar(20) NOT NULL DEFAULT '' COMMENT '终端维度：pc,m,ios,android,others,all',
  `update_time` date NOT NULL DEFAULT '1970-01-01' COMMENT '日期(YYYY-MM-DD)',
  `site` varchar(20) NOT NULL DEFAULT '' COMMENT '网站',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_site_platform_buyer_identity` (`buyer_identity`,`platform`,`site`) USING BTREE,
  KEY `idx_sub_id` (`sub_id`) USING BTREE,
  KEY `idx_module_id` (`module_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3193832 DEFAULT CHARSET=utf8 COMMENT='首页页流量数据统计表';

-- ----------------------------
-- Table structure for language
-- ----------------------------
DROP TABLE IF EXISTS `language`;
CREATE TABLE `language` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `activity_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动ID',
  `is_js` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否js语言包|@0否@1是',
  `alias` varchar(50) NOT NULL DEFAULT '' COMMENT '键别名',
  `key_name` varchar(2000) NOT NULL DEFAULT '' COMMENT '多语言键名',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_user` varchar(32) NOT NULL DEFAULT '0' COMMENT '创建者',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_user` varchar(32) NOT NULL DEFAULT '0' COMMENT '最后更新用户',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_alias_activity_id` (`alias`,`activity_id`) USING BTREE,
  KEY `idx_key_name` (`key_name`(255)) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='多语言key表|滕家顺|2018-03-31';

-- ----------------------------
-- Table structure for language_trans
-- ----------------------------
DROP TABLE IF EXISTS `language_trans`;
CREATE TABLE `language_trans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `language_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '多语言原文记录的id',
  `lang` varchar(5) NOT NULL DEFAULT 'en' COMMENT '语系简称',
  `content` varchar(2000) NOT NULL DEFAULT '' COMMENT '多语言内容',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建者',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后操作人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_language_id_lang` (`language_id`,`lang`) USING BTREE,
  KEY `idx_content` (`content`(255)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='多语言内容表|滕家顺|2018-03-31';

-- ----------------------------
-- Table structure for layout_component
-- ----------------------------
DROP TABLE IF EXISTS `layout_component`;
CREATE TABLE `layout_component` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '组件分类id',
  `is_custom` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否自定义|0否,1是',
  `range` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '适用范围|1PC端,2WAP端,3响应式',
  `component_key` varchar(10) NOT NULL DEFAULT '0' COMMENT '组件身份编码|第一位:[组件类型(L为布局)],后面六位自增数字序号)',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '组件名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '组件描述',
  `logo_url` varchar(255) DEFAULT '' COMMENT '组件logo',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '组件状态|1预申请,2待上线,3已上线,4待下线,5已下线',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '5' COMMENT '组件审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_component_key` (`component_key`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COMMENT='布局组件表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for menu
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单id|自增',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `route` char(60) NOT NULL DEFAULT '' COMMENT 'php路由',
  `icon_class` char(30) NOT NULL DEFAULT '' COMMENT '图标class',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '路由类型|1菜单，0操作',
  `is_public` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否公开不需检测权限|0否，1是',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '路由状态|0关闭,1开启',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `node` char(50) NOT NULL DEFAULT '' COMMENT '节点|...,父父id,父id,id',
  `remark` char(100) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  UNIQUE KEY `route` (`route`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8 COMMENT='后台菜单|朱国强|2018-03-19';

-- ----------------------------
-- Table structure for menu_copy
-- ----------------------------
DROP TABLE IF EXISTS `menu_copy`;
CREATE TABLE `menu_copy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单id|自增',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `route` char(60) NOT NULL DEFAULT '' COMMENT 'php路由',
  `icon_class` char(30) NOT NULL DEFAULT '' COMMENT '图标class',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '路由类型|1菜单，0操作',
  `is_public` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否公开不需检测权限|0否，1是',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '路由状态|0关闭,1开启',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序|越小越靠前',
  `node` char(50) NOT NULL DEFAULT '' COMMENT '节点|...,父父id,父id,id',
  `remark` char(100) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  UNIQUE KEY `route` (`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8 COMMENT='后台菜单|朱国强|2018-03-19';

-- ----------------------------
-- Table structure for native_page_layout_component
-- ----------------------------
DROP TABLE IF EXISTS `native_page_layout_component`;
CREATE TABLE `native_page_layout_component` (
  `id` bigint(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_id` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言',
  `data` mediumtext COMMENT '组件排列顺序',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1831 DEFAULT CHARSET=utf8 COMMENT='原生专题页面布局组件关联表|夏人杰|20190821';

-- ----------------------------
-- Table structure for native_page_template
-- ----------------------------
DROP TABLE IF EXISTS `native_page_template`;
CREATE TABLE `native_page_template` (
  `id` bigint(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '页面模板名称',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '模板预览图',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_data` mediumtext COMMENT '模板布局数据',
  `ui_data` mediumtext COMMENT '模板UI组件数据',
  `tpl_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板类型：1-公用模板；2-私有模板',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '''是否默认|0否,1是'',',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '''是否删除|0否,1是'',',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '更信任',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `idx_site_code` (`site_code`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='原生专题页面模板|夏人杰|20190831';

-- ----------------------------
-- Table structure for native_page_ui_component
-- ----------------------------
DROP TABLE IF EXISTS `native_page_ui_component`;
CREATE TABLE `native_page_ui_component` (
  `id` bigint(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` bigint(12) unsigned NOT NULL COMMENT 'UI组件页面位置ID',
  `page_id` bigint(12) unsigned NOT NULL COMMENT '关联页面',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  `tpl_title` varchar(50) NOT NULL DEFAULT '' COMMENT '模板title',
  `tpl_name` varchar(50) NOT NULL DEFAULT '' COMMENT '模板名称',
  `style_data` mediumtext COMMENT '组件样式内容',
  `sku_data` mediumtext COMMENT '商品数据',
  `setting_data` mediumtext COMMENT '配置内容',
  `async_data_format` mediumtext COMMENT '组件自动刷新数据格式',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_component_page_id_lang` (`component_id`,`page_id`,`lang`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=17087 DEFAULT CHARSET=utf8 COMMENT='原生专题页面布局组件与UI组件|夏人杰|20190821';

-- ----------------------------
-- Table structure for page
-- ----------------------------
DROP TABLE IF EXISTS `page`;
CREATE TABLE `page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为在链接中展示使用，区别于其它表的page_id，此处取名pid',
  `activity_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联的活动ID|activity.id',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180709',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面状态|1待上线,2已上线,3待下线,4已下线',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端,3app端|夏人杰|20180709',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `custom_css` text NOT NULL COMMENT '自定义CSS样式',
  `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '加解锁 1：解锁 2：加锁|夏人杰|20180706',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id',
  `auto_refresh` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要自动刷新',
  `refresh_time` int(11) NOT NULL DEFAULT '0' COMMENT '自动刷新时间间隔（0-不自动刷新）',
  `end_time` int(11) unsigned NOT NULL COMMENT '子页面活动下架时间|田海深|20180723',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
  `relation_page` varchar(32) NOT NULL DEFAULT '' COMMENT '记录关联页面|夏人杰|20180614',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pid` (`pid`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_url_name` (`url_name`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4160 DEFAULT CHARSET=utf8 COMMENT='页面表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for page_convert_relation
-- ----------------------------
DROP TABLE IF EXISTS `page_convert_relation`;
CREATE TABLE `page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=920 DEFAULT CHARSET=utf8 COMMENT='页面转换关系表|滕家顺|2018-07-04';

-- ----------------------------
-- Table structure for page_group
-- ----------------------------
DROP TABLE IF EXISTS `page_group`;
CREATE TABLE `page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3165 DEFAULT CHARSET=utf8 COMMENT='活动子页面分组|田海深|20180913';

-- ----------------------------
-- Table structure for page_language
-- ----------------------------
DROP TABLE IF EXISTS `page_language`;
CREATE TABLE `page_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID|page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '页面title',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id|夏人杰|20180709',
  `seo_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO标题|滕家顺|20180814',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '页面keywords',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '页面描述',
  `background_color` varchar(12) NOT NULL DEFAULT '' COMMENT '背景颜色',
  `background_image` varchar(128) NOT NULL DEFAULT '' COMMENT '背景图片',
  `background_position` varchar(32) NOT NULL DEFAULT '' COMMENT '背景位置',
  `background_repeat` varchar(32) NOT NULL DEFAULT '' COMMENT '背景定位',
  `background_attachment` char(10) NOT NULL DEFAULT '' COMMENT '背景固定|夏人杰|20181214',
  `goods_component_style` text COMMENT '商品类组件样式设置|夏人杰|20181218',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830',
  `style_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903',
  `multi_time_style` text COMMENT '多时段样式|田海深|201800903',
  `page_url` varchar(255) NOT NULL DEFAULT '' COMMENT '页面访问URL',
  `end_url` varchar(255) NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723',
  `redirect_url` varchar(255) NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806',
  `custom_css` text NOT NULL COMMENT '自定义CSS样式',
  `statistics_code` text NOT NULL COMMENT '统计代码',
  `local_files` text NOT NULL COMMENT '本地文件存储路径json格式（可能有多个文件）',
  `s3_files` text NOT NULL COMMENT '文件在S3上存储路径json格式（可能有多个文件）',
  `goods_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '推送网站状态0未推送 1推送成功',
  `share_image` varchar(255) NOT NULL DEFAULT '' COMMENT '分享图片',
  `share_title` varchar(255) NOT NULL DEFAULT '' COMMENT '分享标题',
  `share_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '分享描述',
  `share_link` varchar(255) NOT NULL DEFAULT '' COMMENT '分享链接',
  `share_place` tinytext NOT NULL COMMENT '分享渠道',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=9269 DEFAULT CHARSET=utf8 COMMENT='页面语言配置项表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for page_language_data
-- ----------------------------
DROP TABLE IF EXISTS `page_language_data`;
CREATE TABLE `page_language_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言编码|夏人杰|20190617',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT '多语言key|夏人杰|20190617',
  `value` varchar(500) NOT NULL DEFAULT '' COMMENT '多语言内容|夏人杰|20190617',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '多语言说明|夏人杰|20190617',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人|夏人杰|20190617',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间|夏人杰|20190617',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '操作人|夏人杰|20190617',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间|夏人杰|20190617',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除 0：否 1：是|夏人杰|20190617',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_lang` (`lang`),
  KEY `idx_key` (`key`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1600 DEFAULT CHARSET=utf8 COMMENT='多语言内容列表|夏人杰|20190617';

-- ----------------------------
-- Table structure for page_language_data_bak
-- ----------------------------
DROP TABLE IF EXISTS `page_language_data_bak`;
CREATE TABLE `page_language_data_bak` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言编码|夏人杰|20190617',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT '多语言key|夏人杰|20190617',
  `value` varchar(500) NOT NULL DEFAULT '' COMMENT '多语言内容|夏人杰|20190617',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '多语言说明|夏人杰|20190617',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人|夏人杰|20190617',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间|夏人杰|20190617',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '操作人|夏人杰|20190617',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间|夏人杰|20190617',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除 0：否 1：是|夏人杰|20190617',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_lang` (`lang`),
  KEY `idx_key` (`key`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1606 DEFAULT CHARSET=utf8 COMMENT='多语言内容列表|夏人杰|20190617';

-- ----------------------------
-- Table structure for page_language_package
-- ----------------------------
DROP TABLE IF EXISTS `page_language_package`;
CREATE TABLE `page_language_package` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点编码|夏人杰|20190615',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言|夏人杰|20190615',
  `lang_name` varchar(30) NOT NULL DEFAULT '' COMMENT '语言名称|夏人杰|20190617',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人|夏人杰|20190615',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间|夏人杰|20190615',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '操作人|夏人杰|20190615',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间|夏人杰|20190615',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_site_code` (`site_code`),
  KEY `idx_lang` (`lang`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='语言包管理表|夏人杰|20190617';

-- ----------------------------
-- Table structure for page_layout_component
-- ----------------------------
DROP TABLE IF EXISTS `page_layout_component`;
CREATE TABLE `page_layout_component` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6) NOT NULL DEFAULT 'en' COMMENT '语言代码简称，如：en/zh/de',
  `page_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_id_next_lang` (`page_id`,`next_id`,`lang`) USING BTREE,
  KEY `idx_next_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=52531 DEFAULT CHARSET=utf8 COMMENT='页面布局组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for page_layout_data
-- ----------------------------
DROP TABLE IF EXISTS `page_layout_data`;
CREATE TABLE `page_layout_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id|page_ui_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT 'en' COMMENT '语言代码简称，如：en/zh/de',
  `data` text NOT NULL COMMENT '组件内容数据，json格式',
  `custom_css` text NOT NULL COMMENT '自定义CSS',
  `background_color` varchar(10) NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  `style_data` text COMMENT '配置样式数据',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_component_id` (`component_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=52253 DEFAULT CHARSET=utf8 COMMENT='layout组件数据表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for page_publish_cache
-- ----------------------------
DROP TABLE IF EXISTS `page_publish_cache`;
CREATE TABLE `page_publish_cache` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `html` mediumtext,
  `html_network` mediumtext COMMENT '包含网采商品的html',
  `layout` mediumtext COMMENT '排好序的Layout组件列表',
  `uilist` mediumtext COMMENT 'UI组件列表',
  `js` mediumtext,
  `css` mediumtext,
  `customJs` mediumtext COMMENT '自定义Js',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0-未启用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE,
  KEY `idx_page_id_lang_status` (`page_id`,`lang`,`status`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=353508 DEFAULT CHARSET=utf8 COMMENT='页面内容缓存记录|滕家顺|2018-07-30';

-- ----------------------------
-- Table structure for page_publish_log
-- ----------------------------
DROP TABLE IF EXISTS `page_publish_log`;
CREATE TABLE `page_publish_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `log_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '日志类型，1-缓存文件生成日志，2-发布S3日志',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180727',
  `action_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '操作类型（1-上线，2-下线）',
  `file_name` varchar(40) NOT NULL DEFAULT '' COMMENT '文件名',
  `file_type` varchar(8) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `file_size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小，单位B',
  `file_hash` varchar(32) NOT NULL DEFAULT '' COMMENT '文件hash',
  `include_network` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否包含网采商品',
  `local_path` varchar(255) NOT NULL DEFAULT '' COMMENT '本地文件相对路径',
  `s3_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'S3上存储绝对路径',
  `diff` varchar(2000) NOT NULL DEFAULT '' COMMENT '差异对比json结果',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ip|ip2long|滕家顺|20180917',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_page_id_lang_file` (`page_id`,`lang`,`file_type`,`file_hash`) USING BTREE,
  KEY `idx_file_hash_type` (`file_hash`,`file_type`) USING BTREE,
  KEY `idx_version_page_id` (`version`,`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2419491 DEFAULT CHARSET=utf8 COMMENT='页面发布日志|滕家顺|2018-05-03'''';';

-- ----------------------------
-- Table structure for page_template
-- ----------------------------
DROP TABLE IF EXISTS `page_template`;
CREATE TABLE `page_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '页面模板名称',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '模板预览图',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `range` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '适用范围|1PC端,2WAP端,3响应式',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `custom_css` text COMMENT '页面自定义样式',
  `layout` text NOT NULL COMMENT '模板布局组件信息',
  `layout_data` text NOT NULL COMMENT '模板布局数据',
  `ui` text NOT NULL COMMENT '模板UI组件信息',
  `ui_data` mediumtext NOT NULL COMMENT '模板UI组件数据',
  `tpl_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板类型：1-公用模板；2-私有模板',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '''是否默认|0否,1是'',',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '''是否删除|0否,1是'',',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '更信任',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704',
  `platform_type` tinyint(2) NOT NULL COMMENT '平台类型 1:PC; 2:Wap; 3:App|田海深|20180817',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=922 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for page_ui_component
-- ----------------------------
DROP TABLE IF EXISTS `page_ui_component`;
CREATE TABLE `page_ui_component` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT 'en' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  `async_data_format` text COMMENT '组件自动刷新数据格式|田海深|20190627',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_layout_next_position_lang` (`layout_id`,`next_id`,`position`,`lang`) USING BTREE,
  KEY `idx_pred_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=125669 DEFAULT CHARSET=utf8 COMMENT='页面布局组件与UI组件关联表|朱国强|2018-03-16'';';

-- ----------------------------
-- Table structure for page_ui_component_data
-- ----------------------------
DROP TABLE IF EXISTS `page_ui_component_data`;
CREATE TABLE `page_ui_component_data` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2392318 DEFAULT CHARSET=utf8 COMMENT='UI组件数据表|黄志辉|2018-07-04';

-- ----------------------------
-- Table structure for page_ui_data
-- ----------------------------
DROP TABLE IF EXISTS `page_ui_data`;
CREATE TABLE `page_ui_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id|page_ui_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text NOT NULL COMMENT '组件内容数据，json格式',
  `share_data` text COMMENT '常用配置数据，json格式',
  `custom_css` text NOT NULL COMMENT '自定义CSS',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件当前页面所用模板ID',
  `select_tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件当前页面选中模板ID',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_component_lang_tpl` (`component_id`,`lang`,`tpl_id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3195 DEFAULT CHARSET=utf8 COMMENT='UI组件数据表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for page_ui_template
-- ----------------------------
DROP TABLE IF EXISTS `page_ui_template`;
CREATE TABLE `page_ui_template` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `website_code` varchar(5) NOT NULL COMMENT '网站简码，如： zf/rg/dl',
  `platform_code` varchar(5) NOT NULL COMMENT '平台简码，如： pc/wap/app',
  `page_id` bigint(11) unsigned NOT NULL COMMENT '原页面ID',
  `place_type` tinyint(2) NOT NULL COMMENT '应用场景1：活动页 2：首页| 3.推广页',
  `lang` varchar(5) NOT NULL COMMENT '语言代码简称，如：en/zh/de',
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `pic_url` varchar(255) DEFAULT NULL COMMENT '模板预览图',
  `ui_key` varchar(20) NOT NULL COMMENT '组件key,如：U00001',
  `tpl_id` bigint(11) unsigned NOT NULL COMMENT '组件模板id',
  `ui` mediumtext COMMENT '组件|田海深|20190627',
  `ui_data` mediumtext COMMENT '组件数据',
  `view_type` tinyint(2) unsigned NOT NULL COMMENT '查看类型：1-公用模板；2-私有模板',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除(0否,1是)',
  `create_user` varchar(50) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(50) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `used_count` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_platform_place` (`platform_code`,`place_type`) USING BTREE,
  KEY `idx_create_user` (`create_user`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8 COMMENT='组件模板';

-- ----------------------------
-- Table structure for resource
-- ----------------------------
DROP TABLE IF EXISTS `resource`;
CREATE TABLE `resource` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) unsigned NOT NULL COMMENT '资源类型0目录1图片2视频3音频',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '资源标题',
  `node` varchar(255) NOT NULL COMMENT '结点',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资源分类id|resources.id',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '资源链接地址',
  `thumbnail_url` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图的url',
  `size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片尺寸',
  `height` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片的高度',
  `width` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片的宽度',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  `create_user` varchar(20) NOT NULL COMMENT '上传用户',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=9965 DEFAULT CHARSET=utf8 COMMENT='资源表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for rg_activity
-- ----------------------------
DROP TABLE IF EXISTS `rg_activity`;
CREATE TABLE `rg_activity` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `group_id` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动分组ID|activity_group.id',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道简码,多个渠道以英文逗号分隔',
  `lang` varchar(2047) NOT NULL DEFAULT '' COMMENT '活动所属语言，多个语系以英文逗号分隔，如zh,en,de',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '活动名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '活动简介',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态|1待上线,2已上线,3待下线,4已下线',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否，1是',
  `is_lock` tinyint(2) unsigned NOT NULL COMMENT '是否加锁 0：否 1：是|夏人杰|20180609',
  `is_frequently` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用活动(0 - 不是； 1 - 是)|田海深|20190904',
  `mold` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动类型 1 普通活动 2 活动推广',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_site` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2547 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for rg_activity_group
-- ----------------------------
DROP TABLE IF EXISTS `rg_activity_group`;
CREATE TABLE `rg_activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` text NOT NULL COMMENT '支持语言列表',
  `support_list` text NOT NULL COMMENT '支持端口渠道语言列表',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=974 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动分组|田海深|20180913';

-- ----------------------------
-- Table structure for rg_page
-- ----------------------------
DROP TABLE IF EXISTS `rg_page`;
CREATE TABLE `rg_page` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `group_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为在链接中展示使用，区别于其它表的page_id，此处取名pid',
  `activity_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联的活动ID|activity.id',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180709',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面状态|1待上线,2已上线,3待下线,4已下线',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端,3app端|夏人杰|20180709',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '加解锁 1：解锁 2：加锁|夏人杰|20180706',
  `auto_refresh` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要自动刷新',
  `refresh_time` int(11) NOT NULL DEFAULT '0' COMMENT '自动刷新时间间隔（0-不自动刷新）',
  `end_time` int(11) unsigned NOT NULL COMMENT '子页面活动下架时间|田海深|20180723',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `is_blog` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否博客页面|田海深|20190321',
  `home_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '首页类型 0：A首页 1：B首页|夏人杰|20190610',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
  `default_lang` varchar(6) NOT NULL DEFAULT '' COMMENT '默认语言简称，如：zh/en/de',
  `is_redirect_country` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否国家站自动跳转(0 - 不跳转； 1 - 跳转)|田海深|20190427',
  `is_native` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否原生专题 0：否 1：是|夏人杰|20190821',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '页面数据版本',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_pid` (`pid`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=9088 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for rg_page_convert_relation
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_convert_relation`;
CREATE TABLE `rg_page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1040 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面转换关系表|滕家顺|2018-07-04';

-- ----------------------------
-- Table structure for rg_page_group
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_group`;
CREATE TABLE `rg_page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4725 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动子页面分组|田海深|20180913';

-- ----------------------------
-- Table structure for rg_page_language
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_language`;
CREATE TABLE `rg_page_language` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID|page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '页面title',
  `tpl_id` int(10) NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id|夏人杰|20180709',
  `seo_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO标题|滕家顺|20180814',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '页面keywords',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '页面描述',
  `background_color` varchar(12) NOT NULL DEFAULT '' COMMENT '背景颜色',
  `background_image` varchar(128) NOT NULL DEFAULT '' COMMENT '背景图片',
  `background_position` varchar(32) NOT NULL DEFAULT '' COMMENT '背景位置',
  `background_repeat` varchar(32) NOT NULL DEFAULT '' COMMENT '背景定位',
  `style_type` tinyint(2) NOT NULL COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903',
  `multi_time_style` text COMMENT '多时段样式|田海深|201800903',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830',
  `page_url` varchar(255) NOT NULL DEFAULT '' COMMENT '页面访问URL',
  `end_url` varchar(255) NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723',
  `redirect_url` varchar(255) NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806',
  `custom_css` text COMMENT '自定义CSS样式',
  `statistics_code` text COMMENT '统计代码',
  `local_files` text COMMENT '本地文件存储路径json格式（可能有多个文件）',
  `s3_files` text COMMENT '文件在S3上存储路径json格式（可能有多个文件）',
  `goods_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '推送网站状态0未推送 1推送成功',
  `share_image` varchar(255) NOT NULL DEFAULT '' COMMENT '分享图片',
  `share_title` varchar(255) NOT NULL DEFAULT '' COMMENT '分享标题',
  `share_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '分享描述',
  `share_link` varchar(255) NOT NULL DEFAULT '' COMMENT '分享链接',
  `share_place` tinytext COMMENT '分享渠道',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '页面数据版本',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=9088 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面语言配置项表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for rg_page_layout_component
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_layout_component`;
CREATE TABLE `rg_page_layout_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言|夏人杰|20180709',
  `page_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`,`next_id`) USING BTREE,
  KEY `idx_next_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=57137 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for rg_page_layout_data
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_layout_data`;
CREATE TABLE `rg_page_layout_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text COMMENT '组件内容数据，json格式',
  `custom_css` text COMMENT '自定义CSS',
  `background_color` varchar(10) NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  `style_data` text COMMENT '配置样式数据',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_component_id` (`component_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=56859 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='layout组件数据表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for rg_page_publish_cache
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_publish_cache`;
CREATE TABLE `rg_page_publish_cache` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `html` mediumtext COMMENT '不包含网采商品的html',
  `html_network` mediumtext COMMENT '包含网采商品的html',
  `layout` mediumtext COMMENT '排好序的Layout组件列表',
  `uilist` mediumtext COMMENT 'UI组件列表',
  `js` mediumtext COMMENT '页面打包js',
  `css` mediumtext COMMENT '页面打包css',
  `customJs` mediumtext COMMENT '自定义Js',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0-未启用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面内容缓存记录|滕家顺|2018-07-30';

-- ----------------------------
-- Table structure for rg_page_publish_log
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_publish_log`;
CREATE TABLE `rg_page_publish_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `log_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '日志类型，1-缓存文件生成日志，2-发布S3日志',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180727',
  `action_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '操作类型（1-上线，2-下线）',
  `file_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `file_type` varchar(8) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `file_size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小，单位B',
  `file_hash` varchar(32) NOT NULL DEFAULT '' COMMENT '文件hash',
  `include_network` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否包含网采商品',
  `local_path` varchar(255) NOT NULL DEFAULT '' COMMENT '本地文件相对路径',
  `s3_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'S3上存储绝对路径',
  `diff` varchar(2000) NOT NULL DEFAULT '' COMMENT '差异对比json结果',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ip|ip2long|滕家顺|20180917',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `rollback_user` varchar(32) NOT NULL DEFAULT '' COMMENT '回滚操作人|夏人杰|20190610',
  `rollback_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回滚时间|夏人杰|20190610',
  `online_user` varchar(32) NOT NULL DEFAULT '' COMMENT '上线操作人|夏人杰|20190610',
  `online_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上线时间|夏人杰|20190610',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`,`file_type`,`file_hash`) USING BTREE,
  KEY `idx_file_hash_type` (`file_hash`,`file_type`) USING BTREE,
  KEY `idx_version_page_id` (`version`,`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2474710 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面发布日志|滕家顺|2018-05-03';

-- ----------------------------
-- Table structure for rg_page_template
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_template`;
CREATE TABLE `rg_page_template` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '页面模板名称',
  `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704 3.推广页',
  `platform_type` tinyint(2) NOT NULL COMMENT '平台类型 1:PC; 2:Wap; 3:App|田海深|20180817',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '模板预览图',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `range` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '适用范围|1PC端,2WAP端,3响应式',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `custom_css` text COMMENT '页面自定义样式',
  `layout` text COMMENT '模板布局组件信息',
  `layout_data` text COMMENT '模板布局数据',
  `ui` text COMMENT '模板UI组件信息',
  `ui_data` mediumtext COMMENT '模板UI组件数据',
  `tpl_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板类型：1-公用模板；2-私有模板',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认|0否,1是',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '更信任',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `idx_site_code` (`site_code`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=925 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面模板';

-- ----------------------------
-- Table structure for rg_page_ui_component
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_ui_component`;
CREATE TABLE `rg_page_ui_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  `bind_relation` varchar(100) NOT NULL DEFAULT '' COMMENT '三端绑定关系',
  `async_data_format` text COMMENT '组件自动刷新数据格式|田海深|20190627',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_layout_id_next_id_position` (`layout_id`,`next_id`,`position`,`lang`) USING BTREE,
  KEY `idx_pred_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=138338 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件与UI组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for rg_page_ui_component_data
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_ui_component_data`;
CREATE TABLE `rg_page_ui_component_data` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2617580 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='UI组件数据表|黄志辉|2018-07-04';

-- ----------------------------
-- Table structure for rg_page_ui_template
-- ----------------------------
DROP TABLE IF EXISTS `rg_page_ui_template`;
CREATE TABLE `rg_page_ui_template` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `website_code` varchar(5) NOT NULL COMMENT '网站简码，如： zf/rg/dl',
  `platform_code` varchar(5) NOT NULL COMMENT '平台简码，如： pc/wap/app',
  `page_id` bigint(11) unsigned NOT NULL COMMENT '原页面ID',
  `place_type` tinyint(2) NOT NULL COMMENT '应用场景1：活动页 2：首页| 3.推广页',
  `lang` varchar(5) NOT NULL COMMENT '语言代码简称，如：en/zh/de',
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `pic_url` varchar(255) DEFAULT NULL COMMENT '模板预览图',
  `ui_key` varchar(20) NOT NULL COMMENT '组件key,如：U00001',
  `tpl_id` bigint(11) unsigned NOT NULL COMMENT '组件模板id',
  `ui` mediumtext COMMENT '组件|田海深|20190627',
  `ui_data` mediumtext COMMENT '组件数据',
  `view_type` tinyint(2) unsigned NOT NULL COMMENT '查看类型：1-公用模板；2-私有模板',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除(0否,1是)',
  `create_user` varchar(50) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(50) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `used_count` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_platform_place` (`platform_code`,`place_type`) USING BTREE,
  KEY `idx_create_user` (`create_user`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8 COMMENT='组件模板';

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色id|自增',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '角色名称',
  `department_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门id',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点code',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '启用状态|0未启用,1已启用',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `parent_id` int(10) NOT NULL DEFAULT '0' COMMENT '父id，标识自动创建的来源',
  `need_auto_create` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要自动创建|0否，1是',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '角色创建人',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后更新人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `site_code` (`site_code`) USING BTREE,
  KEY `create_user` (`create_user`) USING BTREE,
  KEY `update_user` (`update_user`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=396 DEFAULT CHARSET=utf8 COMMENT='角色表|朱国强|2018-03-19';

-- ----------------------------
-- Table structure for role_privilege
-- ----------------------------
DROP TABLE IF EXISTS `role_privilege`;
CREATE TABLE `role_privilege` (
  `role_id` int(10) unsigned NOT NULL COMMENT '角色与权限关联ID',
  `privilege_id` int(10) unsigned NOT NULL COMMENT '权限ID|menu.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  UNIQUE KEY `uk_role_id` (`role_id`,`privilege_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色与权限关联表|朱国强|2018-03-19';

-- ----------------------------
-- Table structure for setting
-- ----------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `group_id` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  `key` varchar(30) NOT NULL DEFAULT '' COMMENT '键',
  `value` text NOT NULL COMMENT '值',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group` (`group_id`,`key`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='站点配置|马善灵|2017-01-11';

-- ----------------------------
-- Table structure for site_head_footer_replace_log
-- ----------------------------
DROP TABLE IF EXISTS `site_head_footer_replace_log`;
CREATE TABLE `site_head_footer_replace_log` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '站点头尾部更新数据自增ID|夏人杰|20180625',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点|夏人杰|20180625',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言|夏人杰|20180625',
  `md5` varchar(32) NOT NULL DEFAULT '' COMMENT '头尾部md5值|夏人杰|20180625',
  `desc` blob COMMENT '头尾部内容|夏人杰|20180625',
  `is_use` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '当前是否使用|0：否 1：是 2：历史版本|夏人杰|20180625',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间|夏人杰|20180625',
  PRIMARY KEY (`id`),
  KEY `site_lang_use_idx` (`site_code`,`lang`,`is_use`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8 COMMENT='站点头尾部更新检测记录表|夏人杰|20180625';

-- ----------------------------
-- Table structure for site_update_log
-- ----------------------------
DROP TABLE IF EXISTS `site_update_log`;
CREATE TABLE `site_update_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_code` varchar(16) NOT NULL DEFAULT '' COMMENT '站点siteCode',
  `place` tinyint(1) NOT NULL DEFAULT '1' COMMENT '组件应用场景1：活动页 2：首页|滕家顺|20180731',
  `page_ids` varchar(512) NOT NULL DEFAULT '' COMMENT '页面ID，多个用英文逗号分隔',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '基于父记录的ID',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态，0-未开始，1-进行中，2-已完成',
  `result` varchar(255) NOT NULL DEFAULT '' COMMENT '总的结果描述',
  `detail` mediumtext COMMENT '详情',
  `complete_time` int(11) NOT NULL DEFAULT '0' COMMENT '完成时间戳',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_site_code_parent_id` (`site_code`,`parent_id`) USING BTREE,
  KEY `idx_parent_id` (`parent_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1276 DEFAULT CHARSET=utf8 COMMENT='站点更新日志|滕家顺|2018-06-12';

-- ----------------------------
-- Table structure for soa_ips_activity_sku
-- ----------------------------
DROP TABLE IF EXISTS `soa_ips_activity_sku`;
CREATE TABLE `soa_ips_activity_sku` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ips_activity_id` bigint(11) unsigned NOT NULL COMMENT '选品系统子活动ID',
  `sku_update_time` int(10) unsigned NOT NULL COMMENT '选品系统子活动SKU更新时间',
  `sku_list` text COMMENT '选品系统子活动已选SKU',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ips_activity_id` (`ips_activity_id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4401 DEFAULT CHARSET=utf8 COMMENT='选品系统子活动已选SKU缓存表';

-- ----------------------------
-- Table structure for soa_ips_goods
-- ----------------------------
DROP TABLE IF EXISTS `soa_ips_goods`;
CREATE TABLE `soa_ips_goods` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ips_activity_id` bigint(11) unsigned NOT NULL COMMENT '选品系统子活动ID',
  `website_code` varchar(5) NOT NULL COMMENT '网站简码；如:zf/rg',
  `rule_type` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '规则类型 1：自动；2：手动',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动页面ID|page.id',
  `lang` varchar(6) NOT NULL COMMENT '语言简称，如：zh/en/de',
  `component_id` int(10) unsigned NOT NULL COMMENT 'UI组件ID|page_ui_component.id',
  `component_key` varchar(8) NOT NULL COMMENT 'UI组件身份唯一编码|component.component_key',
  `goods_sku` text COMMENT '选品系统活动SKU最新列表',
  `last_update_time` int(10) NOT NULL COMMENT '选品系统活动SKU最后更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_ips_activity_id` (`ips_activity_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE,
  KEY `idx_website_code` (`website_code`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=12153 DEFAULT CHARSET=utf8 COMMENT='选品系统商品对接表|田海深|2018-09-04'';';

-- ----------------------------
-- Table structure for soa_ips_queue
-- ----------------------------
DROP TABLE IF EXISTS `soa_ips_queue`;
CREATE TABLE `soa_ips_queue` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `message` mediumtext COMMENT 'MQ消息',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `run_time` int(10) unsigned DEFAULT NULL COMMENT '处理开始时间',
  `end_time` int(10) unsigned DEFAULT NULL COMMENT '处理结束时间',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态(0: 待处理； 1: 处理成功; 2: 处理失败)',
  `result` text COMMENT '处理结果信息',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4324 DEFAULT CHARSET=utf8 COMMENT='选品系统MQ队列消息缓存表|田海深|2019-06-14';

-- ----------------------------
-- Table structure for soa_obs_goods
-- ----------------------------
DROP TABLE IF EXISTS `soa_obs_goods`;
CREATE TABLE `soa_obs_goods` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `theme_id` bigint(11) unsigned NOT NULL COMMENT 'OBS主题ID',
  `theme_name` varchar(100) NOT NULL DEFAULT '' COMMENT '板块名称',
  `page_id` int(11) unsigned NOT NULL COMMENT 'OBS页面ID',
  `page_name` varchar(100) NOT NULL DEFAULT '' COMMENT '页面名称',
  `section_id` int(11) unsigned NOT NULL COMMENT 'OBS版块ID',
  `section_name` varchar(100) NOT NULL DEFAULT '' COMMENT '板块名称',
  `activity_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动ID',
  `pid` int(11) unsigned NOT NULL COMMENT '页面ID',
  `lang` varchar(6) NOT NULL COMMENT '语言简称，如：zh/en/de',
  `platform` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端,3app端',
  `component_id` int(10) unsigned NOT NULL COMMENT 'UI组件ID|page_ui_component.id',
  `component_key` varchar(8) NOT NULL COMMENT 'UI组件身份唯一编码|component.component_key',
  `goods_sku` text COMMENT '产品最新列表',
  `last_update_time` int(10) NOT NULL DEFAULT '0' COMMENT '产品最后更新时间',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`) USING BTREE,
  KEY `section_id` (`section_id`) USING BTREE,
  KEY `theme_id` (`theme_id`),
  KEY `page_id` (`page_id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1301 DEFAULT CHARSET=utf8 COMMENT='OBS版块关系对照表';

-- ----------------------------
-- Table structure for suk_activity
-- ----------------------------
DROP TABLE IF EXISTS `suk_activity`;
CREATE TABLE `suk_activity` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `group_id` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动分组ID|activity_group.id',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道简码,多个渠道以英文逗号分隔',
  `lang` varchar(2047) NOT NULL DEFAULT '' COMMENT '活动所属语言，多个语系以英文逗号分隔，如zh,en,de',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '活动名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '活动简介',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态|1待上线,2已上线,3待下线,4已下线',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否，1是',
  `is_lock` tinyint(2) unsigned NOT NULL COMMENT '是否加锁 0：否 1：是|夏人杰|20180609',
  `is_frequently` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用活动(0 - 不是； 1 - 是)|田海深|20190904',
  `mold` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动类型 1 普通活动 2 活动推广',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_site` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2436 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for suk_activity_group
-- ----------------------------
DROP TABLE IF EXISTS `suk_activity_group`;
CREATE TABLE `suk_activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` text NOT NULL COMMENT '支持语言列表',
  `support_list` text NOT NULL COMMENT '支持端口渠道语言列表',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=684 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动分组|田海深|20180913';

-- ----------------------------
-- Table structure for suk_page
-- ----------------------------
DROP TABLE IF EXISTS `suk_page`;
CREATE TABLE `suk_page` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `group_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为在链接中展示使用，区别于其它表的page_id，此处取名pid',
  `activity_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联的活动ID|activity.id',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180709',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面状态|1待上线,2已上线,3待下线,4已下线',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端,3app端|夏人杰|20180709',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '加解锁 1：解锁 2：加锁|夏人杰|20180706',
  `auto_refresh` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要自动刷新',
  `refresh_time` int(11) NOT NULL DEFAULT '0' COMMENT '自动刷新时间间隔（0-不自动刷新）',
  `end_time` int(11) unsigned NOT NULL COMMENT '子页面活动下架时间|田海深|20180723',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `is_blog` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否博客页面|田海深|20190321',
  `home_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '首页类型 0：A首页 1：B首页|夏人杰|20190610',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
  `default_lang` varchar(6) NOT NULL DEFAULT '' COMMENT '默认语言简称，如：zh/en/de',
  `is_redirect_country` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否国家站自动跳转(0 - 不跳转； 1 - 跳转)|田海深|20190427',
  `is_native` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否原生专题 0：否 1：是|夏人杰|20190821',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '页面数据版本',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_pid` (`pid`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for suk_page_convert_relation
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_convert_relation`;
CREATE TABLE `suk_page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=926 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面转换关系表|滕家顺|2018-07-04';

-- ----------------------------
-- Table structure for suk_page_group
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_group`;
CREATE TABLE `suk_page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动子页面分组|田海深|20180913';

-- ----------------------------
-- Table structure for suk_page_language
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_language`;
CREATE TABLE `suk_page_language` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID|page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '页面title',
  `tpl_id` int(10) NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id|夏人杰|20180709',
  `seo_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO标题|滕家顺|20180814',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '页面keywords',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '页面描述',
  `background_color` varchar(12) NOT NULL DEFAULT '' COMMENT '背景颜色',
  `background_image` varchar(128) NOT NULL DEFAULT '' COMMENT '背景图片',
  `background_position` varchar(32) NOT NULL DEFAULT '' COMMENT '背景位置',
  `background_repeat` varchar(32) NOT NULL DEFAULT '' COMMENT '背景定位',
  `style_type` tinyint(2) NOT NULL COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903',
  `multi_time_style` text COMMENT '多时段样式|田海深|201800903',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830',
  `page_url` varchar(255) NOT NULL DEFAULT '' COMMENT '页面访问URL',
  `end_url` varchar(255) NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723',
  `redirect_url` varchar(255) NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806',
  `custom_css` text COMMENT '自定义CSS样式',
  `statistics_code` text COMMENT '统计代码',
  `local_files` text COMMENT '本地文件存储路径json格式（可能有多个文件）',
  `s3_files` text COMMENT '文件在S3上存储路径json格式（可能有多个文件）',
  `goods_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '推送网站状态0未推送 1推送成功',
  `share_image` varchar(255) NOT NULL DEFAULT '' COMMENT '分享图片',
  `share_title` varchar(255) NOT NULL DEFAULT '' COMMENT '分享标题',
  `share_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '分享描述',
  `share_link` varchar(255) NOT NULL DEFAULT '' COMMENT '分享链接',
  `share_place` tinytext COMMENT '分享渠道',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '页面数据版本',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面语言配置项表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for suk_page_layout_component
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_layout_component`;
CREATE TABLE `suk_page_layout_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言|夏人杰|20180709',
  `page_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`,`next_id`) USING BTREE,
  KEY `idx_next_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=52748 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for suk_page_layout_data
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_layout_data`;
CREATE TABLE `suk_page_layout_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text COMMENT '组件内容数据，json格式',
  `custom_css` text COMMENT '自定义CSS',
  `background_color` varchar(10) NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  `style_data` text COMMENT '配置样式数据',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_component_id` (`component_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=52470 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='layout组件数据表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for suk_page_publish_cache
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_publish_cache`;
CREATE TABLE `suk_page_publish_cache` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `html` mediumtext COMMENT '不包含网采商品的html',
  `html_network` mediumtext COMMENT '包含网采商品的html',
  `layout` mediumtext COMMENT '排好序的Layout组件列表',
  `uilist` mediumtext COMMENT 'UI组件列表',
  `js` mediumtext COMMENT '页面打包js',
  `css` mediumtext COMMENT '页面打包css',
  `customJs` mediumtext COMMENT '自定义Js',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0-未启用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面内容缓存记录|滕家顺|2018-07-30';

-- ----------------------------
-- Table structure for suk_page_publish_log
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_publish_log`;
CREATE TABLE `suk_page_publish_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `log_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '日志类型，1-缓存文件生成日志，2-发布S3日志',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180727',
  `action_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '操作类型（1-上线，2-下线）',
  `file_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `file_type` varchar(8) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `file_size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小，单位B',
  `file_hash` varchar(32) NOT NULL DEFAULT '' COMMENT '文件hash',
  `include_network` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否包含网采商品',
  `local_path` varchar(255) NOT NULL DEFAULT '' COMMENT '本地文件相对路径',
  `s3_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'S3上存储绝对路径',
  `diff` varchar(2000) NOT NULL DEFAULT '' COMMENT '差异对比json结果',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ip|ip2long|滕家顺|20180917',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `rollback_user` varchar(32) NOT NULL DEFAULT '' COMMENT '回滚操作人|夏人杰|20190610',
  `rollback_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回滚时间|夏人杰|20190610',
  `online_user` varchar(32) NOT NULL DEFAULT '' COMMENT '上线操作人|夏人杰|20190610',
  `online_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上线时间|夏人杰|20190610',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`,`file_type`,`file_hash`) USING BTREE,
  KEY `idx_file_hash_type` (`file_hash`,`file_type`) USING BTREE,
  KEY `idx_version_page_id` (`version`,`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2420205 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面发布日志|滕家顺|2018-05-03';

-- ----------------------------
-- Table structure for suk_page_template
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_template`;
CREATE TABLE `suk_page_template` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '页面模板名称',
  `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704 3.推广页',
  `platform_type` tinyint(2) NOT NULL COMMENT '平台类型 1:PC; 2:Wap; 3:App|田海深|20180817',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '模板预览图',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `range` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '适用范围|1PC端,2WAP端,3响应式',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `custom_css` text COMMENT '页面自定义样式',
  `layout` text COMMENT '模板布局组件信息',
  `layout_data` text COMMENT '模板布局数据',
  `ui` text COMMENT '模板UI组件信息',
  `ui_data` mediumtext COMMENT '模板UI组件数据',
  `tpl_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板类型：1-公用模板；2-私有模板',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认|0否,1是',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '更信任',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `idx_site_code` (`site_code`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=924 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面模板';

-- ----------------------------
-- Table structure for suk_page_ui_component
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_ui_component`;
CREATE TABLE `suk_page_ui_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  `bind_relation` varchar(100) NOT NULL DEFAULT '' COMMENT '三端绑定关系',
  `async_data_format` text COMMENT '组件自动刷新数据格式|田海深|20190627',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_layout_id_next_id_position` (`layout_id`,`next_id`,`position`,`lang`) USING BTREE,
  KEY `idx_pred_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=127278 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件与UI组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for suk_page_ui_component_data
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_ui_component_data`;
CREATE TABLE `suk_page_ui_component_data` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2417850 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='UI组件数据表|黄志辉|2018-07-04';

-- ----------------------------
-- Table structure for suk_page_ui_template
-- ----------------------------
DROP TABLE IF EXISTS `suk_page_ui_template`;
CREATE TABLE `suk_page_ui_template` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `website_code` varchar(5) NOT NULL COMMENT '网站简码，如： zf/rg/dl',
  `platform_code` varchar(5) NOT NULL COMMENT '平台简码，如： pc/wap/app',
  `page_id` bigint(11) unsigned NOT NULL COMMENT '原页面ID',
  `place_type` tinyint(2) NOT NULL COMMENT '应用场景1：活动页 2：首页| 3.推广页',
  `lang` varchar(5) NOT NULL COMMENT '语言代码简称，如：en/zh/de',
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `pic_url` varchar(255) DEFAULT NULL COMMENT '模板预览图',
  `ui_key` varchar(20) NOT NULL COMMENT '组件key,如：U00001',
  `tpl_id` bigint(11) unsigned NOT NULL COMMENT '组件模板id',
  `ui` mediumtext COMMENT '组件|田海深|20190627',
  `ui_data` mediumtext COMMENT '组件数据',
  `view_type` tinyint(2) unsigned NOT NULL COMMENT '查看类型：1-公用模板；2-私有模板',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除(0否,1是)',
  `create_user` varchar(50) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(50) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `used_count` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_platform_place` (`platform_code`,`place_type`) USING BTREE,
  KEY `idx_create_user` (`create_user`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='组件模板';

-- ----------------------------
-- Table structure for sys_api_monitor
-- ----------------------------
DROP TABLE IF EXISTS `sys_api_monitor`;
CREATE TABLE `sys_api_monitor` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_code` varchar(10) NOT NULL COMMENT '站点简码,如:zf-pc',
  `api_key` varchar(100) NOT NULL COMMENT 'API接口在配置文件里的数组下标，如:goods_async_detail',
  `fail_url` text COMMENT 'API请求异常完整地址',
  `fail_message` text COMMENT '异常信息',
  `fail_count` int(10) unsigned NOT NULL COMMENT '异常次数',
  `process_status` tinyint(2) unsigned NOT NULL COMMENT '处理状态(0: 未处理；1:处理中 2:处理完成)',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_site_code_api_key` (`site_code`,`api_key`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组件异步API监控表|田海深|20191014';

-- ----------------------------
-- Table structure for sys_config
-- ----------------------------
DROP TABLE IF EXISTS `sys_config`;
CREATE TABLE `sys_config` (
  `option_key` varchar(100) NOT NULL COMMENT '选项键',
  `option_value` text COMMENT '选项值',
  `option_desc` varchar(255) DEFAULT NULL COMMENT '选项描述',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`option_key`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统设置表|田海深|20191014';

-- ----------------------------
-- Table structure for sys_request_log
-- ----------------------------
DROP TABLE IF EXISTS `sys_request_log`;
CREATE TABLE `sys_request_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `request_id` varchar(32) NOT NULL COMMENT '请求唯一ID',
  `website_code` varchar(5) NOT NULL COMMENT '网站编码',
  `request_date` date NOT NULL COMMENT '请求日期',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `page_ids` varchar(255) NOT NULL DEFAULT '0' COMMENT '操作活动页面ID',
  `module` varchar(50) NOT NULL COMMENT '模块名称',
  `request_route` varchar(100) NOT NULL COMMENT '请求路由',
  `request_url` varchar(255) NOT NULL DEFAULT '0' COMMENT '操作活动页面ID',
  `method` varchar(10) NOT NULL COMMENT '请求方法',
  `request_time` int(10) unsigned NOT NULL COMMENT '请求时间',
  `user_ip` varchar(255) NOT NULL COMMENT '用户IP',
  `post_params` text COMMENT 'json格式POST参数',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_request_id` (`request_id`),
  KEY `idx_code_date` (`website_code`,`request_date`) USING BTREE,
  KEY `idx_username` (`username`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2438427 DEFAULT CHARSET=utf8 COMMENT='系统-用户访问日志';

-- ----------------------------
-- Table structure for ui_component
-- ----------------------------
DROP TABLE IF EXISTS `ui_component`;
CREATE TABLE `ui_component` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '组件分类id',
  `is_custom` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否自定义|0否,1是',
  `range` tinyint(2) unsigned NOT NULL COMMENT '适用范围|1PC端,2WAP端,3响应式',
  `component_key` varchar(10) NOT NULL DEFAULT '0' COMMENT '组件身份编码|第一位:[组件类型(U为UI)],后面六位自增数字序号)',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '组件名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '组件描述',
  `logo_url` varchar(255) DEFAULT '' COMMENT '组件logo',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组件默认模板ID',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '组件状态|1预申请,2待上线,3已上线,4待下线,5已下线',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '5' COMMENT '组件审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `need_navigate` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否添加到导航栏|0否,1是',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '组件icon|夏人杰|20190821',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_component_key` (`component_key`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=258 DEFAULT CHARSET=utf8 COMMENT='UI组件表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for ui_component_language_relation
-- ----------------------------
DROP TABLE IF EXISTS `ui_component_language_relation`;
CREATE TABLE `ui_component_language_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `lang_key` varchar(100) NOT NULL DEFAULT '' COMMENT '多语言key|夏人杰|20190624',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板ID|夏人杰|20190624',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_tepl_id` (`tpl_id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组件多语言关联表|夏人杰|20190624';

-- ----------------------------
-- Table structure for ui_component_tpl
-- ----------------------------
DROP TABLE IF EXISTS `ui_component_tpl`;
CREATE TABLE `ui_component_tpl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_en` varchar(50) NOT NULL DEFAULT '' COMMENT '模板英文名称',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '模板中文名称',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否启用|0否 1是',
  `is_vue_ssr` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否使用node渲染 0：否 1：是|夏人杰|20190424',
  `is_async` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '是否异步组件模板 (0: 否;1: 是)|田海深|20191007',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '模板预览图',
  `component_key` varchar(10) NOT NULL COMMENT '组件编码',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除 1：已删除 0：未删除|夏人杰|20180524',
  `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_key` (`component_key`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=569 DEFAULT CHARSET=utf8 COMMENT='UI组件模板表|朱国强|2018-04-24';

-- ----------------------------
-- Table structure for ui_component_tpl_relation
-- ----------------------------
DROP TABLE IF EXISTS `ui_component_tpl_relation`;
CREATE TABLE `ui_component_tpl_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tpl_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件模板ID|ui_component_tpl.id',
  `relation_tpl_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联的组件模板ID|ui_component_tpl.id',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用，0-禁用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_relation_tpl_id_tpl_id` (`relation_tpl_id`,`tpl_id`) USING BTREE,
  UNIQUE KEY `uk_tpl_id` (`tpl_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8 COMMENT='组件模板关联关系|滕家顺|2018-07-07';

-- ----------------------------
-- Table structure for zf_activity
-- ----------------------------
DROP TABLE IF EXISTS `zf_activity`;
CREATE TABLE `zf_activity` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `group_id` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动分组ID|activity_group.id',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道简码,多个渠道以英文逗号分隔',
  `lang` varchar(2047) NOT NULL DEFAULT '' COMMENT '活动所属语言，多个语系以英文逗号分隔，如zh,en,de',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '活动名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '活动简介',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态|1待上线,2已上线,3待下线,4已下线',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否，1是',
  `is_lock` tinyint(2) unsigned NOT NULL COMMENT '是否加锁 0：否 1：是|夏人杰|20180609',
  `is_frequently` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用活动(0 - 不是； 1 - 是)|田海深|20190904',
  `mold` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动类型 1 普通活动 2 活动推广',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_site` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=5193 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for zf_activity_group
-- ----------------------------
DROP TABLE IF EXISTS `zf_activity_group`;
CREATE TABLE `zf_activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` text NOT NULL COMMENT '支持语言列表',
  `support_list` text NOT NULL COMMENT '支持端口渠道语言列表',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3469 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动分组|田海深|20180913';

-- ----------------------------
-- Table structure for zf_page
-- ----------------------------
DROP TABLE IF EXISTS `zf_page`;
CREATE TABLE `zf_page` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `group_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为在链接中展示使用，区别于其它表的page_id，此处取名pid',
  `activity_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联的活动ID|activity.id',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180709',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面状态|1待上线,2已上线,3待下线,4已下线',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端,3app端|夏人杰|20180709',
  `verify_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '页面审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '加解锁 1：解锁 2：加锁|夏人杰|20180706',
  `auto_refresh` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要自动刷新',
  `refresh_time` int(11) NOT NULL DEFAULT '0' COMMENT '自动刷新时间间隔（0-不自动刷新）',
  `end_time` int(11) unsigned NOT NULL COMMENT '子页面活动下架时间|田海深|20180723',
  `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `is_blog` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否博客页面|田海深|20190321',
  `home_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '首页类型 0：A首页 1：B首页|夏人杰|20190610',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
  `default_lang` varchar(6) NOT NULL DEFAULT '' COMMENT '默认语言简称，如：zh/en/de',
  `is_redirect_country` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否国家站自动跳转(0 - 不跳转； 1 - 跳转)|田海深|20190427',
  `is_native` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否原生专题 0：否 1：是|夏人杰|20190821',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '页面数据版本',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_pid` (`pid`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=87253 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for zf_page_convert_relation
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_convert_relation`;
CREATE TABLE `zf_page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4432 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面转换关系表|滕家顺|2018-07-04';

-- ----------------------------
-- Table structure for zf_page_group
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_group`;
CREATE TABLE `zf_page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=67616 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动子页面分组|田海深|20180913';

-- ----------------------------
-- Table structure for zf_page_language
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_language`;
CREATE TABLE `zf_page_language` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID|page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '页面title',
  `tpl_id` int(10) NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id|夏人杰|20180709',
  `seo_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO标题|滕家顺|20180814',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '页面keywords',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '页面描述',
  `background_color` varchar(12) NOT NULL DEFAULT '' COMMENT '背景颜色',
  `background_image` varchar(128) NOT NULL DEFAULT '' COMMENT '背景图片',
  `background_position` varchar(32) NOT NULL DEFAULT '' COMMENT '背景位置',
  `background_repeat` varchar(32) NOT NULL DEFAULT '' COMMENT '背景定位',
  `style_type` tinyint(2) NOT NULL COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903',
  `multi_time_style` text COMMENT '多时段样式|田海深|201800903',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830',
  `page_url` varchar(255) NOT NULL DEFAULT '' COMMENT '页面访问URL',
  `end_url` varchar(255) NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723',
  `redirect_url` varchar(255) NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806',
  `custom_css` text COMMENT '自定义CSS样式',
  `statistics_code` text COMMENT '统计代码',
  `local_files` text COMMENT '本地文件存储路径json格式（可能有多个文件）',
  `s3_files` text COMMENT '文件在S3上存储路径json格式（可能有多个文件）',
  `goods_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编码',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '推送网站状态0未推送 1推送成功',
  `share_image` varchar(255) NOT NULL DEFAULT '' COMMENT '分享图片',
  `share_title` varchar(255) NOT NULL DEFAULT '' COMMENT '分享标题',
  `share_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '分享描述',
  `share_link` varchar(255) NOT NULL DEFAULT '' COMMENT '分享链接',
  `share_place` tinytext COMMENT '分享渠道',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '页面数据版本',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=102349 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面语言配置项表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for zf_page_layout_component
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_layout_component`;
CREATE TABLE `zf_page_layout_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言|夏人杰|20180709',
  `page_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`,`next_id`) USING BTREE,
  KEY `idx_next_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=343387 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for zf_page_layout_data
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_layout_data`;
CREATE TABLE `zf_page_layout_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text COMMENT '组件内容数据，json格式',
  `custom_css` text COMMENT '自定义CSS',
  `background_color` varchar(10) NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  `style_data` text COMMENT '配置样式数据',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_component_id` (`component_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=343081 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='layout组件数据表|滕家顺|2018-04-18';

-- ----------------------------
-- Table structure for zf_page_operate_log
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_operate_log`;
CREATE TABLE `zf_page_operate_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `page_group_id` varchar(32) NOT NULL DEFAULT '' COMMENT '页面分组id',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:保存, 3:绑定、4:同步',
  `content` text COMMENT '日志内容数据',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=658 DEFAULT CHARSET=utf8 COMMENT='三端绑定操作日志';

-- ----------------------------
-- Table structure for zf_page_publish_cache
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_publish_cache`;
CREATE TABLE `zf_page_publish_cache` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `html` mediumtext COMMENT '不包含网采商品的html',
  `html_network` mediumtext COMMENT '包含网采商品的html',
  `layout` mediumtext COMMENT '排好序的Layout组件列表',
  `uilist` mediumtext COMMENT 'UI组件列表',
  `js` mediumtext COMMENT '页面打包js',
  `css` mediumtext COMMENT '页面打包css',
  `customJs` mediumtext COMMENT '自定义Js',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0-未启用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=187194 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面内容缓存记录|滕家顺|2018-07-30';

-- ----------------------------
-- Table structure for zf_page_publish_log
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_publish_log`;
CREATE TABLE `zf_page_publish_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `log_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '日志类型，1-缓存文件生成日志，2-发布S3日志',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180727',
  `action_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '操作类型（1-上线，2-下线）',
  `file_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `file_type` varchar(8) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `file_size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小，单位B',
  `file_hash` varchar(32) NOT NULL DEFAULT '' COMMENT '文件hash',
  `include_network` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否包含网采商品',
  `local_path` varchar(255) NOT NULL DEFAULT '' COMMENT '本地文件相对路径',
  `s3_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'S3上存储绝对路径',
  `diff` varchar(2000) NOT NULL DEFAULT '' COMMENT '差异对比json结果',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ip|ip2long|滕家顺|20180917',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `rollback_user` varchar(32) NOT NULL DEFAULT '' COMMENT '回滚操作人|夏人杰|20190610',
  `rollback_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回滚时间|夏人杰|20190610',
  `online_user` varchar(32) NOT NULL DEFAULT '' COMMENT '上线操作人|夏人杰|20190610',
  `online_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上线时间|夏人杰|20190610',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`,`file_type`,`file_hash`) USING BTREE,
  KEY `idx_file_hash_type` (`file_hash`,`file_type`) USING BTREE,
  KEY `idx_version_page_id` (`version`,`page_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4959539 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面发布日志|滕家顺|2018-05-03';

-- ----------------------------
-- Table structure for zf_page_sync_platform_wait_data
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_sync_platform_wait_data`;
CREATE TABLE `zf_page_sync_platform_wait_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `page_group_id` varchar(32) NOT NULL DEFAULT '' COMMENT '页面组id',
  `pipeline` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道，字符串多个用，隔开',
  `platform` varchar(255) NOT NULL DEFAULT '' COMMENT '平台，多个用，隔开',
  `is_cover` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否覆盖，不覆盖: 0 ，覆盖：1',
  `form_data` text COMMENT '表单内容',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COMMENT='zaful同步平台数据缓存表';

-- ----------------------------
-- Table structure for zf_page_template
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_template`;
CREATE TABLE `zf_page_template` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '页面模板名称',
  `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704 3.推广页',
  `platform_type` tinyint(2) NOT NULL COMMENT '平台类型 1:PC; 2:Wap; 3:App|田海深|20180817',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '模板预览图',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `range` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '适用范围|1PC端,2WAP端,3响应式',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `custom_css` text COMMENT '页面自定义样式',
  `layout` text COMMENT '模板布局组件信息',
  `layout_data` text COMMENT '模板布局数据',
  `ui` text COMMENT '模板UI组件信息',
  `ui_data` mediumtext COMMENT '模板UI组件数据',
  `tpl_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板类型：1-公用模板；2-私有模板',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认|0否,1是',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除|0否,1是',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '更信任',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `idx_site_code` (`site_code`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2133 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面模板';

-- ----------------------------
-- Table structure for zf_page_ui_component
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_ui_component`;
CREATE TABLE `zf_page_ui_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  `bind_relation` varchar(100) NOT NULL DEFAULT '' COMMENT '三端绑定关系',
  `async_data_format` text COMMENT '组件自动刷新数据格式|田海深|20190627',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_layout_id_next_id_position` (`layout_id`,`next_id`,`position`,`lang`) USING BTREE,
  KEY `idx_pred_id` (`next_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=714066 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件与UI组件关联表|朱国强|2018-03-16';

-- ----------------------------
-- Table structure for zf_page_ui_component_data
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_ui_component_data`;
CREATE TABLE `zf_page_ui_component_data` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=13625220 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='UI组件数据表|黄志辉|2018-07-04';

-- ----------------------------
-- Table structure for zf_page_ui_template
-- ----------------------------
DROP TABLE IF EXISTS `zf_page_ui_template`;
CREATE TABLE `zf_page_ui_template` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `website_code` varchar(5) NOT NULL COMMENT '网站简码，如： zf/rg/dl',
  `platform_code` varchar(5) NOT NULL COMMENT '平台简码，如： pc/wap/app',
  `page_id` bigint(11) unsigned NOT NULL COMMENT '原页面ID',
  `place_type` tinyint(2) NOT NULL COMMENT '应用场景1：活动页 2：首页| 3.推广页',
  `lang` varchar(5) NOT NULL COMMENT '语言代码简称，如：en/zh/de',
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `pic_url` varchar(255) DEFAULT NULL COMMENT '模板预览图',
  `ui_key` varchar(20) NOT NULL COMMENT '组件key,如：U00001',
  `tpl_id` bigint(11) unsigned NOT NULL COMMENT '组件模板id',
  `ui` mediumtext COMMENT '组件|田海深|20190627',
  `ui_data` mediumtext COMMENT '组件数据',
  `view_type` tinyint(2) unsigned NOT NULL COMMENT '查看类型：1-公用模板；2-私有模板',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除(0否,1是)',
  `create_user` varchar(50) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(50) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `used_count` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `sys_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_platform_place` (`platform_code`,`place_type`) USING BTREE,
  KEY `idx_create_user` (`create_user`) USING BTREE,
  KEY `sys_update_time` (`sys_update_time`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=utf8 COMMENT='组件模板';
