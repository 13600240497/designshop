/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` smallint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员id|自增',
  `department_id` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '所属部门id',
  `role_id` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '角色id',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `realname` char(30) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `email` char(50) NOT NULL DEFAULT '',
  `phone` char(15) NOT NULL DEFAULT '' COMMENT '电话',
  `is_leader` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否负责人|0否,1是',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用|0否,1是',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `last_login_ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录ip',
  `logins` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '登陆次数',
  `text` text NOT NULL COMMENT 'text字段',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员|马善灵|2016-12-03';
DROP TABLE IF EXISTS `department`;
CREATE TABLE `department` (
  `id` smallint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '部门id|自增',
  `parent_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `node` char(50) NOT NULL DEFAULT '' COMMENT '节点|...,父父id,父id,id',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '部门名称',
  `memo` char(100) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='部门|马善灵|2016-12-03';
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` smallint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单id|自增',
  `parent_id` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `route` char(60) NOT NULL DEFAULT '' COMMENT 'php路由',
  `front_page_route` char(60) NOT NULL DEFAULT '' COMMENT '前端页面路由',
  `icon_class` char(30) NOT NULL DEFAULT '' COMMENT '图标class',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否在菜单中显示|0否,1是',
  `is_leader_permission` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否负责人权限|0否,1是',
  `order` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序|越小越靠前',
  `node` char(50) NOT NULL DEFAULT '' COMMENT '节点|...,父父id,父id,id',
  `memo` char(100) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `route` (`route`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='后台菜单|马善灵|2016-12-03';

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
