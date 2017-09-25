-- ----------------------------
-- 后台管理人员 t_member
-- ----------------------------
DROP TABLE IF EXISTS `t_member`;
CREATE TABLE `t_member` (
  `member_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '人员ID',
  `member_perms` set('product_read','product_export','product_import','product_create','product_update','product_delete') COMMENT '人员权限',
  `member_name` varchar(90) NOT NULL UNIQUE KEY COMMENT '用户名',
  `member_password` char(35) NOT NULL COMMENT '密码',
  `member_regtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `member_logtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后一次登录时间',
  `member_logip` char(15) NOT NULL DEFAULT '' COMMENT '最后一次登录ip',
  `member_lognum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '登录次数',
  `member_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='后台管理人员表';

-- admin@123456
INSERT INTO `t_member`(`member_perms`, `member_name`, `member_password`) VALUES ('product_read,product_export,product_import,product_create,product_update,product_delete', 'admin', '5f0bcb3a8f4a5b4381a25acb90168d74:30');

-- ----------------------------
-- Table structure for t_district
-- ----------------------------
DROP TABLE IF EXISTS `t_district`;
CREATE TABLE `t_district` (
  `district_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `district_name` varchar(255) NOT NULL UNIQUE KEY ,
  `district_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=525 DEFAULT CHARSET=utf8 COMMENT='中国省市数据表';

-- ----------------------------
-- Records of t_district
-- ----------------------------
INSERT INTO `t_district` VALUES ('1', '北京市', '0');
INSERT INTO `t_district` VALUES ('2', '天津市', '0');
INSERT INTO `t_district` VALUES ('9', '上海市', '0');
INSERT INTO `t_district` VALUES ('22', '重庆市', '0');
INSERT INTO `t_district` VALUES ('35', '海外', '0');
INSERT INTO `t_district` VALUES ('73', '石家庄市', '0');
INSERT INTO `t_district` VALUES ('74', '唐山市', '0');
INSERT INTO `t_district` VALUES ('75', '秦皇岛市', '0');
INSERT INTO `t_district` VALUES ('76', '邯郸市', '0');
INSERT INTO `t_district` VALUES ('77', '邢台市', '0');
INSERT INTO `t_district` VALUES ('78', '保定市', '0');
INSERT INTO `t_district` VALUES ('79', '张家口市', '0');
INSERT INTO `t_district` VALUES ('80', '承德市', '0');
INSERT INTO `t_district` VALUES ('81', '衡水市', '0');
INSERT INTO `t_district` VALUES ('82', '廊坊市', '0');
INSERT INTO `t_district` VALUES ('83', '沧州市', '0');
INSERT INTO `t_district` VALUES ('84', '太原市', '0');
INSERT INTO `t_district` VALUES ('85', '大同市', '0');
INSERT INTO `t_district` VALUES ('86', '阳泉市', '0');
INSERT INTO `t_district` VALUES ('87', '长治市', '0');
INSERT INTO `t_district` VALUES ('88', '晋城市', '0');
INSERT INTO `t_district` VALUES ('89', '朔州市', '0');
INSERT INTO `t_district` VALUES ('90', '晋中市', '0');
INSERT INTO `t_district` VALUES ('91', '运城市', '0');
INSERT INTO `t_district` VALUES ('92', '忻州市', '0');
INSERT INTO `t_district` VALUES ('93', '临汾市', '0');
INSERT INTO `t_district` VALUES ('94', '吕梁市', '0');
INSERT INTO `t_district` VALUES ('95', '呼和浩特市', '0');
INSERT INTO `t_district` VALUES ('96', '包头市', '0');
INSERT INTO `t_district` VALUES ('97', '乌海市', '0');
INSERT INTO `t_district` VALUES ('98', '赤峰市', '0');
INSERT INTO `t_district` VALUES ('99', '通辽市', '0');
INSERT INTO `t_district` VALUES ('100', '鄂尔多斯市', '0');
INSERT INTO `t_district` VALUES ('101', '呼伦贝尔市', '0');
INSERT INTO `t_district` VALUES ('102', '巴彦淖尔市', '0');
INSERT INTO `t_district` VALUES ('103', '乌兰察布市', '0');
INSERT INTO `t_district` VALUES ('107', '沈阳市', '0');
INSERT INTO `t_district` VALUES ('108', '大连市', '0');
INSERT INTO `t_district` VALUES ('109', '鞍山市', '0');
INSERT INTO `t_district` VALUES ('110', '抚顺市', '0');
INSERT INTO `t_district` VALUES ('111', '本溪市', '0');
INSERT INTO `t_district` VALUES ('112', '丹东市', '0');
INSERT INTO `t_district` VALUES ('113', '锦州市', '0');
INSERT INTO `t_district` VALUES ('114', '营口市', '0');
INSERT INTO `t_district` VALUES ('115', '阜新市', '0');
INSERT INTO `t_district` VALUES ('116', '辽阳市', '0');
INSERT INTO `t_district` VALUES ('117', '盘锦市', '0');
INSERT INTO `t_district` VALUES ('118', '铁岭市', '0');
INSERT INTO `t_district` VALUES ('119', '朝阳市', '0');
INSERT INTO `t_district` VALUES ('120', '葫芦岛市', '0');
INSERT INTO `t_district` VALUES ('121', '长春市', '0');
INSERT INTO `t_district` VALUES ('122', '吉林市', '0');
INSERT INTO `t_district` VALUES ('123', '四平市', '0');
INSERT INTO `t_district` VALUES ('124', '辽源市', '0');
INSERT INTO `t_district` VALUES ('125', '通化市', '0');
INSERT INTO `t_district` VALUES ('126', '白山市', '0');
INSERT INTO `t_district` VALUES ('127', '松原市', '0');
INSERT INTO `t_district` VALUES ('128', '白城市', '0');
INSERT INTO `t_district` VALUES ('130', '哈尔滨市', '0');
INSERT INTO `t_district` VALUES ('131', '齐齐哈尔市', '0');
INSERT INTO `t_district` VALUES ('132', '鸡西市', '0');
INSERT INTO `t_district` VALUES ('133', '鹤岗市', '0');
INSERT INTO `t_district` VALUES ('134', '双鸭山市', '0');
INSERT INTO `t_district` VALUES ('135', '大庆市', '0');
INSERT INTO `t_district` VALUES ('136', '伊春市', '0');
INSERT INTO `t_district` VALUES ('137', '佳木斯市', '0');
INSERT INTO `t_district` VALUES ('138', '七台河市', '0');
INSERT INTO `t_district` VALUES ('139', '牡丹江市', '0');
INSERT INTO `t_district` VALUES ('140', '黑河市', '0');
INSERT INTO `t_district` VALUES ('141', '绥化市', '0');
INSERT INTO `t_district` VALUES ('162', '南京市', '0');
INSERT INTO `t_district` VALUES ('163', '无锡市', '0');
INSERT INTO `t_district` VALUES ('164', '徐州市', '0');
INSERT INTO `t_district` VALUES ('165', '常州市', '0');
INSERT INTO `t_district` VALUES ('166', '苏州市', '0');
INSERT INTO `t_district` VALUES ('167', '南通市', '0');
INSERT INTO `t_district` VALUES ('168', '连云港市', '0');
INSERT INTO `t_district` VALUES ('169', '淮安市', '0');
INSERT INTO `t_district` VALUES ('170', '盐城市', '0');
INSERT INTO `t_district` VALUES ('171', '扬州市', '0');
INSERT INTO `t_district` VALUES ('172', '镇江市', '0');
INSERT INTO `t_district` VALUES ('173', '泰州市', '0');
INSERT INTO `t_district` VALUES ('174', '宿迁市', '0');
INSERT INTO `t_district` VALUES ('175', '杭州市', '0');
INSERT INTO `t_district` VALUES ('176', '宁波市', '0');
INSERT INTO `t_district` VALUES ('177', '温州市', '0');
INSERT INTO `t_district` VALUES ('178', '嘉兴市', '0');
INSERT INTO `t_district` VALUES ('179', '湖州市', '0');
INSERT INTO `t_district` VALUES ('180', '绍兴市', '0');
INSERT INTO `t_district` VALUES ('181', '舟山市', '0');
INSERT INTO `t_district` VALUES ('182', '衢州市', '0');
INSERT INTO `t_district` VALUES ('183', '金华市', '0');
INSERT INTO `t_district` VALUES ('184', '台州市', '0');
INSERT INTO `t_district` VALUES ('185', '丽水市', '0');
INSERT INTO `t_district` VALUES ('186', '合肥市', '0');
INSERT INTO `t_district` VALUES ('187', '芜湖市', '0');
INSERT INTO `t_district` VALUES ('188', '蚌埠市', '0');
INSERT INTO `t_district` VALUES ('189', '淮南市', '0');
INSERT INTO `t_district` VALUES ('190', '马鞍山市', '0');
INSERT INTO `t_district` VALUES ('191', '淮北市', '0');
INSERT INTO `t_district` VALUES ('192', '铜陵市', '0');
INSERT INTO `t_district` VALUES ('194', '黄山市', '0');
INSERT INTO `t_district` VALUES ('195', '滁州市', '0');
INSERT INTO `t_district` VALUES ('196', '阜阳市', '0');
INSERT INTO `t_district` VALUES ('197', '宿州市', '0');
INSERT INTO `t_district` VALUES ('198', '巢湖市', '0');
INSERT INTO `t_district` VALUES ('199', '六安市', '0');
INSERT INTO `t_district` VALUES ('200', '亳州市', '0');
INSERT INTO `t_district` VALUES ('201', '池州市', '0');
INSERT INTO `t_district` VALUES ('202', '宣城市', '0');
INSERT INTO `t_district` VALUES ('203', '福州市', '0');
INSERT INTO `t_district` VALUES ('204', '厦门市', '0');
INSERT INTO `t_district` VALUES ('205', '莆田市', '0');
INSERT INTO `t_district` VALUES ('206', '三明市', '0');
INSERT INTO `t_district` VALUES ('207', '泉州市', '0');
INSERT INTO `t_district` VALUES ('208', '漳州市', '0');
INSERT INTO `t_district` VALUES ('209', '南平市', '0');
INSERT INTO `t_district` VALUES ('210', '龙岩市', '0');
INSERT INTO `t_district` VALUES ('211', '宁德市', '0');
INSERT INTO `t_district` VALUES ('212', '南昌市', '0');
INSERT INTO `t_district` VALUES ('213', '景德镇市', '0');
INSERT INTO `t_district` VALUES ('214', '萍乡市', '0');
INSERT INTO `t_district` VALUES ('215', '九江市', '0');
INSERT INTO `t_district` VALUES ('216', '新余市', '0');
INSERT INTO `t_district` VALUES ('217', '鹰潭市', '0');
INSERT INTO `t_district` VALUES ('218', '赣州市', '0');
INSERT INTO `t_district` VALUES ('219', '吉安市', '0');
INSERT INTO `t_district` VALUES ('220', '宜春市', '0');
INSERT INTO `t_district` VALUES ('221', '抚州市', '0');
INSERT INTO `t_district` VALUES ('222', '上饶市', '0');
INSERT INTO `t_district` VALUES ('223', '济南市', '0');
INSERT INTO `t_district` VALUES ('224', '青岛市', '0');
INSERT INTO `t_district` VALUES ('225', '淄博市', '0');
INSERT INTO `t_district` VALUES ('226', '枣庄市', '0');
INSERT INTO `t_district` VALUES ('227', '东营市', '0');
INSERT INTO `t_district` VALUES ('228', '烟台市', '0');
INSERT INTO `t_district` VALUES ('229', '潍坊市', '0');
INSERT INTO `t_district` VALUES ('230', '济宁市', '0');
INSERT INTO `t_district` VALUES ('231', '泰安市', '0');
INSERT INTO `t_district` VALUES ('232', '威海市', '0');
INSERT INTO `t_district` VALUES ('233', '日照市', '0');
INSERT INTO `t_district` VALUES ('234', '莱芜市', '0');
INSERT INTO `t_district` VALUES ('235', '临沂市', '0');
INSERT INTO `t_district` VALUES ('236', '德州市', '0');
INSERT INTO `t_district` VALUES ('237', '聊城市', '0');
INSERT INTO `t_district` VALUES ('238', '滨州市', '0');
INSERT INTO `t_district` VALUES ('239', '菏泽市', '0');
INSERT INTO `t_district` VALUES ('240', '郑州市', '0');
INSERT INTO `t_district` VALUES ('241', '开封市', '0');
INSERT INTO `t_district` VALUES ('242', '洛阳市', '0');
INSERT INTO `t_district` VALUES ('243', '平顶山市', '0');
INSERT INTO `t_district` VALUES ('244', '安阳市', '0');
INSERT INTO `t_district` VALUES ('245', '鹤壁市', '0');
INSERT INTO `t_district` VALUES ('246', '新乡市', '0');
INSERT INTO `t_district` VALUES ('247', '焦作市', '0');
INSERT INTO `t_district` VALUES ('248', '濮阳市', '0');
INSERT INTO `t_district` VALUES ('249', '许昌市', '0');
INSERT INTO `t_district` VALUES ('250', '漯河市', '0');
INSERT INTO `t_district` VALUES ('251', '三门峡市', '0');
INSERT INTO `t_district` VALUES ('252', '南阳市', '0');
INSERT INTO `t_district` VALUES ('253', '商丘市', '0');
INSERT INTO `t_district` VALUES ('254', '信阳市', '0');
INSERT INTO `t_district` VALUES ('255', '周口市', '0');
INSERT INTO `t_district` VALUES ('256', '驻马店市', '0');
INSERT INTO `t_district` VALUES ('257', '济源市', '0');
INSERT INTO `t_district` VALUES ('258', '武汉市', '0');
INSERT INTO `t_district` VALUES ('259', '黄石市', '0');
INSERT INTO `t_district` VALUES ('260', '十堰市', '0');
INSERT INTO `t_district` VALUES ('261', '宜昌市', '0');
INSERT INTO `t_district` VALUES ('262', '襄樊市', '0');
INSERT INTO `t_district` VALUES ('263', '鄂州市', '0');
INSERT INTO `t_district` VALUES ('264', '荆门市', '0');
INSERT INTO `t_district` VALUES ('265', '孝感市', '0');
INSERT INTO `t_district` VALUES ('266', '荆州市', '0');
INSERT INTO `t_district` VALUES ('267', '黄冈市', '0');
INSERT INTO `t_district` VALUES ('268', '咸宁市', '0');
INSERT INTO `t_district` VALUES ('269', '随州市', '0');
INSERT INTO `t_district` VALUES ('271', '仙桃市', '0');
INSERT INTO `t_district` VALUES ('272', '潜江市', '0');
INSERT INTO `t_district` VALUES ('273', '天门市', '0');
INSERT INTO `t_district` VALUES ('275', '长沙市', '0');
INSERT INTO `t_district` VALUES ('276', '株洲市', '0');
INSERT INTO `t_district` VALUES ('277', '湘潭市', '0');
INSERT INTO `t_district` VALUES ('278', '衡阳市', '0');
INSERT INTO `t_district` VALUES ('279', '邵阳市', '0');
INSERT INTO `t_district` VALUES ('280', '岳阳市', '0');
INSERT INTO `t_district` VALUES ('281', '常德市', '0');
INSERT INTO `t_district` VALUES ('282', '张家界市', '0');
INSERT INTO `t_district` VALUES ('283', '益阳市', '0');
INSERT INTO `t_district` VALUES ('284', '郴州市', '0');
INSERT INTO `t_district` VALUES ('285', '永州市', '0');
INSERT INTO `t_district` VALUES ('286', '怀化市', '0');
INSERT INTO `t_district` VALUES ('287', '娄底市', '0');
INSERT INTO `t_district` VALUES ('289', '广州市', '0');
INSERT INTO `t_district` VALUES ('290', '韶关市', '0');
INSERT INTO `t_district` VALUES ('291', '深圳市', '0');
INSERT INTO `t_district` VALUES ('292', '珠海市', '0');
INSERT INTO `t_district` VALUES ('293', '汕头市', '0');
INSERT INTO `t_district` VALUES ('294', '佛山市', '0');
INSERT INTO `t_district` VALUES ('295', '江门市', '0');
INSERT INTO `t_district` VALUES ('296', '湛江市', '0');
INSERT INTO `t_district` VALUES ('297', '茂名市', '0');
INSERT INTO `t_district` VALUES ('298', '肇庆市', '0');
INSERT INTO `t_district` VALUES ('299', '惠州市', '0');
INSERT INTO `t_district` VALUES ('300', '梅州市', '0');
INSERT INTO `t_district` VALUES ('301', '汕尾市', '0');
INSERT INTO `t_district` VALUES ('302', '河源市', '0');
INSERT INTO `t_district` VALUES ('303', '阳江市', '0');
INSERT INTO `t_district` VALUES ('304', '清远市', '0');
INSERT INTO `t_district` VALUES ('305', '东莞市', '0');
INSERT INTO `t_district` VALUES ('306', '中山市', '0');
INSERT INTO `t_district` VALUES ('307', '潮州市', '0');
INSERT INTO `t_district` VALUES ('308', '揭阳市', '0');
INSERT INTO `t_district` VALUES ('309', '云浮市', '0');
INSERT INTO `t_district` VALUES ('310', '南宁市', '0');
INSERT INTO `t_district` VALUES ('311', '柳州市', '0');
INSERT INTO `t_district` VALUES ('312', '桂林市', '0');
INSERT INTO `t_district` VALUES ('313', '梧州市', '0');
INSERT INTO `t_district` VALUES ('314', '北海市', '0');
INSERT INTO `t_district` VALUES ('315', '防城港市', '0');
INSERT INTO `t_district` VALUES ('316', '钦州市', '0');
INSERT INTO `t_district` VALUES ('317', '贵港市', '0');
INSERT INTO `t_district` VALUES ('318', '玉林市', '0');
INSERT INTO `t_district` VALUES ('319', '百色市', '0');
INSERT INTO `t_district` VALUES ('320', '贺州市', '0');
INSERT INTO `t_district` VALUES ('321', '河池市', '0');
INSERT INTO `t_district` VALUES ('322', '来宾市', '0');
INSERT INTO `t_district` VALUES ('323', '崇左市', '0');
INSERT INTO `t_district` VALUES ('324', '海口市', '0');
INSERT INTO `t_district` VALUES ('325', '三亚市', '0');
INSERT INTO `t_district` VALUES ('326', '五指山市', '0');
INSERT INTO `t_district` VALUES ('327', '琼海市', '0');
INSERT INTO `t_district` VALUES ('328', '儋州市', '0');
INSERT INTO `t_district` VALUES ('329', '文昌市', '0');
INSERT INTO `t_district` VALUES ('330', '万宁市', '0');
INSERT INTO `t_district` VALUES ('331', '东方市', '0');
INSERT INTO `t_district` VALUES ('381', '江津市', '0');
INSERT INTO `t_district` VALUES ('382', '合川市', '0');
INSERT INTO `t_district` VALUES ('383', '永川市', '0');
INSERT INTO `t_district` VALUES ('384', '南川市', '0');
INSERT INTO `t_district` VALUES ('385', '成都市', '0');
INSERT INTO `t_district` VALUES ('386', '自贡市', '0');
INSERT INTO `t_district` VALUES ('387', '攀枝花市', '0');
INSERT INTO `t_district` VALUES ('388', '泸州市', '0');
INSERT INTO `t_district` VALUES ('389', '德阳市', '0');
INSERT INTO `t_district` VALUES ('390', '绵阳市', '0');
INSERT INTO `t_district` VALUES ('391', '广元市', '0');
INSERT INTO `t_district` VALUES ('392', '遂宁市', '0');
INSERT INTO `t_district` VALUES ('393', '内江市', '0');
INSERT INTO `t_district` VALUES ('394', '乐山市', '0');
INSERT INTO `t_district` VALUES ('395', '南充市', '0');
INSERT INTO `t_district` VALUES ('396', '眉山市', '0');
INSERT INTO `t_district` VALUES ('397', '宜宾市', '0');
INSERT INTO `t_district` VALUES ('398', '广安市', '0');
INSERT INTO `t_district` VALUES ('399', '达州市', '0');
INSERT INTO `t_district` VALUES ('400', '雅安市', '0');
INSERT INTO `t_district` VALUES ('401', '巴中市', '0');
INSERT INTO `t_district` VALUES ('402', '资阳市', '0');
INSERT INTO `t_district` VALUES ('406', '贵阳市', '0');
INSERT INTO `t_district` VALUES ('407', '六盘水市', '0');
INSERT INTO `t_district` VALUES ('408', '遵义市', '0');
INSERT INTO `t_district` VALUES ('415', '昆明市', '0');
INSERT INTO `t_district` VALUES ('416', '曲靖市', '0');
INSERT INTO `t_district` VALUES ('417', '玉溪市', '0');
INSERT INTO `t_district` VALUES ('418', '保山市', '0');
INSERT INTO `t_district` VALUES ('419', '昭通市', '0');
INSERT INTO `t_district` VALUES ('420', '丽江市', '0');
INSERT INTO `t_district` VALUES ('421', '思茅市', '0');
INSERT INTO `t_district` VALUES ('422', '临沧市', '0');
INSERT INTO `t_district` VALUES ('431', '拉萨市', '0');
INSERT INTO `t_district` VALUES ('438', '西安市', '0');
INSERT INTO `t_district` VALUES ('439', '铜川市', '0');
INSERT INTO `t_district` VALUES ('440', '宝鸡市', '0');
INSERT INTO `t_district` VALUES ('441', '咸阳市', '0');
INSERT INTO `t_district` VALUES ('442', '渭南市', '0');
INSERT INTO `t_district` VALUES ('443', '延安市', '0');
INSERT INTO `t_district` VALUES ('444', '汉中市', '0');
INSERT INTO `t_district` VALUES ('445', '榆林市', '0');
INSERT INTO `t_district` VALUES ('447', '商洛市', '0');
INSERT INTO `t_district` VALUES ('448', '兰州市', '0');
INSERT INTO `t_district` VALUES ('449', '嘉峪关市', '0');
INSERT INTO `t_district` VALUES ('450', '金昌市', '0');
INSERT INTO `t_district` VALUES ('451', '白银市', '0');
INSERT INTO `t_district` VALUES ('452', '天水市', '0');
INSERT INTO `t_district` VALUES ('453', '武威市', '0');
INSERT INTO `t_district` VALUES ('454', '张掖市', '0');
INSERT INTO `t_district` VALUES ('455', '平凉市', '0');
INSERT INTO `t_district` VALUES ('456', '酒泉市', '0');
INSERT INTO `t_district` VALUES ('457', '庆阳市', '0');
INSERT INTO `t_district` VALUES ('458', '定西市', '0');
INSERT INTO `t_district` VALUES ('459', '陇南市', '0');
INSERT INTO `t_district` VALUES ('462', '西宁市', '0');
INSERT INTO `t_district` VALUES ('470', '银川市', '0');
INSERT INTO `t_district` VALUES ('471', '石嘴山市', '0');
INSERT INTO `t_district` VALUES ('472', '吴忠市', '0');
INSERT INTO `t_district` VALUES ('473', '固原市', '0');
INSERT INTO `t_district` VALUES ('474', '中卫市', '0');
INSERT INTO `t_district` VALUES ('475', '乌鲁木齐市', '0');
INSERT INTO `t_district` VALUES ('476', '克拉玛依市', '0');
INSERT INTO `t_district` VALUES ('489', '石河子市', '0');
INSERT INTO `t_district` VALUES ('491', '图木舒克市', '0');
INSERT INTO `t_district` VALUES ('492', '五家渠市', '0');
INSERT INTO `t_district` VALUES ('493', '台北市', '0');
INSERT INTO `t_district` VALUES ('494', '高雄市', '0');
INSERT INTO `t_district` VALUES ('495', '基隆市', '0');
INSERT INTO `t_district` VALUES ('496', '台中市', '0');
INSERT INTO `t_district` VALUES ('497', '台南市', '0');
INSERT INTO `t_district` VALUES ('498', '新竹市', '0');
INSERT INTO `t_district` VALUES ('499', '嘉义市', '0');

-- ----------------------------
-- 会员表 t_customers
-- ----------------------------
DROP TABLE IF EXISTS `t_customers`;
CREATE TABLE `t_customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_openid` varchar(100) NOT NULL DEFAULT '' COMMENT '微信ID',
  `customer_gender` varchar(6) COMMENT '性别',
  `customer_age` varchar(10) COMMENT '年龄段',
  `customer_name` char(16) NOT NULL DEFAULT '' COMMENT '名字',
  `customer_headimg` varchar(45) COMMENT '头像',
  `customer_regtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `district_id` int(11) COMMENT '常住城市ID',
  `customer_default_address_id` int(5) NOT NULL DEFAULT '0' COMMENT '默认收货地址',
  `customer_tel` varchar(32) COMMENT '电话',
  `customer_score` mediumint(8) NOT NULL DEFAULT '0' COMMENT '积分',
  `customer_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员表';

-- ----------------------------
-- 会员收货地址表 t_customer_address
-- ----------------------------
DROP TABLE IF EXISTS `t_customer_address`;
CREATE TABLE `t_customer_address` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `district_id` int(11) COMMENT '城市ID',
  `user_address` varchar(200) NOT NULL COMMENT '收货地址',
  `user_name` varchar(32) NOT NULL COMMENT '收货人',
  `user_tel` varchar(32) NOT NULL COMMENT '联系电话',
  `user_des` varchar(200) COMMENT '特殊说明',
  `address_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `address_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员收货地址';

-- ----------------------------
-- 会员积分日志表 t_customer_score_log
-- ----------------------------
DROP TABLE IF EXISTS `t_customer_score_log`;
CREATE TABLE `t_customer_score_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `score_type` enum('have', 'buy') NOT NULL COMMENT '积分行为：buy购买商品；have获得积分',
  `score_des` varchar(20) COMMENT '积分说明',
  `score_quantity` int(8) NOT NULL DEFAULT 0 COMMENT '积分数',
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员积分日志';

-- ----------------------------
-- 会员积分级别表 t_customer_score_level
-- ----------------------------
DROP TABLE IF EXISTS `t_customer_score_level`;
CREATE TABLE `t_customer_score_level` (
  `level_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `level_name` varchar(32) NOT NULL UNIQUE KEY COMMENT '级别名称',
  `level_score` mediumint(8) NOT NULL UNIQUE KEY COMMENT '所需积分',
  `level_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员积分级别';

-- ----------------------------
-- 会员登录日志表 t_customer_login_log
-- ----------------------------
DROP TABLE IF EXISTS `t_customer_login_log`;
CREATE TABLE `t_customer_login_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `district_id` int(11) COMMENT '城市ID',
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '登录时间',
  `log_ip` char(15) NOT NULL DEFAULT '' COMMENT '登录IP'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员登录日志';

-- ----------------------------
-- 会员搜索日志表 t_customer_search_log
-- ----------------------------
DROP TABLE IF EXISTS `t_customer_search_log`;
CREATE TABLE `t_customer_search_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `log_name` varchar(60) NOT NULL COMMENT '搜索名字',
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员搜索日志';

-- ----------------------------
-- 系统配置表 t_configs
-- ----------------------------
DROP TABLE IF EXISTS `t_configs`;
CREATE TABLE `t_configs` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `config_page` varchar(32) NOT NULL COMMENT '页面',
  `config_title` varchar(32) NOT NULL COMMENT '标题',
  `config_text` text COMMENT '内容',
  `config_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `config_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='系统配置';

-- ----------------------------
-- 首页图片表 t_images
-- ----------------------------
DROP TABLE IF EXISTS `t_images`;
CREATE TABLE `t_images` (
  `image_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `image_title` varchar(32) COMMENT '标题',
  `district_id` tinyint(4) NOT NULL COMMENT '城市ID',
  `image_path` varchar(45) NOT NULL COMMENT '图片路径',
  `image_href` varchar(200) COMMENT '图片链接',
  `image_sort` int(5) NOT NULL DEFAULT '0' COMMENT '排序',
  `image_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `image_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='首页图片';

-- ----------------------------
-- 素材视频表 t_videos
-- ----------------------------
DROP TABLE IF EXISTS `t_videos`;
CREATE TABLE `t_videos` (
  `video_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `video_path` varchar(45) NOT NULL COMMENT '视频路径',
  `video_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `video_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='素材视频';

-- ----------------------------
-- 阅读任务表 t_task_reads
-- ----------------------------
DROP TABLE IF EXISTS `t_task_reads`;
CREATE TABLE `t_task_reads` (
  `read_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `read_title` varchar(32) NOT NULL COMMENT '标题',
  `read_banner` varchar(45) NOT NULL COMMENT '缩略图路径',
  `read_text` text COMMENT '内容',
  `read_num` tinyint(4) NOT NULL DEFAULT '1' COMMENT '每天阅读次数',
  `read_score` mediumint(8) NOT NULL DEFAULT '0' COMMENT '阅读积分',
  `read_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `read_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='阅读任务';

-- ----------------------------
-- 答题任务表 t_task_questions
-- ----------------------------
DROP TABLE IF EXISTS `t_task_questions`;
CREATE TABLE `t_task_questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `question_title` varchar(200) NOT NULL COMMENT '问题描述',
  `question_banner` varchar(45) NOT NULL COMMENT '缩略图路径',
  `question_a` varchar(200) NOT NULL COMMENT '选项A',
  `question_b` varchar(200) NOT NULL COMMENT '选项B',
  `question_c` varchar(200) NOT NULL COMMENT '选项C',
  `question_d` varchar(200) NOT NULL COMMENT '选项D',
  `question_e` varchar(200) NOT NULL COMMENT '选项E',
  `question_f` varchar(200) NOT NULL COMMENT '选项F',
  `question_answer` varchar(20) NOT NULL COMMENT '答案',
  `question_answer_num` tinyint(1) NOT NULL DEFAULT '1' COMMENT '答案个数',
  `question_score` mediumint(8) NOT NULL DEFAULT '0' COMMENT '答题积分',
  `question_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `question_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='答题任务';


-- ----------------------------
-- 转盘表 t_turntable
-- ----------------------------
DROP TABLE IF EXISTS `t_turntable`;
CREATE TABLE `t_turntable` (
  `turntable_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `turntable_num` varchar(200) NOT NULL COMMENT '每天次数',
  `turntable_use_score` mediumint(8) NOT NULL DEFAULT '10' COMMENT '兑换一次消耗积分'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='转盘';

-- ----------------------------
-- 转盘奖品表 t_turntable_products
-- ----------------------------
DROP TABLE IF EXISTS `t_turntable_products`;
CREATE TABLE `t_turntable_products` (
  `turntablep_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `turntablep_title` varchar(200) NOT NULL COMMENT '奖品名称',
  `turntablep_attr` enum('product','score','thank') NOT NULL COMMENT '类型',
  `turntablep_image` varchar(45) COMMENT '商品图片',
  `turntablep_score` varchar(45) COMMENT '积分数量',
  `turntablep_probability` int(3) NOT NULL COMMENT '概率',
  `turntablep_sort` int(3) NOT NULL DEFAULT '0' COMMENT '排序',
  `turntablep_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `turntablep_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='转盘奖品';

-- ----------------------------
-- 会员奖品表 t_prizes
-- ----------------------------
DROP TABLE IF EXISTS `t_prizes`;
CREATE TABLE `t_prizes` (
  `prize_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `turntablep_id` int(11) NOT NULL COMMENT '奖品ID',
  `prize_address` text COMMENT '收货地址',
  `prize_attr` enum('pending','shipped','received') NOT NULL DEFAULT 'pending' COMMENT '订单状态：pending正在发货；shipped已发货；received已收货',
  `prize_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `prize_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员奖品';

-- ----------------------------
-- 商家表 t_shops
-- ----------------------------
DROP TABLE IF EXISTS `t_shops`;
CREATE TABLE `t_shops` (
  `shop_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `shop_name` varchar(255) NOT NULL UNIQUE KEY COMMENT '名字',
  `shop_headimg` varchar(60) NOT NULL COMMENT '商家头像',
  `shop_tel` varchar(32) NOT NULL DEFAULT '' COMMENT '电话',
  `shop_address` varchar(200) NOT NULL DEFAULT '0' COMMENT '地址',
  `shop_lat` decimal(20,17) NOT NULL COMMENT '纬度',
  `shop_lng` decimal(20,17) NOT NULL COMMENT '经度',
  `district_id` int(11) NOT NULL COMMENT '城市ID',
  `shop_dec` text COMMENT '特殊说明',
  `shop_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `shop_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商家';

-- ----------------------------
-- Table structure for t_products
-- ----------------------------
DROP TABLE IF EXISTS `t_products`;
CREATE TABLE `t_products` (
  `product_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '商品ID',
  `district_id` tinyint(4) NOT NULL COMMENT '城市ID',
  `product_code` char(12) NOT NULL UNIQUE KEY COMMENT '商品CODE',
  `product_name` varchar(150) NOT NULL COMMENT '商品名称',
  `product_quantity` smallint(6) NOT NULL DEFAULT '0' COMMENT '库存数量',
  `product_price` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `product_virtual_price` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '商品虚拟价格',
  `attr_id` int(11) NOT NULL COMMENT '商品类别',
  `product_desc` varchar(225) NOT NULL DEFAULT '' COMMENT '商品推荐说明',
  `product_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '商品添加时间',
  `product_end` timestamp NOT NULL COMMENT '商品下架时间',
  `product_qr_code_day` int(3) NOT NULL COMMENT '领取二维码有效时长(天)',
  `product_sort` int(3) DEFAULT 0 COMMENT '商品排序',
  `product_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '商品状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品详情表';

-- ----------------------------
-- Table structure for t_product_attr
-- ----------------------------
DROP TABLE IF EXISTS `t_product_attr`;
CREATE TABLE `t_product_attr` (
  `attr_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `attr_name` varchar(32) NOT NULL COMMENT '类别名字',
  `attr_sort` int(3) DEFAULT 0 COMMENT '类别排序',
  `attr_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品类别表';

-- ----------------------------
-- Table structure for t_product_images
-- ----------------------------
DROP TABLE IF EXISTS `t_product_images`;
CREATE TABLE `t_product_images` (
  `image_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `image_path` varchar(45) NOT NULL COMMENT '商品图片',
  `image_type` enum('home','banner','detail') NOT NULL COMMENT '图片类型：home首页图片；banner详情顶部图片；detail详情底部图片',
  `image_sort` int(3) DEFAULT 0 COMMENT '排序',
  `image_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品图片表';

-- ----------------------------
-- Table structure for t_product_quantity
-- ----------------------------
DROP TABLE IF EXISTS `t_product_quantity`;
CREATE TABLE `t_product_quantity` (
  `quantity_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `shop_id` int(11) NOT NULL COMMENT '商家ID',
  `quantity_num` smallint(6) NOT NULL DEFAULT '0' COMMENT '商家库存数量'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品库存分配表';

-- ----------------------------
-- Table structure for t_reviews
-- ----------------------------
DROP TABLE IF EXISTS `t_reviews`;
CREATE TABLE `t_reviews` (
  `review_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '顾客ID',
  `product_id` int(10) NOT NULL COMMENT '商品ID',
  `review_content` text NOT NULL COMMENT '评论内容',
  `review_score` decimal(2,1) NOT NULL DEFAULT '0' COMMENT '评论得分',
  `review_vote_up` int(5) NOT NULL DEFAULT '0' COMMENT '评论获赞数量',
  `review_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '评论时间',
  `review_attr` enum('unread','pending','published') NOT NULL COMMENT '评论状态：unread不可读；pending审核中；published发布',
  `review_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品评论表';

-- ----------------------------
-- Table structure for t_review_images
-- ----------------------------
DROP TABLE IF EXISTS `t_review_images`;
CREATE TABLE `t_review_images` (
  `image_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `review_id` int(11) NOT NULL COMMENT '商品ID',
  `image_path` varchar(45) NOT NULL COMMENT '评论图片',
  `image_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='评论图片表';