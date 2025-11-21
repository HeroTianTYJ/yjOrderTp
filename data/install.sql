CREATE TABLE `yjorder_express` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL DEFAULT '',
  `code` char(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_field` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL DEFAULT '',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
INSERT INTO `yjorder_field`(`id`,`name`,`is_default`) VALUES('1','订购数量','0');
INSERT INTO `yjorder_field`(`id`,`name`,`is_default`) VALUES('2','姓名','0');
INSERT INTO `yjorder_field`(`id`,`name`,`is_default`) VALUES('3','联系电话','0');
INSERT INTO `yjorder_field`(`id`,`name`,`is_default`) VALUES('4','所在地区（选填）','0');
INSERT INTO `yjorder_field`(`id`,`name`,`is_default`) VALUES('5','所在地区（手填）','0');
INSERT INTO `yjorder_field`(`id`,`name`,`is_default`) VALUES('6','街道地址','0');
INSERT INTO `yjorder_field`(`id`,`name`,`is_default`) VALUES('7','备注','0');
INSERT INTO `yjorder_field`(`id`,`name`,`is_default`) VALUES('8','电子邮箱','0');

CREATE TABLE `yjorder_login_record_manager` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `manager_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` char(39) NOT NULL DEFAULT '',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_manager` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL DEFAULT '',
  `pass` char(40) NOT NULL DEFAULT '',
  `level_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_activation` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `permit_group_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `order_permit_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `wechat_open_id` char(28) NOT NULL DEFAULT '',
  `wechat_union_id` char(28) NOT NULL DEFAULT '',
  `qq_open_id` char(32) NOT NULL DEFAULT '',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` char(13) NOT NULL DEFAULT '',
  `manager_id` int(10) unsigned NOT NULL DEFAULT '0',
  `template_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` char(20) NOT NULL DEFAULT '',
  `tel` char(20) NOT NULL DEFAULT '',
  `province` char(10) NOT NULL DEFAULT '',
  `city` char(15) NOT NULL DEFAULT '',
  `county` char(15) NOT NULL DEFAULT '',
  `town` char(25) NOT NULL DEFAULT '',
  `address` char(200) NOT NULL DEFAULT '',
  `note` char(255) NOT NULL DEFAULT '',
  `email` char(50) NOT NULL DEFAULT '',
  `ip` char(39) NOT NULL DEFAULT '',
  `referrer` char(255) NOT NULL DEFAULT '',
  `payment_id` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `pay_id` char(28) NOT NULL DEFAULT '',
  `pay_scene_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pay_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `order_state_id` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `express_id` int(10) unsigned NOT NULL DEFAULT '0',
  `express_number` char(30) NOT NULL DEFAULT '',
  `is_recycle` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_order_state` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL DEFAULT '',
  `color` char(20) NOT NULL DEFAULT '',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
INSERT INTO `yjorder_order_state`(`id`,`name`,`color`,`sort`,`is_default`,`create_time`) VALUES('1','待支付','#008000','1','1','2021-12-22 09:39:57');
INSERT INTO `yjorder_order_state`(`id`,`name`,`color`,`sort`,`is_default`,`create_time`) VALUES('2','待发货','#F00','2','0','2021-12-22 09:39:57');
INSERT INTO `yjorder_order_state`(`id`,`name`,`color`,`sort`,`is_default`,`create_time`) VALUES('3','已发货','#00F','3','0','2021-12-22 09:39:57');
INSERT INTO `yjorder_order_state`(`id`,`name`,`color`,`sort`,`is_default`,`create_time`) VALUES('4','已签收','#C60','4','0','2021-12-22 09:39:57');
INSERT INTO `yjorder_order_state`(`id`,`name`,`color`,`sort`,`is_default`,`create_time`) VALUES('5','售后中','#C06','5','0','2021-12-22 09:39:57');
INSERT INTO `yjorder_order_state`(`id`,`name`,`color`,`sort`,`is_default`,`create_time`) VALUES('6','交易关闭','#993','6','0','2021-12-22 09:39:57');

CREATE TABLE `yjorder_permit_data` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(7) NOT NULL DEFAULT '',
  `alias` varchar(20) NOT NULL DEFAULT '',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `parent_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('1','系统信息','system','0','0');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('2','版本号','version_code','0','1');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('3','更新时间','update_time','0','1');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('4','个人信息','profile','0','0');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('5','身份','level','0','4');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('6','权限组','permit_group','0','4');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('7','登录次数','login_count','0','4');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('8','上次登录时间','login_time','0','4');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('9','上次登录IP','login_ip','0','4');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('10','订单','order','0','0');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('11','总数','total','0','10');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('12','待支付','arrearage','0','10');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('13','待发货','undelivered','0','10');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('14','已发货','delivered','0','10');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('15','已签收','received','0','10');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('16','售后中','after_sale','0','10');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('17','交易关闭','closed','0','10');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('18','剩余订单量','count','0','10');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('19','商品','product','0','0');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('20','总数','total','0','19');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('21','运作商品','view_total','0','19');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('22','数据','data','0','0');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('23','今日网站PV','web_pv','0','22');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('24','文件','file','0','22');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('25','管理员','manager','0','0');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('26','总数','total','0','25');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('27','创始人','founder','0','25');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('28','超级管理员','super','0','25');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('29','普通管理员','general','0','25');
INSERT INTO `yjorder_permit_data`(`id`,`name`,`alias`,`is_default`,`parent_id`) VALUES('30','待激活','wait_activation','0','25');

CREATE TABLE `yjorder_permit_group` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL DEFAULT '',
  `text_id_permit_manage_ids` int(10) unsigned NOT NULL DEFAULT '0',
  `permit_data_ids` char(140) NOT NULL DEFAULT '',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_permit_manage` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(15) NOT NULL DEFAULT '',
  `controller` varchar(20) NOT NULL DEFAULT '',
  `action` varchar(20) NOT NULL DEFAULT '',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `parent_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('1','订单管理','Order','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('2','添加','','add','0','1');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('3','修改','','update','0','1');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('4','详情','','detail','0','1');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('5','导出','','output','0','1');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('6','删除','','delete','0','1');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('7','修改状态','','state','0','1');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('8','修改物流','','express','0','1');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('9','订单回收站','OrderRecycle','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('10','详情','','detail','0','9');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('11','导出','','output','0','9');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('12','还原','','recover','0','9');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('13','删除','','delete','0','9');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('14','订单统计','OrderStatistic','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('15','按天','','day','0','14');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('16','按月','','month','0','14');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('17','按年','','year','0','14');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('18','导出','','output','0','14');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('19','订单状态','OrderState','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('20','添加','','add','0','19');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('21','修改','','update','0','19');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('22','删除','','delete','0','19');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('23','设置默认','','isDefault','0','19');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('24','排序','','sort','0','19');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('25','快递公司','Express','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('26','添加','','add','0','25');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('27','修改','','update','0','25');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('28','删除','','delete','0','25');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('29','商品管理','Product','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('30','添加','','add','0','29');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('31','修改','','update','0','29');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('32','删除','','delete','0','29');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('33','上下架','','isView','0','29');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('34','设置默认','','isDefault','0','29');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('35','排序','','sort','0','29');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('36','商品分类','ProductSort','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('37','添加','','add','0','36');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('38','修改','','update','0','36');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('39','删除','','delete','0','36');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('40','排序','','sort','0','36');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('41','模板管理','Template','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('42','添加','','add','0','41');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('43','修改','','update','0','41');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('44','删除','','delete','0','41');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('45','获取代码','','code','0','41');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('46','设置默认','','isDefault','0','41');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('47','模板样式','TemplateStyle','','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('48','添加','','add','0','47');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('49','修改','','update','0','47');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('50','删除','','delete','0','47');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('51','下单字段','Field','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('52','设置默认','','isDefault','0','51');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('53','访问统计','Visit','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('54','导出','','output','0','53');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('55','更新JS','','js','0','53');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('56','文件管理','File','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('57','打包','','zip','0','56');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('58','下载','','download','0','56');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('59','删除','','delete','0','56');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('60','行政区划','District','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('61','添加','','add','0','60');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('62','批量添加','','multi','0','60');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('63','修改','','update','0','60');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('64','删除','','delete','0','60');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('65','管理员','Manager','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('66','添加','','add','0','65');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('67','修改','','update','0','65');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('68','删除','','delete','0','65');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('69','激活','','isActivation','0','65');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('70','解绑微信','','wechatOpenId','0','65');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('71','解绑QQ','','qqOpenId','0','65');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('72','登录记录','LoginRecordManager','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('73','导出并清空','','output','0','72');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('74','权限组','PermitGroup','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('75','添加','','add','0','74');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('76','修改','','update','0','74');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('77','删除','','delete','0','74');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('78','设置默认','','isDefault','0','74');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('79','管理权限','PermitManage','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('80','设置默认','','isDefault','0','79');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('81','数据权限','PermitData','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('82','设置默认','','isDefault','0','81');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('83','系统设置','System','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('84','生成验证文件','ValidateFile','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('85','SMTP服务器','Smtp','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('86','添加','','add','0','85');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('87','修改','','update','0','85');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('88','删除','','delete','0','85');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('89','运行状态','','state','0','85');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('90','数据表状态','Database','index','0','0');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('91','优化表','','optimize','0','90');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('92','修复AutoIncrement','','repairAutoIncrement','0','90');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('93','更新表缓存','','schema','0','90');
INSERT INTO `yjorder_permit_manage`(`id`,`name`,`controller`,`action`,`is_default`,`parent_id`) VALUES('94','数据库备份','DatabaseBackup','index','0','0');

CREATE TABLE `yjorder_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(30) NOT NULL DEFAULT '',
  `product_sort_id` int(10) unsigned NOT NULL DEFAULT '0',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `color` char(20) NOT NULL DEFAULT '',
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  `is_view` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_product_sort` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL DEFAULT '',
  `color` char(20) NOT NULL DEFAULT '',
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_smtp` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `smtp` char(20) NOT NULL DEFAULT '',
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `email` char(50) NOT NULL DEFAULT '',
  `pass` char(50) NOT NULL DEFAULT '',
  `from_name` char(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL DEFAULT '',
  `manager_id` int(10) unsigned NOT NULL DEFAULT '0',
  `template_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `template_style_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `product_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `product_sort_ids` char(255) NOT NULL DEFAULT '',
  `product_ids` char(255) NOT NULL DEFAULT '',
  `product_default` int(10) unsigned NOT NULL DEFAULT '0',
  `product_view_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_ids` char(15) NOT NULL DEFAULT '',
  `payment_ids` char(5) NOT NULL DEFAULT '',
  `payment_default_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_show_search` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_show_send` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_captcha` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `success` char(255) NOT NULL DEFAULT '',
  `success2` char(255) NOT NULL DEFAULT '',
  `often` char(255) NOT NULL DEFAULT '',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_template_style` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `bg_color` char(20) NOT NULL DEFAULT '',
  `border_color` char(20) NOT NULL DEFAULT '',
  `button_color` char(20) NOT NULL DEFAULT '',
  `select_current_bg_color` char(20) NOT NULL DEFAULT '',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('1','#EBFFEF','#0F3','#0C3','#0C3','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('2','#EBF7FF','#B8E3FF','#09F','#09F','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('3','#FFF0F0','#FFD9D9','#F66','#F66','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('4','#FFF7EB','#FFE3B8','#F90','#F90','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('5','#EBFFFF','#A6FFFF','#099','#099','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('6','#F2FFF9','#B2FFD9','#0C6','#0C6','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('7','#E6FAFF','#B2F0FF','#0CF','#0CF','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('8','#FFEBF0','#FFCCD9','#F36','#F36','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('9','#FFF4ED','#FFD9BF','#F60','#F60','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('10','#F2FFFF','#BFFFFF','#3CC','#3CC','2015-09-05 11:39:18');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('11','#FFF','#FC4400','#F63','#F63','2017-02-20 11:17:40');
INSERT INTO `yjorder_template_style`(`id`,`bg_color`,`border_color`,`button_color`,`select_current_bg_color`,`create_time`) VALUES('12','#FFF','#FFF','#BE0F22','#BE0F22','2019-12-16 11:40:26');

CREATE TABLE `yjorder_text` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `yjorder_visit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` char(39) NOT NULL DEFAULT '',
  `url` char(255) NOT NULL DEFAULT '',
  `count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `last_visit_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;