#1.0.1版本[2018-05-24 10:56]page表新增url_name字段和相应索引
ALTER TABLE `page` ADD `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题' AFTER `status`;
ALTER TABLE `page` ADD INDEX idx_url_name ( `url_name` );
#1.0.1版本，上线时间5月31日，新增模板删除
ALTER TABLE `ui_component_tpl` ADD `is_delete` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除 1：已删除 0：未删除|夏人杰|20180524';
#修改页面title和keywords字段长度
ALTER TABLE `page_language` MODIFY COLUMN `title` varchar(255) NOT NULL DEFAULT '' COMMENT '页面title', MODIFY COLUMN `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '页面keywords';
#1.1.0版本，上线时间6月22日，新增站点刷新日志表
CREATE TABLE `site_update_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_code` varchar(16) NOT NULL DEFAULT '' COMMENT '站点siteCode',
  `page_ids` varchar(512) NOT NULL DEFAULT '' COMMENT '页面ID，多个用英文逗号分隔',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '基于父记录的ID',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态，0-未开始，1-进行中，2-已完成',
  `result` varchar(255) NOT NULL DEFAULT '' COMMENT '总的结果描述',
  `detail` text COMMENT '详情',
  `complete_time` int(11) NOT NULL DEFAULT '0' COMMENT '完成时间戳',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  PRIMARY KEY (`id`),
  KEY `idx_site_code_parent_id` (`site_code`,`parent_id`) USING BTREE,
  KEY `idx_parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='站点更新日志|滕家顺|2018-06-12';
#新增活动加锁字段
ALTER TABLE `activity` ADD `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否加锁 0：否 1：是|夏人杰|20180609';
#新增一键转APP时记录关联页面关系，选中页面模板id字段
ALTER TABLE `page` ADD `relation_page` varchar(32) NOT NULL DEFAULT '' COMMENT '记录关联页面|夏人杰|20180614',ADD tpl_id int(10) unsigned NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id' after `custom_css`, ADD `refresh_time` int(11) NOT NULL DEFAULT '0' COMMENT '自动刷新时间间隔（0-不自动刷新）' AFTER `auto_refresh`;
#新增常用配置、选中模板id字段，配置component_id, lang, tpl_id联合唯一索引
ALTER TABLE page_ui_data DROP index `uk_component_id_lang`,ADD share_data text COMMENT '常用配置数据，json格式' after `data`,ADD select_tpl_id int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件当前页面选中模板ID',ADD UNIQUE index uk_component_lang_tpl(component_id,lang,tpl_id) USING BTREE;
#新增模板类型，预览pid，lang字段
ALTER TABLE page_template ADD tpl_type tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板类型：1-公用模板；2-私有模板' after `ui_data`,ADD pid varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid' after `id`,ADD `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de' after `site_code`;
#1.1.1版本，上线时间6月29日，页面模板表新增适用范围字段
ALTER TABLE page_template ADD `range` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '适用范围|1PC端,2WAP端,3响应式' AFTER site_code;
#更新适用范围字段默认值
UPDATE page_template SET `range`=2 WHERE LOCATE('wap', site_code)>0;
#头尾部同步解决方案优化
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='站点头尾部更新检测记录表|夏人杰|20180625';
#1.2.0版本，组件表新增模板id字段
ALTER TABLE page_ui_component ADD `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID';
Update page_ui_component as p SET p.tpl_id=(SELECT u.tpl_id FROM ui_component as u WHERE p.component_key=u.component_key) WHERE p.tpl_id =0;
#新增ui组件数据表
CREATE TABLE `page_ui_component_data` (
`id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
`component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
`lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
`key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
`value` text COMMENT '字段value(统一json格式存入)',
`is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
`is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
`is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
`tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
PRIMARY KEY (`id`),
KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='UI组件数据表|黄志辉|2018-07-04';
#新增页面转换关系表
CREATE TABLE `page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='页面转换关系表|滕家顺|2018-07-04';
#新增组件模板关联关系
CREATE TABLE `ui_component_tpl_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tpl_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件模板ID|ui_component_tpl.id',
  `relation_tpl_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联的组件模板ID|ui_component_tpl.id',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用，0-禁用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_relation_tpl_id_tpl_id` (`relation_tpl_id`,`tpl_id`) USING BTREE,
  UNIQUE KEY `uk_tpl_id` (`tpl_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='组件模板关联关系|滕家顺|2018-07-07';
#新增应用场景字段
ALTER TABLE component_category ADD `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704';
ALTER TABLE page_template ADD `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704';
ALTER TABLE ui_component_tpl ADD `place` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704';
ALTER TABLE page ADD `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180709' AFTER `activity_id`;
ALTER TABLE page ADD `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动所属站点类型|1PC端,2wap端,3app端|夏人杰|20180709'
AFTER `status`;
ALTER TABLE page ADD `is_lock` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '加解锁 1：解锁 2：加锁|夏人杰|20180706'
AFTER `custom_css`;
ALTER TABLE page_language ADD `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '页面模板id| page_template.id|夏人杰|20180709'
AFTER `title`;
#页面多语言区分
ALTER TABLE `page_layout_component`
ADD COLUMN `lang`  varchar(6) NOT NULL DEFAULT 'en' COMMENT '语言代码简称，如：en/zh/de'
AFTER `id`,
DROP INDEX `uk_page_id_next_id` ,
ADD UNIQUE INDEX `uk_page_id_next_id` (`page_id`, `next_id`, `lang`) USING BTREE ;

ALTER TABLE `page_layout_data`
ADD COLUMN `lang`  varchar(6) NOT NULL DEFAULT 'en' COMMENT '语言代码简称，如：en/zh/de'
AFTER `component_id`;

ALTER TABLE `page_ui_component`
ADD COLUMN `lang`  varchar(6) NOT NULL DEFAULT 'en' COMMENT '语言代码简称，如：en/zh/de'
AFTER `component_key`,
DROP INDEX `uk_layout_id_next_id_position` ,
ADD UNIQUE INDEX `uk_layout_id_next_id_position` (`layout_id`, `next_id`, `position`,
                                                  `lang`) USING BTREE ;
#商品管理
CREATE TABLE `goods_manage_page` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` varchar(32) NOT NULL COMMENT '分组ID',
  `site_code` varchar(10) NOT NULL COMMENT '站点简称，如：rw-pc/rw-wap',
  `activity_id` int(11) unsigned NOT NULL COMMENT '关联的活动ID|activity.id',
  `page_id` int(11) unsigned NOT NULL COMMENT '页面ID|page.id',
  PRIMARY KEY (`id`),
  KEY `idx_site_code` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品管理页面表|田海深|2018-07-06';

CREATE TABLE `goods_manage_data_block` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `gmp_id` bigint(11) NOT NULL COMMENT '商品管理页面ID|goods_manage_page.id',
  `lang` varchar(6) NOT NULL COMMENT '语言简称，如：zh/en/de',
  `category_title` varchar(255) NOT NULL COMMENT '商品分类的标题名称',
  `more_url` varchar(255) DEFAULT NULL COMMENT 'view more 链接',
  `goods_sku` varchar(255) NOT NULL COMMENT '商品sku列表，多个用英文逗号分隔',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品管理页面数据块表|田海深|2018-07-06';

#发布日志表增加版本号字段
ALTER TABLE `page_publish_log`
ADD COLUMN `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号' AFTER `id`,
ADD INDEX `idx_version_page_id` (`version`, `page_id`) USING BTREE;

#v1.3.0 子页面下线
ALTER TABLE `page`
ADD COLUMN `end_time` int(11) UNSIGNED NOT NULL COMMENT '子页面活动下架时间|田海深|20180723' AFTER `refresh_time`;
ALTER TABLE `page_language`
ADD COLUMN `end_url` varchar(255) NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723' AFTER `page_url`;
UPDATE page AS p SET p.end_time=(SELECT a.end_time FROM activity AS a WHERE a.id=p.activity_id) WHERE p.end_time =0;

#发布日志表增加站点字段
ALTER TABLE `page_publish_log`
ADD COLUMN `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180727' AFTER `lang`;

#增加发布缓存内容表
CREATE TABLE `page_publish_cache` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20) NOT NULL DEFAULT '0' COMMENT '版本号',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID',
  `lang` varchar(5) NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `html` text COMMENT 'HTML内容',
  `js` text COMMENT 'JS内容',
  `css` text COMMENT 'CSS内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0-未启用，1-启用',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  PRIMARY KEY (`id`),
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COMMENT='页面内容缓存记录|滕家顺|2018-07-30';

#站点头尾更新日志增加place字段
ALTER TABLE `site_update_log`
ADD COLUMN `place` tinyint(1) NOT NULL DEFAULT '1' COMMENT '组件应用场景1：活动页 2：首页|滕家顺|20180731' AFTER `site_code`;

#修复page表site_code字段为空的数据(从activity表拿数据去填充)
UPDATE page AS p
LEFT JOIN activity AS a ON p.activity_id = a.id
SET p.site_code = a.site_code
WHERE p.activity_id > 0 AND p.site_code = '';

#增加页面跳转链接
ALTER TABLE `page_language`
ADD COLUMN `redirect_url` varchar(255) NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806' AFTER `end_url`;

#去掉page表的无用字段relation_page
ALTER TABLE `page` DROP COLUMN `relation_page`, DROP COLUMN `custom_css`;

#v1.4.0 页面模板增加平台类型
ALTER TABLE `page_template`
ADD COLUMN `platform_type` tinyint(2) NOT NULL COMMENT '平台类型 1:PC; 2:Wap; 3:App|田海深|20180817' AFTER `place`;
#添加字段platform_type的旧数据处理
UPDATE page_template AS p SET p.platform_type=(SELECT CASE platform_code WHEN 'pc' THEN 1 WHEN 'wap' THEN 2 WHEN 'app' THEN 3 ELSE 0 END p_type FROM (SELECT id, substring_index(site_code, '-', -1) AS platform_code FROM page_template) AS t WHERE t.id=p.id) WHERE p.platform_type=0;

#v1.4.0版本 新增SEO标题字段
ALTER TABLE `page_language`
ADD COLUMN `seo_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO标题，展示在页签上的滕家顺|20180814' AFTER `tpl_id`;

#v1.5.0版本 新增url_name字段
ALTER TABLE `page_language`
ADD COLUMN `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830' AFTER `background_repeat`;

#(历史数据修复)更新page的url_name数据到page_language表中
UPDATE page_language l, page p SET l.url_name = p.url_name WHERE l.page_id = p.id AND l.url_name = '';

#新增组件站点关联关系表
CREATE TABLE `component_site_relation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '组件类型|1布局组件,2ui组件',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组件ID',
  `site_code` varchar(32) NOT NULL DEFAULT '' COMMENT '站点siteCode',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_site_code_component_id_type` (`site_code`,`component_id`,`type`) USING BTREE,
  KEY `idx_site_code` (`component_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组件站点配置表|滕家顺|2018-09-03';

#新增组件模板关联关系表
CREATE TABLE `component_tpl_site_relation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '组件类型|1布局组件,2ui组件',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组件ID',
  `site_code` varchar(32) NOT NULL DEFAULT '' COMMENT '站点siteCode',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_site_code_tpl_id_type` (`site_code`,`tpl_id`,`type`) USING BTREE,
  KEY `idx_site_code` (`tpl_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组件模板站点配置表|滕家顺|2018-09-03';

#(历史数据修复)更新组件和模板对应站点关系的数据(tips:方法可重复调用，记录存在会更新，不存在则插入)
http://test.geshop.com.develop.php7.egomsl.com/test/mysql/component-site

#v1.5.0版本 增加SKU来源
ALTER TABLE `goods_manage_data_block`
ADD COLUMN `sku_from` tinyint(2) NOT NULL COMMENT 'SKU来源(1:手动输入; 2:选品系统)|田海深|201800901' AFTER `more_url`;
UPDATE `goods_manage_data_block` SET sku_from=1 WHERE sku_from=0;

#页面多时段样式
ALTER TABLE `page_language`
ADD COLUMN `style_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903' AFTER `background_repeat`,
ADD COLUMN `multi_time_style` text COMMENT '多时段样式|田海深|201800903' AFTER `style_type`;
UPDATE `page_language` SET style_type=1 WHERE style_type=0;

#对接选品系统
CREATE TABLE `soa_ips_goods` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ips_activity_id` bigint(11) unsigned NOT NULL COMMENT '选品系统子活动ID',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动页面ID|page.id',
  `lang` varchar(6) NOT NULL COMMENT '语言简称，如：zh/en/de',
  `component_id` int(10) unsigned NOT NULL COMMENT 'UI组件ID|page_ui_component.id',
  `component_key` varchar(8) NOT NULL COMMENT 'UI组件身份唯一编码|component.component_key',
  `goods_sku` text COMMENT '选品系统活动SKU最新列表',
  `last_update_time` int(10) NOT NULL COMMENT '选品系统活动SKU最后更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_ips_activity_id` (`ips_activity_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='选品系统商品对接表|田海深|2018-09-04'';';

#v1.6.0 0915版本
ALTER TABLE `activity`
ADD COLUMN `group_id` bigint(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动分组ID|activity_group.id' AFTER `id`,
ADD INDEX `idx_group_id`(`group_id`) USING BTREE;

#活动分组
CREATE TABLE `activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` varchar(200) NOT NULL COMMENT '支持语言列表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动分组|田海深|20180913';

#活动子页面分组
CREATE TABLE `page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  PRIMARY KEY (`id`),
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动子页面分组|田海深|20180913';

#发布记录表增加IP字段
ALTER TABLE `page_publish_log`
ADD COLUMN `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ip|ip2long|滕家顺|20180917' AFTER `diff`;

#新增obs选品关系对照表
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
  `component_id` int(10) unsigned NOT NULL COMMENT 'UI组件ID|page_ui_component.id',
  `component_key` varchar(8) NOT NULL COMMENT 'UI组件身份唯一编码|component.component_key',
  `goods_sku` text NOT NULL COMMENT '产品最新列表',
  `last_update_time` int(10) NOT NULL DEFAULT '0' COMMENT '产品最后更新时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`) USING BTREE,
  KEY `section_id` (`section_id`) USING BTREE,
  KEY `theme_id` (`theme_id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OBS版块关系对照表';

#自动更新线上产品
alter table page_publish_cache add  `layout` mediumtext COMMENT '排好序的Layout组件列表' after `html`,
add `uilist` mediumtext COMMENT 'UI组件列表' after `layout`;

#保持和大数据中转表结构一致
ALTER TABLE `activity_data`
MODIFY COLUMN `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '数据记录ID' FIRST;

#推广落地页1.0
ALTER TABLE page_language ADD `goods_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编码',
						  ADD `status` tinyint(2) unsigned NOT NULL DEFAULT 0 COMMENT '推送网站状态0未推送 1推送成功';
ALTER TABLE activity ADD `mold` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动类型 1 普通活动 2 活动推广';
alter table page_publish_cache add `customJs` mediumtext COMMENT '自定义Js' after `css`;

#新增字段-保持和大数据中转表结构一致(2018-10-16)
ALTER TABLE `activity_data`
ADD COLUMN `sub_uv` int(11) NOT NULL DEFAULT 0 COMMENT '专题访问人数' AFTER `sub_ic_pv`;

#v1.63版本 新增分享信息
ALTER TABLE page_language ADD `share_image` varchar(255) NOT NULL DEFAULT '' COMMENT '分享图片',
                          ADD `share_title` varchar(255) NOT NULL DEFAULT '' COMMENT '分享标题',
                          ADD `share_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '分享描述',
                          ADD `share_link` varchar(255) NOT NULL DEFAULT '' COMMENT '分享链接',
						  ADD `share_place` tinytext NOT NULL COMMENT '分享渠道';

#数据报表二期
ALTER TABLE `activity_data`
ADD INDEX `idx_site_platform_buyer_identity`(`buyer_identity`, `platform`, `site`) USING BTREE,
ADD INDEX `idx_sub_id`(`sub_id`) USING BTREE,
ADD INDEX `idx_module_id`(`module_id`) USING BTREE;

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
  PRIMARY KEY (`id`),
  KEY `idx_site_platform_buyer_identity` (`buyer_identity`,`platform`,`site`) USING BTREE,
  KEY `idx_sub_id` (`sub_id`) USING BTREE,
  KEY `idx_module_id` (`module_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='首页页流量数据统计表';

#v1.30版本  gb活动

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
  PRIMARY KEY (`id`),
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_site` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动表';

CREATE TABLE `gb_activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` mediumtext  COMMENT '支持渠道语言列表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动分组';

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pid` (`pid`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_url_name` (`url_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面表';

CREATE TABLE `gb_page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面转换关系表';

CREATE TABLE `gb_page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  PRIMARY KEY (`id`),
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动子页面分组';

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
  `style_type` tinyint(2) NOT NULL COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903',
  `multi_time_style` text COMMENT '多时段样式|田海深|201800903',
  `url_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830',
  `page_url` varchar(255) NOT NULL DEFAULT '' COMMENT '页面访问URL',
  `end_url` varchar(255) NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723',
  `redirect_url` varchar(255) NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806',
  `custom_css` text  COMMENT '自定义CSS样式',
  `statistics_code` text  COMMENT '统计代码',
  `local_files` text COMMENT '本地文件存储路径json格式（可能有多个文件）',
  `s3_files` text  COMMENT '文件在S3上存储路径json格式（可能有多个文件）',
  `group_id` varchar(32)  DEFAULT '0' COMMENT '渠道聚合ID',
  `share` mediumtext  COMMENT '分享信息(json)',
  PRIMARY KEY (`id`),
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面语言配置项表';

CREATE TABLE `gb_page_layout_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言|夏人杰|20180709',
  `page_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`,`next_id`) USING BTREE,
  KEY `idx_next_id` (`next_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件关联表';

CREATE TABLE `gb_page_layout_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text  COMMENT '组件内容数据，json格式',
  `custom_css` text COMMENT '自定义CSS',
  `background_color` varchar(10) NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_component_id` (`component_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='layout组件数据表';

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
  PRIMARY KEY (`id`),
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面内容缓存记录';

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
  PRIMARY KEY (`id`),
  KEY `idx_file_hash_type` (`file_hash`,`file_type`) USING BTREE,
  KEY `idx_page_id_lang_file` (`page_id`,`lang`,`file_type`,`file_hash`) USING BTREE,
  KEY `idx_version_page_id` (`version`,`page_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面发布日志';

CREATE TABLE `gb_page_ui_component_data` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext  COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
  PRIMARY KEY (`id`),
  KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='UI组件数据表';

CREATE TABLE `gb_page_ui_component` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_layout_id_next_id_position` (`layout_id`,`next_id`,`position`,`lang`) USING BTREE,
  KEY `idx_pred_id` (`next_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面布局组件与UI组件关联表';


CREATE TABLE `gb_page_service_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '页面ID|gb_page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `tag_config` text COMMENT '服务标配置',
  PRIMARY KEY (`id`),
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='页面服务标配置表|田海深|2018-11-03';

#数据报表添加销售数据
ALTER TABLE `activity_data`
ADD COLUMN sub_pur_numb int(11) NOT NULL DEFAULT '0' COMMENT '专题购买用户数' after sub_cl_rate,
ADD COLUMN sub_pay_amount double(16,2) NOT NULL DEFAULT '0.00' COMMENT '专题购买金额' after sub_pur_numb,
ADD COLUMN module_pur_numb int(11) NOT NULL DEFAULT '0' COMMENT '组件购买用户数' after module_ic_pv,
ADD COLUMN module_pay_amount double(16,2) NOT NULL DEFAULT '0.00' COMMENT '组件购买金额' after module_pur_numb,
ADD COLUMN pit_pur_numb int(11) NOT NULL DEFAULT '0' COMMENT '坑位购买用户数' after pit_cl_rate,
ADD COLUMN pit_pay_amount double(16,2) NOT NULL DEFAULT '0.00' COMMENT '坑位购买金额' after pit_pur_numb;

#ZF国家站
CREATE TABLE `zf_activity`  (
  `id` bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `group_id` bigint(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动分组ID|activity_group.id',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '活动所属站点类型|1PC端,2wap端',
  `site_code` varchar(10)  NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(255)  NOT NULL DEFAULT '' COMMENT '渠道简码,多个渠道以英文逗号分隔',
  `lang` varchar(2047)  NOT NULL DEFAULT '' COMMENT '活动所属语言，多个语系以英文逗号分隔，如zh,en,de',
  `name` varchar(50)  NOT NULL DEFAULT '' COMMENT '活动名称',
  `description` varchar(500)  NOT NULL DEFAULT '' COMMENT '活动简介',
  `status` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '活动状态|1待上线,2已上线,3待下线,4已下线',
  `verify_status` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '活动审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_delete` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除|0否，1是',
  `is_lock` tinyint(2) UNSIGNED NOT NULL COMMENT '是否加锁 0：否 1：是|夏人杰|20180609',
  `mold` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '活动类型 1 普通活动 2 活动推广',
  `start_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动开始时间',
  `end_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动结束时间',
  `create_user` varchar(20)  NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_user` varchar(20)  NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后编辑时间',
  `verify_user` varchar(20)  NOT NULL DEFAULT '' COMMENT '审核人',
  `verify_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '审核时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_time`(`create_time`) USING BTREE,
  INDEX `idx_site`(`site_code`) USING BTREE,
  INDEX `idx_group_id`(`group_id`) USING BTREE,
  INDEX `idx_pipeline`(`pipeline`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '活动表|朱国强|2018-03-16' ROW_FORMAT = Compact;

CREATE TABLE `zf_activity_group`  (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100)  NOT NULL COMMENT '支持端口列表',
  `lang_list` varchar(200)  NOT NULL COMMENT '支持语言列表',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '活动分组|田海深|20180913' ROW_FORMAT = Compact;

CREATE TABLE `zf_page`  (
  `id` bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `group_id` varchar(32)  NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `pid` varchar(32)  NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为在链接中展示使用，区别于其它表的page_id，此处取名pid',
  `activity_id` int(10) NOT NULL DEFAULT 0 COMMENT '关联的活动ID|activity.id',
  `site_code` varchar(10)  NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180709',
  `pipeline` varchar(6)  NOT NULL DEFAULT '' COMMENT '渠道简码',
  `status` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '页面状态|1待上线,2已上线,3待下线,4已下线',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '活动所属站点类型|1PC端,2wap端,3app端|夏人杰|20180709',
  `verify_status` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '页面审核状态|1未提交,2撤回提交,3提交上线审核,4上线审核拒绝,5上线审核通过,6下线审核提交,7下线审核拒绝,8下线审核通过',
  `is_lock` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '加解锁 1：解锁 2：加锁|夏人杰|20180706',
  `auto_refresh` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否需要自动刷新',
  `refresh_time` int(11) NOT NULL DEFAULT 0 COMMENT '自动刷新时间间隔（0-不自动刷新）',
  `end_time` int(11) UNSIGNED NOT NULL COMMENT '子页面活动下架时间|田海深|20180723',
  `is_delete` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除|0否,1是',
  `create_user` varchar(20)  NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_user` varchar(20)  NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后编辑时间',
  `verify_user` varchar(20)  NOT NULL DEFAULT '' COMMENT '最后审核人',
  `verify_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后审核时间',
  `default_lang` varchar(6)  NOT NULL DEFAULT '' COMMENT '默认语言简称，如：zh/en/de',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_pid`(`pid`) USING BTREE,
  INDEX `idx_time`(`create_time`) USING BTREE,
  INDEX `idx_activity_id`(`activity_id`) USING BTREE,
  INDEX `idx_group_id`(`group_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '页面表|朱国强|2018-03-16' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_convert_relation`  (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT 0 COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT 0 COMMENT '目标页面ID|page.id',
  `create_user` varchar(20)  NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_user` varchar(32)  NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后编辑时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_source_id`(`source_id`, `target_id`) USING BTREE,
  INDEX `idx_target_id`(`target_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '页面转换关系表|滕家顺|2018-07-04' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_group`  (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) UNSIGNED NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32)  NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) UNSIGNED NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) UNSIGNED NOT NULL COMMENT '活动子页面ID|page.id',
  `pipeline` varchar(6)  NOT NULL DEFAULT '' COMMENT '渠道简码',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_page_group_id`(`page_group_id`) USING BTREE,
  INDEX `idx_page_id`(`page_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '活动子页面分组|田海深|20180913' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_language`  (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` varchar(32)  NOT NULL DEFAULT '0' COMMENT '渠道聚合ID',
  `page_id` int(11) NOT NULL DEFAULT 0 COMMENT '页面ID|page.id',
  `lang` varchar(6)  NOT NULL DEFAULT '' COMMENT '语言简称，如：zh/en/de',
  `title` varchar(255)  NOT NULL DEFAULT '' COMMENT '页面title',
  `tpl_id` int(10)  NOT NULL DEFAULT 0 COMMENT '页面模板id| page_template.id|夏人杰|20180709',
  `seo_title` varchar(255)  NOT NULL DEFAULT '' COMMENT 'SEO标题|滕家顺|20180814',
  `keywords` varchar(255)  NOT NULL DEFAULT '' COMMENT '页面keywords',
  `description` varchar(500)  NOT NULL DEFAULT '' COMMENT '页面描述',
  `background_color` varchar(12)  NOT NULL DEFAULT '' COMMENT '背景颜色',
  `background_image` varchar(128)  NOT NULL DEFAULT '' COMMENT '背景图片',
  `background_position` varchar(32)  NOT NULL DEFAULT '' COMMENT '背景位置',
  `background_repeat` varchar(32)  NOT NULL DEFAULT '' COMMENT '背景定位',
  `style_type` tinyint(2) NOT NULL COMMENT '页面样式类型(1:系统设置; 2:自定义)|田海深|201800903',
  `multi_time_style` text  COMMENT '多时段样式|田海深|201800903',
  `url_name` varchar(64)  NOT NULL DEFAULT '' COMMENT 'URL标题|滕家顺|20180830',
  `page_url` varchar(255)  NOT NULL DEFAULT '' COMMENT '页面访问URL',
  `end_url` varchar(255)  NOT NULL COMMENT '子页面活动下架后跳转URL|田海深|20180723',
  `redirect_url` varchar(255)  NOT NULL DEFAULT '' COMMENT '不同端关联的跳转链接|滕家顺|20180806',
  `custom_css` text  COMMENT '自定义CSS样式',
  `statistics_code` text  COMMENT '统计代码',
  `local_files` text  COMMENT '本地文件存储路径json格式（可能有多个文件）',
  `s3_files` text  COMMENT '文件在S3上存储路径json格式（可能有多个文件）',
  `goods_sn` varchar(30)  NOT NULL DEFAULT '' COMMENT '产品编码',
  `status` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '推送网站状态0未推送 1推送成功',
  `share_image` varchar(255)  NOT NULL DEFAULT '' COMMENT '分享图片',
  `share_title` varchar(255)  NOT NULL DEFAULT '' COMMENT '分享标题',
  `share_desc` varchar(255)  NOT NULL DEFAULT '' COMMENT '分享描述',
  `share_link` varchar(255)  NOT NULL DEFAULT '' COMMENT '分享链接',
  `share_place` tinytext  COMMENT '分享渠道',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uk_page_id_lang`(`page_id`, `lang`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '页面语言配置项表|滕家顺|2018-04-18' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_layout_component`  (
  `id` bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6)  NOT NULL DEFAULT '' COMMENT '多语言|夏人杰|20180709',
  `page_id` int(10) NOT NULL DEFAULT 0 COMMENT '关联页面id|page.id',
  `component_key` varchar(8)  NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT 0 COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_page_id_lang_next_id`(`page_id`, `lang`, `next_id`) USING BTREE,
  INDEX `idx_next_id`(`next_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '页面布局组件关联表|朱国强|2018-03-16' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_layout_data`  (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(11) NOT NULL DEFAULT 0 COMMENT '组件ID（page_layout_component.id）',
  `lang` varchar(6)  NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text  COMMENT '组件内容数据，json格式',
  `custom_css` text  COMMENT '自定义CSS',
  `background_color` varchar(10)  NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120)  NOT NULL DEFAULT '' COMMENT '背景图片地址',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_component_id`(`component_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'layout组件数据表|滕家顺|2018-04-18' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_publish_cache`  (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20)  NOT NULL DEFAULT '0' COMMENT '版本号',
  `page_id` int(11) NOT NULL DEFAULT 0 COMMENT '页面ID',
  `lang` varchar(5)  NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `html` mediumtext  null COMMENT '不包含网采商品的html',
  `html_network` mediumtext  NULL COMMENT '包含网采商品的html',
  `layout` mediumtext  NULL COMMENT '排好序的Layout组件列表',
  `uilist` mediumtext  NULL COMMENT 'UI组件列表',
  `js` mediumtext  NULL COMMENT '页面打包js',
  `css` mediumtext  NULL COMMENT '页面打包css',
  `customJs` mediumtext  NULL COMMENT '自定义Js',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态，0-未启用，1-启用',
  `create_user` varchar(32)  NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_user` varchar(32)  NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后编辑时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_page_id_lang_file`(`page_id`, `lang`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '页面内容缓存记录|滕家顺|2018-07-30' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_publish_log`  (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `version` varchar(20)  NOT NULL DEFAULT '0' COMMENT '版本号',
  `log_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '日志类型，1-缓存文件生成日志，2-发布S3日志',
  `page_id` int(11) NOT NULL DEFAULT 0 COMMENT '页面ID',
  `lang` varchar(5)  NOT NULL DEFAULT '' COMMENT '语言代码简称',
  `site_code` varchar(10)  NOT NULL DEFAULT '' COMMENT '站点简称|夏人杰|20180727',
  `action_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '操作类型（1-上线，2-下线）',
  `file_name` varchar(255)  NOT NULL DEFAULT '' COMMENT '文件名',
  `file_type` varchar(8)  NOT NULL DEFAULT '' COMMENT '文件后缀',
  `file_size` int(11) NOT NULL DEFAULT 0 COMMENT '文件大小，单位B',
  `file_hash` varchar(32)  NOT NULL DEFAULT '' COMMENT '文件hash',
  `include_network` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否包含网采商品',
  `local_path` varchar(255)  NOT NULL DEFAULT '' COMMENT '本地文件相对路径',
  `s3_url` varchar(255)  NOT NULL DEFAULT '' COMMENT 'S3上存储绝对路径',
  `diff` varchar(2000)  NOT NULL DEFAULT '' COMMENT '差异对比json结果',
  `ip` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ip|ip2long|滕家顺|20180917',
  `create_user` varchar(32)  NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_user` varchar(32)  NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后编辑时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_page_id_lang_file`(`page_id`, `lang`, `file_type`, `file_hash`) USING BTREE,
  INDEX `idx_file_hash_type`(`file_hash`, `file_type`) USING BTREE,
  INDEX `idx_version_page_id`(`version`, `page_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '页面发布日志|滕家顺|2018-05-03' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_template`  (
  `id` bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` varchar(32)  NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid',
  `name` varchar(50)  NOT NULL DEFAULT '' COMMENT '页面模板名称',
  `place` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '应用场景1：活动页 2：首页|夏人杰|20180704 3.推广页',
  `platform_type` tinyint(2) NOT NULL COMMENT '平台类型 1:PC; 2:Wap; 3:App|田海深|20180817',
  `pic` varchar(200)  NOT NULL DEFAULT '' COMMENT '模板预览图',
  `site_code` varchar(10)  NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(6)  NOT NULL DEFAULT '' COMMENT '渠道简码',
  `range` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '适用范围|1PC端,2WAP端,3响应式',
  `lang` varchar(10)  NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `custom_css` text  COMMENT '页面自定义样式',
  `layout` text  COMMENT '模板布局组件信息',
  `layout_data` text  COMMENT '模板布局数据',
  `ui` text  COMMENT '模板UI组件信息',
  `ui_data` text  COMMENT '模板UI组件数据',
  `tpl_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '模板类型：1-公用模板；2-私有模板',
  `is_default` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否默认|0否,1是',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除|0否,1是',
  `create_user` varchar(20)  NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '创建时间',
  `update_user` varchar(20)  NOT NULL DEFAULT '' COMMENT '更信任',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_name`(`name`) USING BTREE,
  INDEX `idx_pipeline`(`pipeline`) USING BTREE,
  INDEX `idx_site_code`(`site_code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '页面模板' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_ui_component`  (
  `id` bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8)  NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6)  NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT 0 COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'UI组件选中的模板ID',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_layout_id_next_id_position`(`layout_id`, `next_id`, `position`, `lang`) USING BTREE,
  INDEX `idx_pred_id`(`next_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '页面布局组件与UI组件关联表|朱国强|2018-03-16' ROW_FORMAT = Compact;

CREATE TABLE `zf_page_ui_component_data`  (
  `id` bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'page_ui_component.id',
  `lang` varchar(6)  NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80)  NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext  COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '模板id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_clt_id`(`component_id`, `lang`, `tpl_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'UI组件数据表|黄志辉|2018-07-04' ROW_FORMAT = Compact;

#v1.40 gb obs表增加渠道和站点
ALTER TABLE `soa_obs_goods`
  ADD `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  ADD `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码';


ALTER TABLE `gb_page_language`
ADD COLUMN `background_attachment` char(10) NOT NULL DEFAULT '' COMMENT '背景固定|夏人杰|20181213' AFTER `background_repeat`;

ALTER TABLE `gb_page_language`
ADD COLUMN `goods_component_style` text(0) NOT NULL COMMENT '商品类组件样式设置|夏人杰|20181218' AFTER `background_attachment`;

ALTER TABLE `page_language`
ADD COLUMN `goods_component_style` text(0) NOT NULL COMMENT '商品类组件样式设置|夏人杰|20181218' AFTER `background_attachment`;

ALTER TABLE `page_language`
ADD COLUMN `background_attachment` char(10) NOT NULL DEFAULT '' COMMENT '背景固定|夏人杰|20181214' AFTER `background_repeat`;

#1.7.0 Dresslily接入
CREATE TABLE `dl_activity` (
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
  `mold` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '活动类型 1 普通活动 2 活动推广',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `verify_user` varchar(20) NOT NULL DEFAULT '' COMMENT '审核人',
  `verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  PRIMARY KEY (`id`),
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_site` (`site_code`) USING BTREE,
  KEY `idx_group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动表|朱国强|2018-03-16';

CREATE TABLE `dl_activity_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `platform_list` varchar(100) NOT NULL COMMENT '支持端口列表',
  `lang_list` varchar(200) NOT NULL COMMENT '支持语言列表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动分组|田海深|20180913';

CREATE TABLE `dl_page` (
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pid` (`pid`) USING BTREE,
  KEY `idx_time` (`create_time`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_url_name` (`url_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='页面表|朱国强|2018-03-16';

CREATE TABLE `dl_page_group` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `activity_group_id` bigint(11) unsigned NOT NULL COMMENT '活动分组ID|activity_group.id',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID',
  `platform_type` tinyint(2) unsigned NOT NULL COMMENT '端口类型 1:PC; 2:Wap; 3:App',
  `page_id` int(10) unsigned NOT NULL COMMENT '活动子页面ID|page.id',
  PRIMARY KEY (`id`),
  KEY `idx_page_group_id` (`page_group_id`) USING BTREE,
  KEY `idx_page_id` (`page_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动子页面分组|田海深|20180913';

CREATE TABLE `dl_page_language` (
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
  PRIMARY KEY (`id`),
  KEY `uk_page_id_lang` (`page_id`,`lang`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='页面语言配置项表|滕家顺|2018-04-18';

CREATE TABLE `dl_page_convert_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '源页面ID|page.id',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标页面ID|page.id',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '最后编辑人',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_id` (`source_id`,`target_id`) USING BTREE,
  KEY `idx_target_id` (`target_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='页面转换关系表|滕家顺|2018-07-04';

CREATE TABLE `dl_page_layout_component` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '布局组件页面位置ID',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言|夏人杰|20180709',
  `page_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT '组件身份唯一编码|component.component_key',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前组件的下一个组件位置编号|page_layout_component.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`,`next_id`) USING BTREE,
  KEY `idx_next_id` (`next_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='页面布局组件关联表|朱国强|2018-03-16';

CREATE TABLE `dl_page_layout_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(11) NOT NULL DEFAULT '0' COMMENT '组件ID（page_layout_component.id）',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `data` text COMMENT '组件内容数据，json格式',
  `custom_css` text COMMENT '自定义CSS',
  `background_color` varchar(10) NOT NULL DEFAULT '' COMMENT '背景颜色，eg: #000000',
  `background_img` varchar(120) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_component_id` (`component_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='layout组件数据表|滕家顺|2018-04-18';

CREATE TABLE `dl_page_publish_cache` (
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
  PRIMARY KEY (`id`),
  KEY `idx_page_id_lang_file` (`page_id`,`lang`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='页面内容缓存记录|滕家顺|2018-07-30';

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
  PRIMARY KEY (`id`),
  KEY `idx_page_id_lang_file` (`page_id`,`lang`,`file_type`,`file_hash`) USING BTREE,
  KEY `idx_file_hash_type` (`file_hash`,`file_type`) USING BTREE,
  KEY `idx_version_page_id` (`version`,`page_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='页面发布日志|滕家顺|2018-05-03';

CREATE TABLE `dl_page_template` (
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='页面模板';

CREATE TABLE `dl_page_ui_component` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'UI组件页面位置ID',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件所在的布局组件页面位置编号|page_layout_component.id',
  `next_id` int(10) NOT NULL DEFAULT '0' COMMENT '当前UI组件后面一个UI组件的页面位置编号|page_ui_component.id',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该UI组件在布局组件上面的区域模块|按照区域可选1,2,3',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_layout_id_next_id_position` (`layout_id`,`next_id`,`position`,`lang`) USING BTREE,
  KEY `idx_pred_id` (`next_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='页面布局组件与UI组件关联表|朱国强|2018-03-16';

CREATE TABLE `dl_page_ui_component_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'page_ui_component.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `key` varchar(80) NOT NULL DEFAULT '' COMMENT '字段key',
  `value` mediumtext COMMENT '字段value(统一json格式存入)',
  `is_public` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否公用字段：1-公用； 默认0-不公用；',
  `is_m` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转m端使用：1-可用； 默认0-不可用',
  `is_app` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转app端使用：1-可用； 默认0-不可用',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板id',
  PRIMARY KEY (`id`),
  KEY `idx_clt_id` (`component_id`,`lang`,`tpl_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='UI组件数据表|黄志辉|2018-07-04';

#1.6.0版本
CREATE TABLE `gb_page_special` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_group_id` varchar(32) NOT NULL COMMENT '活动子页面分组ID|夏人杰|20190104',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 COMMENT='专题活动页扩展表|夏人杰|20190104';

ALTER TABLE `gb_page_group`
ADD COLUMN `special_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '专题ID|夏人杰|20190103';

#修复日志详字段情长度不够
ALTER TABLE `site_update_log`
MODIFY COLUMN `detail` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '详情' AFTER `result`;

#1.7.0版本
ALTER TABLE `gb_page_ui_component_data`
ADD COLUMN `is_native_url` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT 'url是否需要转deeplink 0:否 1:是|夏人杰|20190118' AFTER `is_app`;
ALTER TABLE `gb_page_ui_component_data`
ADD COLUMN `is_pipeline_url` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT 'url是否需要转渠道domain 0:否 1:是|夏人杰|20190118' AFTER `is_native_url`;

#服装 选品系统
ALTER TABLE `soa_ips_goods`
  ADD COLUMN `website_code` varchar(5) NOT NULL COMMENT '网站简码；如:zf/rg' AFTER `ips_activity_id`,
  ADD COLUMN `rule_type` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '规则类型 1：自动；2：手动' AFTER `website_code`,
  ADD INDEX `idx_website_code`(`website_code`) USING BTREE;

#FZ 1.7.3
CREATE TABLE `admin_site_privilege` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT '管理用户ID|admin.id',
  `website_code` varchar(5) DEFAULT NULL COMMENT '站点简码；如：zf/rg',
  `home_permissions` text COMMENT 'json格式用户拥有的首页活动渠道/语言权限',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id` (`user_id`,`website_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员站点数据权限|田海深|20190131';

#FZ 1.8.1
ALTER TABLE `page_layout_data`
ADD COLUMN `style_data` text NOT NULL COMMENT '配置样式数据' AFTER `background_img`;
ALTER TABLE `zf_page_layout_data`
ADD COLUMN `style_data` text NOT NULL COMMENT '配置样式数据' AFTER `background_img`;

#FZ 1.8.2
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
  `ui_data` mediumtext COMMENT '组件数据',
  `view_type` tinyint(2) unsigned NOT NULL COMMENT '查看类型：1-公用模板；2-私有模板',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除(0否,1是)',
  `create_user` varchar(50) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(50) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_platform_place` (`platform_code`,`place_type`) USING BTREE,
  KEY `idx_create_user` (`create_user`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组件模板';

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
  `ui_data` mediumtext COMMENT '组件数据',
  `view_type` tinyint(2) unsigned NOT NULL COMMENT '查看类型：1-公用模板；2-私有模板',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除(0否,1是)',
  `create_user` varchar(50) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(50) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_platform_place` (`platform_code`,`place_type`) USING BTREE,
  KEY `idx_create_user` (`create_user`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组件模板';

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
  `ui_data` mediumtext COMMENT '组件数据',
  `view_type` tinyint(2) unsigned NOT NULL COMMENT '查看类型：1-公用模板；2-私有模板',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除(0否,1是)',
  `create_user` varchar(50) NOT NULL COMMENT '创建人',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_user` varchar(50) NOT NULL COMMENT '更新人',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_platform_place` (`platform_code`,`place_type`) USING BTREE,
  KEY `idx_create_user` (`create_user`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组件模板';

#FZ 1.8.3
ALTER TABLE `admin_site_privilege`
ADD COLUMN `special_permissions` text NULL COMMENT '用户拥有的专题页活动渠道/语言权限json格式' AFTER `home_permissions`;

#新增组件三端绑定关系
ALTER TABLE `zf_page_ui_component`
ADD COLUMN `bind_relation` varchar(100) NOT NULL DEFAULT '' COMMENT '三端绑定关系' AFTER `tpl_id`;

#保存三端同步数据缓存表
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='zaful同步平台数据缓存表';

#FZ 1.8.4
ALTER TABLE `zf_page`
ADD COLUMN `is_blog` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否博客页面|田海深|20190321' AFTER `is_delete`;

#FZ 1.8.5
CREATE TABLE `sys_request_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `request_id` varchar(32) NOT NULL COMMENT '请求唯一ID',
  `website_code` varchar(5) NOT NULL COMMENT '网站编码',
  `request_date` date NOT NULL COMMENT '请求日期',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `page_ids` varchar(255) NOT NULL DEFAULT '0' COMMENT '操作活动页面ID',
  `module` varchar(50) NOT NULL COMMENT '模块名称',
  `request_route` varchar(100) NOT NULL COMMENT '请求路由',
  `request_url` varchar(255) NOT NULL COMMENT '请求URL',
  `method` varchar(10) NOT NULL COMMENT '请求方法',
  `request_time` int(10) unsigned NOT NULL COMMENT '请求时间',
  `user_ip` varchar(255) NOT NULL COMMENT '用户IP',
  `post_params` text NULL COMMENT 'json格式POST参数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_request_id` (`request_id`),
  KEY `idx_code_date` (`website_code`,`request_date`) USING BTREE,
  KEY `idx_username` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统-用户访问日志';

ALTER TABLE `admin_log`
ADD COLUMN `request_id` varchar(32) NOT NULL DEFAULT '' COMMENT '请求ID|sys_request_log.request_id' AFTER `id`,
ADD INDEX `idx_request_id`(`request_id`) USING BTREE;


ALTER TABLE `site_update_log`
MODIFY COLUMN `detail` MEDIUMTEXT COMMENT '详情';

#FZ 1.8.6
ALTER TABLE `page_ui_template`
ADD COLUMN `used_count` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数' AFTER `update_time`;

ALTER TABLE `zf_page_ui_template`
ADD COLUMN `used_count` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数' AFTER `update_time`;

ALTER TABLE `dl_page_ui_template`
ADD COLUMN `used_count` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数' AFTER `update_time`;

CREATE TABLE `soa_ips_activity_sku` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ips_activity_id` bigint(11) unsigned NOT NULL COMMENT '选品系统子活动ID',
  `sku_update_time` int(10) unsigned NOT NULL COMMENT '选品系统子活动SKU更新时间',
  `sku_list` text COMMENT '选品系统子活动已选SKU',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ips_activity_id` (`ips_activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='选品系统子活动已选SKU缓存表';

ALTER TABLE `ui_component_tpl`
ADD COLUMN `is_vue_ssr` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否使用node渲染 0：否 1：是|夏人杰|20190424' AFTER `status`;

#FZ 1.8.8
ALTER TABLE `zf_page`
ADD COLUMN `is_redirect_country` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否国家站自动跳转(0 - 不跳转； 1 - 跳转)|田海深|20190427' AFTER `default_lang`;

ALTER TABLE `zf_page`
ADD COLUMN `home_type` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '首页类型 0：A首页 1：B首页|夏人杰|20190610' AFTER `is_blog`;

ALTER TABLE `zf_page_publish_log`
ADD COLUMN `rollback_user` varchar(32) NOT NULL DEFAULT '' COMMENT '回滚操作人|夏人杰|20190610' AFTER `update_time`,
ADD COLUMN `rollback_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '回滚时间|夏人杰|20190610',
ADD COLUMN `online_user` varchar(32) NOT NULL DEFAULT '' COMMENT '上线操作人|夏人杰|20190610',
ADD COLUMN `online_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上线时间|夏人杰|20190610';

CREATE TABLE `soa_ips_queue` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `message` mediumtext COMMENT 'MQ消息',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `run_time` int(10) unsigned DEFAULT NULL COMMENT '处理开始时间',
  `end_time` int(10) unsigned DEFAULT NULL COMMENT '处理结束时间',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态(0: 待处理； 1: 处理成功; 2: 处理失败)',
  `result` text COMMENT '处理结果信息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='选品系统MQ队列消息缓存表|田海深|2019-06-14'

CREATE TABLE `page_language_package` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点编码|夏人杰|20190615',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言|夏人杰|20190615',
  `lang_name` varchar(30) NOT NULL DEFAULT '' COMMENT '语言名称|夏人杰|20190617',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人|夏人杰|20190615',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间|夏人杰|20190615',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '操作人|夏人杰|20190615',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间|夏人杰|20190615',
  PRIMARY KEY (`id`),
  KEY `idx_site_code` (`site_code`),
  KEY `idx_lang` (`lang`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='语言包管理表|夏人杰|20190617';

CREATE TABLE `page_language_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言编码|夏人杰|20190617',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT '多语言key|夏人杰|20190617',
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT '多语言内容|夏人杰|20190617',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '多语言说明|夏人杰|20190617',
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人|夏人杰|20190617',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间|夏人杰|20190617',
  `update_user` varchar(32) NOT NULL DEFAULT '' COMMENT '操作人|夏人杰|20190617',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间|夏人杰|20190617',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除 0：否 1：是|夏人杰|20190617',
  PRIMARY KEY (`id`),
  KEY `idx_lang` (`lang`),
  KEY `idx_key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='多语言内容列表|夏人杰|20190617';

CREATE TABLE `ui_component_language_relation` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `lang_key` varchar(100) NOT NULL DEFAULT '' COMMENT '多语言key|夏人杰|20190624',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板ID|夏人杰|20190624',
  PRIMARY KEY (`id`),
  KEY `idx_tepl_id` (`tpl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='组件多语言关联表|夏人杰|20190624';

#1.9.3
ALTER TABLE `page_ui_component`
ADD COLUMN `async_data_format` text COMMENT '组件自动刷新数据格式|田海深|20190627' AFTER `tpl_id`;

ALTER TABLE `dl_page_ui_component`
ADD COLUMN `async_data_format` text COMMENT '组件自动刷新数据格式|田海深|20190627' AFTER `tpl_id`;

ALTER TABLE `zf_page_ui_component`
ADD COLUMN `async_data_format` text COMMENT '组件自动刷新数据格式|田海深|20190627' AFTER `bind_relation`;

ALTER TABLE `page_ui_template`
ADD COLUMN `ui` mediumtext NULL COMMENT '组件|田海深|20190627' AFTER `tpl_id`;

ALTER TABLE `dl_page_ui_template`
ADD COLUMN `ui` mediumtext NULL COMMENT '组件|田海深|20190627' AFTER `tpl_id`;

ALTER TABLE `zf_page_ui_template`
ADD COLUMN `ui` mediumtext NULL COMMENT '组件|田海深|20190627' AFTER `tpl_id`;

#修复多语言内容列表字段不够长
ALTER TABLE `page_language_data`
MODIFY COLUMN `value` varchar(500) NOT NULL DEFAULT '' COMMENT '多语言内容|夏人杰|20190617' AFTER `key`;

#zf专题页面表新增是否原生页字段
ALTER TABLE `zf_page`
ADD COLUMN `is_native` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否原生专题 0：否 1：是|夏人杰|20190821';

#UI组件新增icon
ALTER TABLE `ui_component`
ADD COLUMN `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '组件icon|夏人杰|20190821';

#原生专题页面布局组件关联表
CREATE TABLE `native_page_layout_component` (
  `id` bigint(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_id` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '关联页面id|page.id',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '多语言',
  `data` mediumtext DEFAULT NULL COMMENT '组件排列顺序',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_id_lang_next_id` (`page_id`,`lang`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='原生专题页面布局组件关联表|夏人杰|20190821';

#原生专题页面模板
CREATE TABLE `native_page_template` (
  `id` bigint(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '32位长度的pid，作为查看预览，区别于其它表的page_id，此处取名pid',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '页面模板名称',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '模板预览图',
  `site_code` varchar(10) NOT NULL DEFAULT '' COMMENT '站点简称',
  `pipeline` varchar(6) NOT NULL DEFAULT '' COMMENT '渠道简码',
  `lang` varchar(10) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `layout_data` mediumtext DEFAULT NULL COMMENT '模板布局数据',
  `ui_data` mediumtext DEFAULT NULL COMMENT '模板UI组件数据',
  `tpl_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板类型：1-公用模板；2-私有模板',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '''是否默认|0否,1是'',',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '''是否删除|0否,1是'',',
  `create_user` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_user` varchar(20) NOT NULL DEFAULT '' COMMENT '更信任',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `idx_pipeline` (`pipeline`) USING BTREE,
  KEY `idx_site_code` (`site_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='原生专题页面模板|夏人杰|20190831';

#原生专题页面布局组件与UI组件
CREATE TABLE `native_page_ui_component` (
  `id` bigint(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `component_id` bigint(12) unsigned NOT NULL COMMENT 'UI组件页面位置ID',
  `page_id` bigint(12) unsigned NOT NULL COMMENT '关联页面',
  `component_key` varchar(8) NOT NULL DEFAULT '0' COMMENT 'UI组件身份唯一编码|component.component_key',
  `lang` varchar(6) NOT NULL DEFAULT '' COMMENT '语言代码简称，如：en/zh/de',
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UI组件选中的模板ID',
  `tpl_title` varchar(50) NOT NULL DEFAULT '' COMMENT '模板title',
  `tpl_name` varchar(50) NOT NULL DEFAULT '' COMMENT '模板名称',
  `style_data` mediumtext DEFAULT NULL COMMENT '组件样式内容',
  `sku_data` mediumtext DEFAULT NULL COMMENT '商品数据',
  `setting_data` mediumtext DEFAULT NULL COMMENT '配置内容',
  `async_data_format` mediumtext DEFAULT NULL COMMENT '组件自动刷新数据格式',
  PRIMARY KEY (`id`),
  KEY `idx_component_page_id_lang` (`component_id`,`page_id`,`lang`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='原生专题页面布局组件与UI组件|夏人杰|20190821;';


#2.0.6
ALTER TABLE `activity`
ADD COLUMN `is_frequently` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用活动(0 - 不是； 1 - 是)|田海深|20190904' AFTER `is_lock`;

ALTER TABLE `zf_activity`
ADD COLUMN `is_frequently` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用活动(0 - 不是； 1 - 是)|田海深|20190904' AFTER `is_lock`;

ALTER TABLE `dl_activity`
ADD COLUMN `is_frequently` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否常用活动(0 - 不是； 1 - 是)|田海深|20190904' AFTER `is_lock`;

# dacu
ALTER TABLE `ui_component_tpl`
ADD COLUMN `is_async` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '是否异步组件模板 (0: 否;1: 是)|田海深|20191007' AFTER `is_vue_ssr`;

CREATE TABLE `sys_config` (
  `option_key` varchar(100) NOT NULL COMMENT '选项键',
  `option_value` text COMMENT '选项值',
  `option_desc` varchar(255) DEFAULT NULL COMMENT '选项描述',
  PRIMARY KEY (`option_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统设置表|田海深|20191014';

INSERT INTO `sys_config`(`option_key`, `option_value`, `option_desc`) VALUES ('sys.ui.sync_api_fallback_enabled', '1', '是否启用组件异步API接口兜底');
INSERT INTO `sys_config`(`option_key`, `option_value`, `option_desc`) VALUES ('sys.ui.dl.direct_use_api_fallback_data', '0', 'DL站点是否在组件里直接使用API的兜底数据，不请求站点接口');
INSERT INTO `sys_config`(`option_key`, `option_value`, `option_desc`) VALUES ('sys.ui.rg.direct_use_api_fallback_data', '0', 'RG站点是否在组件里直接使用API的兜底数据，不请求站点接口');
INSERT INTO `sys_config`(`option_key`, `option_value`, `option_desc`) VALUES ('sys.ui.zf.direct_use_api_fallback_data', '0', 'ZF站点是否在组件里直接使用API的兜底数据，不请求站点接口');

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
 PRIMARY KEY (`id`),
 KEY `idx_site_code_api_key` (`site_code`,`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组件异步API监控表|田海深|20191014';

#数据入湖

ALTER TABLE `activity`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
#update_time date 类型
ALTER TABLE `activity_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
alter table `activity_group` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
alter table `admin` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
 alter table `admin_log` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
alter table `admin_relation` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
alter table `admin_site_privilege` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
alter table `component_category` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
alter table `component_site_relation` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);  
  
alter table `component_tpl_site_relation` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
alter table `department` 
  drop COLUMN `order`,
  MODIFY COLUMN `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  add index `sys_update_time` (`sys_update_time`);
  
alter table `department_relation` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);

ALTER TABLE `index_activity_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `layout_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `menu`
  CHANGE COLUMN `order` `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序', 	
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `native_page_layout_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `native_page_template`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `native_page_ui_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `page`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `page_convert_relation`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `page_group`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `page_language`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `page_language_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `page_language_package`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);      
  
ALTER TABLE `page_layout_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
    
ALTER TABLE `page_layout_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
   
ALTER TABLE `page_publish_cache`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `page_publish_log`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `page_template`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
ALTER TABLE `page_ui_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
ALTER TABLE `page_ui_component_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `page_ui_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);  
  
ALTER TABLE `page_ui_template`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
ALTER TABLE `resource`
  drop COLUMN `order`,
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);

ALTER TABLE `role`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `role_privilege`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `setting`
  CHANGE COLUMN `group` `group_id` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);

ALTER TABLE `site_update_log`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
 
ALTER TABLE `soa_ips_activity_sku`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `soa_ips_goods`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
ALTER TABLE `soa_ips_queue`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);   
    
ALTER TABLE `soa_obs_goods`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `sys_api_monitor`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `sys_config`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `sys_request_log`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
ALTER TABLE `ui_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `ui_component_language_relation`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
 
ALTER TABLE `ui_component_tpl` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `ui_component_tpl_relation` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
#dresslily 
 
ALTER TABLE `dl_activity`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `dl_activity_group`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);   
    
ALTER TABLE `dl_page`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
ALTER TABLE `dl_page_convert_relation`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
ALTER TABLE `dl_page_group`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
ALTER TABLE `dl_page_language`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `dl_page_layout_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `dl_page_layout_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 

ALTER TABLE `dl_page_publish_cache`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
ALTER TABLE `dl_page_publish_log`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
ALTER TABLE `dl_page_template`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);      
  
ALTER TABLE `dl_page_ui_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `dl_page_ui_component_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 

ALTER TABLE `dl_page_ui_template`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
   
#gearbest
ALTER TABLE `gb_activity`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
alter table `gb_activity_group` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `gb_page`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
ALTER TABLE `gb_page_convert_relation`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
   
ALTER TABLE `gb_page_group`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
ALTER TABLE `gb_page_language`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `gb_page_layout_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `gb_page_layout_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 

ALTER TABLE `gb_page_publish_cache`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
ALTER TABLE `gb_page_publish_log`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
ALTER TABLE `gb_page_service_tag`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
 
ALTER TABLE `gb_page_special`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);    
   
ALTER TABLE `gb_page_ui_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `gb_page_ui_component_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
  
#rosegal
ALTER TABLE `rg_activity`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
alter table `rg_activity_group` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `rg_page`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `rg_page_convert_relation`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 
   
ALTER TABLE `rg_page_group`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `rg_page_language`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `rg_page_layout_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `rg_page_layout_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 

ALTER TABLE `rg_page_publish_cache`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
ALTER TABLE `rg_page_publish_log`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
   
ALTER TABLE `rg_page_template`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);  
   
ALTER TABLE `rg_page_ui_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `rg_page_ui_component_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `rg_page_ui_template`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);


#zaful
ALTER TABLE `zf_activity`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
alter table `zf_activity_group` 
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `zf_page`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `zf_page_convert_relation`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
ALTER TABLE `zf_page_group`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `zf_page_language`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `zf_page_layout_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `zf_page_layout_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`); 

ALTER TABLE `zf_page_publish_cache`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);  
   
ALTER TABLE `zf_page_publish_log`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
ALTER TABLE `zf_page_sync_platform_wait_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
   
ALTER TABLE `zf_page_template`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);  
   
ALTER TABLE `zf_page_ui_component`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `zf_page_ui_component_data`
  ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);
  
ALTER TABLE `zf_page_ui_template`
   ADD `sys_update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  ADD INDEX `sys_update_time` (`sys_update_time`);     