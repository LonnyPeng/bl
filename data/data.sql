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
-- Table structure for 
-- ----------------------------
DROP TABLE IF EXISTS `t_perms`;
CREATE TABLE `t_perms` (
  `perm_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `perm_url` varchar(90) NOT NULL COMMENT '路径',
  `perm_value` int(2) NOT NULL DEFAULT '0' COMMENT '权值',
  `member_id` smallint(5) NOT NULL COMMENT '人员ID'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='平台权限表';

-- ----------------------------
-- Table structure for t_district
-- ----------------------------
DROP TABLE IF EXISTS `t_district`;
CREATE TABLE `t_district` (
  `district_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `district_initial` varchar(1) DEFAULT '',
  `district_name` varchar(255) NOT NULL UNIQUE KEY ,
  `district_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=525 DEFAULT CHARSET=utf8 COMMENT='中国省市数据表';

-- ----------------------------
-- Records of t_district
-- ----------------------------
INSERT INTO `t_district` VALUES ('1', 'B', '北京市', '1');
INSERT INTO `t_district` VALUES ('2', 'T', '天津市', '0');
INSERT INTO `t_district` VALUES ('9', 'S', '上海市', '1');
INSERT INTO `t_district` VALUES ('22', 'C', '重庆市', '0');
INSERT INTO `t_district` VALUES ('35', 'H', '海外', '0');
INSERT INTO `t_district` VALUES ('73', 'S', '石家庄市', '0');
INSERT INTO `t_district` VALUES ('74', 'T', '唐山市', '0');
INSERT INTO `t_district` VALUES ('75', 'Q', '秦皇岛市', '0');
INSERT INTO `t_district` VALUES ('76', 'H', '邯郸市', '0');
INSERT INTO `t_district` VALUES ('77', 'X', '邢台市', '0');
INSERT INTO `t_district` VALUES ('78', 'B', '保定市', '0');
INSERT INTO `t_district` VALUES ('79', 'Z', '张家口市', '0');
INSERT INTO `t_district` VALUES ('80', 'C', '承德市', '0');
INSERT INTO `t_district` VALUES ('81', 'H', '衡水市', '0');
INSERT INTO `t_district` VALUES ('82', 'L', '廊坊市', '0');
INSERT INTO `t_district` VALUES ('83', 'C', '沧州市', '0');
INSERT INTO `t_district` VALUES ('84', 'T', '太原市', '0');
INSERT INTO `t_district` VALUES ('85', 'D', '大同市', '0');
INSERT INTO `t_district` VALUES ('86', 'Y', '阳泉市', '0');
INSERT INTO `t_district` VALUES ('87', 'C', '长治市', '0');
INSERT INTO `t_district` VALUES ('88', 'J', '晋城市', '0');
INSERT INTO `t_district` VALUES ('89', 'S', '朔州市', '0');
INSERT INTO `t_district` VALUES ('90', 'J', '晋中市', '0');
INSERT INTO `t_district` VALUES ('91', 'Y', '运城市', '0');
INSERT INTO `t_district` VALUES ('92', 'X', '忻州市', '0');
INSERT INTO `t_district` VALUES ('93', 'L', '临汾市', '0');
INSERT INTO `t_district` VALUES ('94', 'L', '吕梁市', '0');
INSERT INTO `t_district` VALUES ('95', 'H', '呼和浩特市', '0');
INSERT INTO `t_district` VALUES ('96', 'B', '包头市', '0');
INSERT INTO `t_district` VALUES ('97', 'W', '乌海市', '0');
INSERT INTO `t_district` VALUES ('98', 'C', '赤峰市', '0');
INSERT INTO `t_district` VALUES ('99', 'T', '通辽市', '0');
INSERT INTO `t_district` VALUES ('100', 'E', '鄂尔多斯市', '0');
INSERT INTO `t_district` VALUES ('101', 'H', '呼伦贝尔市', '0');
INSERT INTO `t_district` VALUES ('102', 'B', '巴彦淖尔市', '0');
INSERT INTO `t_district` VALUES ('103', 'W', '乌兰察布市', '0');
INSERT INTO `t_district` VALUES ('107', 'C', '沈阳市', '0');
INSERT INTO `t_district` VALUES ('108', 'D', '大连市', '0');
INSERT INTO `t_district` VALUES ('109', 'A', '鞍山市', '0');
INSERT INTO `t_district` VALUES ('110', 'F', '抚顺市', '0');
INSERT INTO `t_district` VALUES ('111', 'B', '本溪市', '0');
INSERT INTO `t_district` VALUES ('112', 'D', '丹东市', '0');
INSERT INTO `t_district` VALUES ('113', 'J', '锦州市', '0');
INSERT INTO `t_district` VALUES ('114', 'Y', '营口市', '0');
INSERT INTO `t_district` VALUES ('115', 'F', '阜新市', '0');
INSERT INTO `t_district` VALUES ('116', 'L', '辽阳市', '0');
INSERT INTO `t_district` VALUES ('117', 'P', '盘锦市', '0');
INSERT INTO `t_district` VALUES ('118', 'T', '铁岭市', '0');
INSERT INTO `t_district` VALUES ('119', 'Z', '朝阳市', '0');
INSERT INTO `t_district` VALUES ('120', 'H', '葫芦岛市', '0');
INSERT INTO `t_district` VALUES ('121', 'Z', '长春市', '0');
INSERT INTO `t_district` VALUES ('122', 'J', '吉林市', '0');
INSERT INTO `t_district` VALUES ('123', 'S', '四平市', '0');
INSERT INTO `t_district` VALUES ('124', 'L', '辽源市', '0');
INSERT INTO `t_district` VALUES ('125', 'T', '通化市', '0');
INSERT INTO `t_district` VALUES ('126', 'B', '白山市', '0');
INSERT INTO `t_district` VALUES ('127', 'S', '松原市', '0');
INSERT INTO `t_district` VALUES ('128', 'B', '白城市', '0');
INSERT INTO `t_district` VALUES ('130', 'H', '哈尔滨市', '0');
INSERT INTO `t_district` VALUES ('131', 'Q', '齐齐哈尔市', '0');
INSERT INTO `t_district` VALUES ('132', 'J', '鸡西市', '0');
INSERT INTO `t_district` VALUES ('133', 'H', '鹤岗市', '0');
INSERT INTO `t_district` VALUES ('134', 'S', '双鸭山市', '0');
INSERT INTO `t_district` VALUES ('135', 'D', '大庆市', '0');
INSERT INTO `t_district` VALUES ('136', 'Y', '伊春市', '0');
INSERT INTO `t_district` VALUES ('137', 'J', '佳木斯市', '0');
INSERT INTO `t_district` VALUES ('138', 'Q', '七台河市', '0');
INSERT INTO `t_district` VALUES ('139', 'M', '牡丹江市', '0');
INSERT INTO `t_district` VALUES ('140', 'H', '黑河市', '0');
INSERT INTO `t_district` VALUES ('141', 'S', '绥化市', '0');
INSERT INTO `t_district` VALUES ('162', 'N', '南京市', '0');
INSERT INTO `t_district` VALUES ('163', 'W', '无锡市', '0');
INSERT INTO `t_district` VALUES ('164', 'X', '徐州市', '0');
INSERT INTO `t_district` VALUES ('165', 'C', '常州市', '0');
INSERT INTO `t_district` VALUES ('166', 'S', '苏州市', '0');
INSERT INTO `t_district` VALUES ('167', 'N', '南通市', '0');
INSERT INTO `t_district` VALUES ('168', 'L', '连云港市', '0');
INSERT INTO `t_district` VALUES ('169', 'H', '淮安市', '0');
INSERT INTO `t_district` VALUES ('170', 'Y', '盐城市', '0');
INSERT INTO `t_district` VALUES ('171', 'Y', '扬州市', '0');
INSERT INTO `t_district` VALUES ('172', 'Z', '镇江市', '0');
INSERT INTO `t_district` VALUES ('173', 'T', '泰州市', '0');
INSERT INTO `t_district` VALUES ('174', 'S', '宿迁市', '0');
INSERT INTO `t_district` VALUES ('175', 'H', '杭州市', '0');
INSERT INTO `t_district` VALUES ('176', 'N', '宁波市', '0');
INSERT INTO `t_district` VALUES ('177', 'W', '温州市', '0');
INSERT INTO `t_district` VALUES ('178', 'J', '嘉兴市', '0');
INSERT INTO `t_district` VALUES ('179', 'H', '湖州市', '0');
INSERT INTO `t_district` VALUES ('180', 'S', '绍兴市', '0');
INSERT INTO `t_district` VALUES ('181', 'Z', '舟山市', '0');
INSERT INTO `t_district` VALUES ('182', 'Q', '衢州市', '0');
INSERT INTO `t_district` VALUES ('183', 'J', '金华市', '0');
INSERT INTO `t_district` VALUES ('184', 'T', '台州市', '0');
INSERT INTO `t_district` VALUES ('185', 'L', '丽水市', '0');
INSERT INTO `t_district` VALUES ('186', 'H', '合肥市', '0');
INSERT INTO `t_district` VALUES ('187', 'W', '芜湖市', '0');
INSERT INTO `t_district` VALUES ('188', 'B', '蚌埠市', '0');
INSERT INTO `t_district` VALUES ('189', 'H', '淮南市', '0');
INSERT INTO `t_district` VALUES ('190', 'M', '马鞍山市', '0');
INSERT INTO `t_district` VALUES ('191', 'H', '淮北市', '0');
INSERT INTO `t_district` VALUES ('192', 'T', '铜陵市', '0');
INSERT INTO `t_district` VALUES ('194', 'H', '黄山市', '0');
INSERT INTO `t_district` VALUES ('195', 'C', '滁州市', '0');
INSERT INTO `t_district` VALUES ('196', 'F', '阜阳市', '0');
INSERT INTO `t_district` VALUES ('197', 'S', '宿州市', '0');
INSERT INTO `t_district` VALUES ('198', 'C', '巢湖市', '0');
INSERT INTO `t_district` VALUES ('199', 'L', '六安市', '0');
INSERT INTO `t_district` VALUES ('200', 'B', '亳州市', '0');
INSERT INTO `t_district` VALUES ('201', 'C', '池州市', '0');
INSERT INTO `t_district` VALUES ('202', 'X', '宣城市', '0');
INSERT INTO `t_district` VALUES ('203', 'F', '福州市', '0');
INSERT INTO `t_district` VALUES ('204', 'X', '厦门市', '0');
INSERT INTO `t_district` VALUES ('205', 'P', '莆田市', '0');
INSERT INTO `t_district` VALUES ('206', 'S', '三明市', '0');
INSERT INTO `t_district` VALUES ('207', 'Q', '泉州市', '0');
INSERT INTO `t_district` VALUES ('208', 'Z', '漳州市', '0');
INSERT INTO `t_district` VALUES ('209', 'N', '南平市', '0');
INSERT INTO `t_district` VALUES ('210', 'L', '龙岩市', '0');
INSERT INTO `t_district` VALUES ('211', 'N', '宁德市', '0');
INSERT INTO `t_district` VALUES ('212', 'N', '南昌市', '0');
INSERT INTO `t_district` VALUES ('213', 'J', '景德镇市', '0');
INSERT INTO `t_district` VALUES ('214', 'P', '萍乡市', '0');
INSERT INTO `t_district` VALUES ('215', 'J', '九江市', '0');
INSERT INTO `t_district` VALUES ('216', 'X', '新余市', '0');
INSERT INTO `t_district` VALUES ('217', 'Y', '鹰潭市', '0');
INSERT INTO `t_district` VALUES ('218', 'G', '赣州市', '0');
INSERT INTO `t_district` VALUES ('219', 'J', '吉安市', '0');
INSERT INTO `t_district` VALUES ('220', 'Y', '宜春市', '0');
INSERT INTO `t_district` VALUES ('221', 'F', '抚州市', '0');
INSERT INTO `t_district` VALUES ('222', 'S', '上饶市', '0');
INSERT INTO `t_district` VALUES ('223', 'J', '济南市', '0');
INSERT INTO `t_district` VALUES ('224', 'Q', '青岛市', '0');
INSERT INTO `t_district` VALUES ('225', 'Z', '淄博市', '0');
INSERT INTO `t_district` VALUES ('226', 'Z', '枣庄市', '0');
INSERT INTO `t_district` VALUES ('227', 'D', '东营市', '0');
INSERT INTO `t_district` VALUES ('228', 'Y', '烟台市', '0');
INSERT INTO `t_district` VALUES ('229', 'W', '潍坊市', '0');
INSERT INTO `t_district` VALUES ('230', 'J', '济宁市', '0');
INSERT INTO `t_district` VALUES ('231', 'T', '泰安市', '0');
INSERT INTO `t_district` VALUES ('232', 'W', '威海市', '0');
INSERT INTO `t_district` VALUES ('233', 'R', '日照市', '0');
INSERT INTO `t_district` VALUES ('234', 'L', '莱芜市', '0');
INSERT INTO `t_district` VALUES ('235', 'L', '临沂市', '0');
INSERT INTO `t_district` VALUES ('236', 'D', '德州市', '0');
INSERT INTO `t_district` VALUES ('237', 'L', '聊城市', '0');
INSERT INTO `t_district` VALUES ('238', 'B', '滨州市', '0');
INSERT INTO `t_district` VALUES ('239', 'H', '菏泽市', '0');
INSERT INTO `t_district` VALUES ('240', 'Z', '郑州市', '0');
INSERT INTO `t_district` VALUES ('241', 'K', '开封市', '0');
INSERT INTO `t_district` VALUES ('242', 'L', '洛阳市', '0');
INSERT INTO `t_district` VALUES ('243', 'P', '平顶山市', '0');
INSERT INTO `t_district` VALUES ('244', 'A', '安阳市', '0');
INSERT INTO `t_district` VALUES ('245', 'H', '鹤壁市', '0');
INSERT INTO `t_district` VALUES ('246', 'X', '新乡市', '0');
INSERT INTO `t_district` VALUES ('247', 'J', '焦作市', '0');
INSERT INTO `t_district` VALUES ('248', 'P', '濮阳市', '0');
INSERT INTO `t_district` VALUES ('249', 'X', '许昌市', '0');
INSERT INTO `t_district` VALUES ('250', 'L', '漯河市', '0');
INSERT INTO `t_district` VALUES ('251', 'S', '三门峡市', '0');
INSERT INTO `t_district` VALUES ('252', 'N', '南阳市', '0');
INSERT INTO `t_district` VALUES ('253', 'S', '商丘市', '0');
INSERT INTO `t_district` VALUES ('254', 'X', '信阳市', '0');
INSERT INTO `t_district` VALUES ('255', 'Z', '周口市', '0');
INSERT INTO `t_district` VALUES ('256', 'Z', '驻马店市', '0');
INSERT INTO `t_district` VALUES ('257', 'J', '济源市', '0');
INSERT INTO `t_district` VALUES ('258', 'W', '武汉市', '0');
INSERT INTO `t_district` VALUES ('259', 'H', '黄石市', '0');
INSERT INTO `t_district` VALUES ('260', 'S', '十堰市', '0');
INSERT INTO `t_district` VALUES ('261', 'Y', '宜昌市', '0');
INSERT INTO `t_district` VALUES ('262', 'X', '襄樊市', '0');
INSERT INTO `t_district` VALUES ('263', 'E', '鄂州市', '0');
INSERT INTO `t_district` VALUES ('264', 'J', '荆门市', '0');
INSERT INTO `t_district` VALUES ('265', 'X', '孝感市', '0');
INSERT INTO `t_district` VALUES ('266', 'J', '荆州市', '0');
INSERT INTO `t_district` VALUES ('267', 'H', '黄冈市', '0');
INSERT INTO `t_district` VALUES ('268', 'X', '咸宁市', '0');
INSERT INTO `t_district` VALUES ('269', 'S', '随州市', '0');
INSERT INTO `t_district` VALUES ('271', 'X', '仙桃市', '0');
INSERT INTO `t_district` VALUES ('272', 'Q', '潜江市', '0');
INSERT INTO `t_district` VALUES ('273', 'T', '天门市', '0');
INSERT INTO `t_district` VALUES ('275', 'Z', '长沙市', '0');
INSERT INTO `t_district` VALUES ('276', 'Z', '株洲市', '0');
INSERT INTO `t_district` VALUES ('277', 'X', '湘潭市', '0');
INSERT INTO `t_district` VALUES ('278', 'H', '衡阳市', '0');
INSERT INTO `t_district` VALUES ('279', 'S', '邵阳市', '0');
INSERT INTO `t_district` VALUES ('280', 'Y', '岳阳市', '0');
INSERT INTO `t_district` VALUES ('281', 'C', '常德市', '0');
INSERT INTO `t_district` VALUES ('282', 'Z', '张家界市', '0');
INSERT INTO `t_district` VALUES ('283', 'Y', '益阳市', '0');
INSERT INTO `t_district` VALUES ('284', 'C', '郴州市', '0');
INSERT INTO `t_district` VALUES ('285', 'Y', '永州市', '0');
INSERT INTO `t_district` VALUES ('286', 'H', '怀化市', '0');
INSERT INTO `t_district` VALUES ('287', 'L', '娄底市', '0');
INSERT INTO `t_district` VALUES ('289', 'G', '广州市', '1');
INSERT INTO `t_district` VALUES ('290', 'S', '韶关市', '0');
INSERT INTO `t_district` VALUES ('291', 'S', '深圳市', '0');
INSERT INTO `t_district` VALUES ('292', 'Z', '珠海市', '0');
INSERT INTO `t_district` VALUES ('293', 'S', '汕头市', '0');
INSERT INTO `t_district` VALUES ('294', 'F', '佛山市', '0');
INSERT INTO `t_district` VALUES ('295', 'J', '江门市', '0');
INSERT INTO `t_district` VALUES ('296', 'Z', '湛江市', '0');
INSERT INTO `t_district` VALUES ('297', 'M', '茂名市', '0');
INSERT INTO `t_district` VALUES ('298', 'Z', '肇庆市', '0');
INSERT INTO `t_district` VALUES ('299', 'H', '惠州市', '0');
INSERT INTO `t_district` VALUES ('300', 'M', '梅州市', '0');
INSERT INTO `t_district` VALUES ('301', 'S', '汕尾市', '0');
INSERT INTO `t_district` VALUES ('302', 'H', '河源市', '0');
INSERT INTO `t_district` VALUES ('303', 'Y', '阳江市', '0');
INSERT INTO `t_district` VALUES ('304', 'Q', '清远市', '0');
INSERT INTO `t_district` VALUES ('305', 'D', '东莞市', '0');
INSERT INTO `t_district` VALUES ('306', 'Z', '中山市', '0');
INSERT INTO `t_district` VALUES ('307', 'C', '潮州市', '0');
INSERT INTO `t_district` VALUES ('308', 'J', '揭阳市', '0');
INSERT INTO `t_district` VALUES ('309', 'Y', '云浮市', '0');
INSERT INTO `t_district` VALUES ('310', 'N', '南宁市', '0');
INSERT INTO `t_district` VALUES ('311', 'L', '柳州市', '0');
INSERT INTO `t_district` VALUES ('312', 'G', '桂林市', '0');
INSERT INTO `t_district` VALUES ('313', 'W', '梧州市', '0');
INSERT INTO `t_district` VALUES ('314', 'B', '北海市', '0');
INSERT INTO `t_district` VALUES ('315', 'F', '防城港市', '0');
INSERT INTO `t_district` VALUES ('316', 'Q', '钦州市', '0');
INSERT INTO `t_district` VALUES ('317', 'G', '贵港市', '0');
INSERT INTO `t_district` VALUES ('318', 'Y', '玉林市', '0');
INSERT INTO `t_district` VALUES ('319', 'B', '百色市', '0');
INSERT INTO `t_district` VALUES ('320', 'H', '贺州市', '0');
INSERT INTO `t_district` VALUES ('321', 'H', '河池市', '0');
INSERT INTO `t_district` VALUES ('322', 'L', '来宾市', '0');
INSERT INTO `t_district` VALUES ('323', 'C', '崇左市', '0');
INSERT INTO `t_district` VALUES ('324', 'H', '海口市', '0');
INSERT INTO `t_district` VALUES ('325', 'S', '三亚市', '0');
INSERT INTO `t_district` VALUES ('326', 'W', '五指山市', '0');
INSERT INTO `t_district` VALUES ('327', 'Q', '琼海市', '0');
INSERT INTO `t_district` VALUES ('328', 'D', '儋州市', '0');
INSERT INTO `t_district` VALUES ('329', 'W', '文昌市', '0');
INSERT INTO `t_district` VALUES ('330', 'W', '万宁市', '0');
INSERT INTO `t_district` VALUES ('331', 'D', '东方市', '0');
INSERT INTO `t_district` VALUES ('381', 'J', '江津市', '0');
INSERT INTO `t_district` VALUES ('382', 'H', '合川市', '0');
INSERT INTO `t_district` VALUES ('383', 'Y', '永川市', '0');
INSERT INTO `t_district` VALUES ('384', 'N', '南川市', '0');
INSERT INTO `t_district` VALUES ('385', 'C', '成都市', '0');
INSERT INTO `t_district` VALUES ('386', 'Z', '自贡市', '0');
INSERT INTO `t_district` VALUES ('387', 'P', '攀枝花市', '0');
INSERT INTO `t_district` VALUES ('388', 'L', '泸州市', '0');
INSERT INTO `t_district` VALUES ('389', 'D', '德阳市', '0');
INSERT INTO `t_district` VALUES ('390', 'M', '绵阳市', '0');
INSERT INTO `t_district` VALUES ('391', 'G', '广元市', '0');
INSERT INTO `t_district` VALUES ('392', 'S', '遂宁市', '0');
INSERT INTO `t_district` VALUES ('393', 'N', '内江市', '0');
INSERT INTO `t_district` VALUES ('394', 'Y', '乐山市', '0');
INSERT INTO `t_district` VALUES ('395', 'N', '南充市', '0');
INSERT INTO `t_district` VALUES ('396', 'M', '眉山市', '0');
INSERT INTO `t_district` VALUES ('397', 'Y', '宜宾市', '0');
INSERT INTO `t_district` VALUES ('398', 'G', '广安市', '0');
INSERT INTO `t_district` VALUES ('399', 'D', '达州市', '0');
INSERT INTO `t_district` VALUES ('400', 'Y', '雅安市', '0');
INSERT INTO `t_district` VALUES ('401', 'B', '巴中市', '0');
INSERT INTO `t_district` VALUES ('402', 'Z', '资阳市', '0');
INSERT INTO `t_district` VALUES ('406', 'G', '贵阳市', '0');
INSERT INTO `t_district` VALUES ('407', 'L', '六盘水市', '0');
INSERT INTO `t_district` VALUES ('408', 'Z', '遵义市', '0');
INSERT INTO `t_district` VALUES ('415', 'K', '昆明市', '0');
INSERT INTO `t_district` VALUES ('416', 'Q', '曲靖市', '0');
INSERT INTO `t_district` VALUES ('417', 'Y', '玉溪市', '0');
INSERT INTO `t_district` VALUES ('418', 'B', '保山市', '0');
INSERT INTO `t_district` VALUES ('419', 'Z', '昭通市', '0');
INSERT INTO `t_district` VALUES ('420', 'L', '丽江市', '0');
INSERT INTO `t_district` VALUES ('421', 'S', '思茅市', '0');
INSERT INTO `t_district` VALUES ('422', 'L', '临沧市', '0');
INSERT INTO `t_district` VALUES ('431', 'L', '拉萨市', '0');
INSERT INTO `t_district` VALUES ('438', 'X', '西安市', '0');
INSERT INTO `t_district` VALUES ('439', 'T', '铜川市', '0');
INSERT INTO `t_district` VALUES ('440', 'B', '宝鸡市', '0');
INSERT INTO `t_district` VALUES ('441', 'X', '咸阳市', '0');
INSERT INTO `t_district` VALUES ('442', 'W', '渭南市', '0');
INSERT INTO `t_district` VALUES ('443', 'Y', '延安市', '0');
INSERT INTO `t_district` VALUES ('444', 'H', '汉中市', '0');
INSERT INTO `t_district` VALUES ('445', 'Y', '榆林市', '0');
INSERT INTO `t_district` VALUES ('447', 'S', '商洛市', '0');
INSERT INTO `t_district` VALUES ('448', 'L', '兰州市', '0');
INSERT INTO `t_district` VALUES ('449', 'J', '嘉峪关市', '0');
INSERT INTO `t_district` VALUES ('450', 'J', '金昌市', '0');
INSERT INTO `t_district` VALUES ('451', 'B', '白银市', '0');
INSERT INTO `t_district` VALUES ('452', 'T', '天水市', '0');
INSERT INTO `t_district` VALUES ('453', 'W', '武威市', '0');
INSERT INTO `t_district` VALUES ('454', 'Z', '张掖市', '0');
INSERT INTO `t_district` VALUES ('455', 'P', '平凉市', '0');
INSERT INTO `t_district` VALUES ('456', 'J', '酒泉市', '0');
INSERT INTO `t_district` VALUES ('457', 'Q', '庆阳市', '0');
INSERT INTO `t_district` VALUES ('458', 'D', '定西市', '0');
INSERT INTO `t_district` VALUES ('459', 'L', '陇南市', '0');
INSERT INTO `t_district` VALUES ('462', 'X', '西宁市', '0');
INSERT INTO `t_district` VALUES ('470', 'Y', '银川市', '0');
INSERT INTO `t_district` VALUES ('471', 'S', '石嘴山市', '0');
INSERT INTO `t_district` VALUES ('472', 'W', '吴忠市', '0');
INSERT INTO `t_district` VALUES ('473', 'G', '固原市', '0');
INSERT INTO `t_district` VALUES ('474', 'Z', '中卫市', '0');
INSERT INTO `t_district` VALUES ('475', 'W', '乌鲁木齐市', '1');
INSERT INTO `t_district` VALUES ('476', 'K', '克拉玛依市', '0');
INSERT INTO `t_district` VALUES ('489', 'S', '石河子市', '0');
INSERT INTO `t_district` VALUES ('491', 'T', '图木舒克市', '0');
INSERT INTO `t_district` VALUES ('492', 'W', '五家渠市', '0');
INSERT INTO `t_district` VALUES ('493', 'T', '台北市', '0');
INSERT INTO `t_district` VALUES ('494', 'G', '高雄市', '0');
INSERT INTO `t_district` VALUES ('495', 'J', '基隆市', '0');
INSERT INTO `t_district` VALUES ('496', 'T', '台中市', '0');
INSERT INTO `t_district` VALUES ('497', 'T', '台南市', '0');
INSERT INTO `t_district` VALUES ('498', 'X', '新竹市', '0');
INSERT INTO `t_district` VALUES ('499', 'J', '嘉义市', '0');

-- ----------------------------
-- 会员表 t_customers
-- ----------------------------
DROP TABLE IF EXISTS `t_customers`;
CREATE TABLE `t_customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_openid` varchar(100) NOT NULL DEFAULT '' COMMENT '微信ID',
  `customer_gender` varchar(6) COMMENT '性别',
  `customer_age` varchar(10) COMMENT '年龄段',
  `customer_name` varchar(16) NOT NULL DEFAULT '' COMMENT '名字',
  `customer_headimg` varchar(45) COMMENT '头像',
  `customer_regtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `district_id` int(11) COMMENT '常住城市ID',
  `customer_default_address_id` int(5) NOT NULL DEFAULT '0' COMMENT '默认收货地址',
  `customer_tel` varchar(32) COMMENT '电话',
  `customer_invite_code` varchar(8) NOT NULL UNIQUE KEY COMMENT '邀请码',
  `customer_invite_id` int(11) COMMENT '邀请人',
  `customer_score` mediumint(8) NOT NULL DEFAULT '0' COMMENT '积分',
  `customer_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员表';

-- ----------------------------
-- 用户反馈 t_customer_feedbacks
-- ----------------------------
DROP TABLE IF EXISTS `t_customer_feedbacks`;
CREATE TABLE `t_customer_feedbacks` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `feedback_text` text COMMENT '内容',
  `feedback_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `feedback_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='用户反馈';

--
-- 手机验证码 `t_phone_verif`
--
DROP TABLE IF EXISTS `t_phone_verif`;
CREATE TABLE `t_phone_verif` (
  `phone_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `phone_number` varchar(32) COMMENT '电话',
  `phone_code` varchar(6) COMMENT '验证码',
  `phone_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `phone_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT ''
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='手机验证';

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
-- 会员收藏表 t_customer_collections
-- ----------------------------
DROP TABLE IF EXISTS `t_customer_collections`;
CREATE TABLE `t_customer_collections` (
  `collection_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `product_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `customer_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员收藏';

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
-- 首页推荐表 t_recommends
-- ----------------------------
DROP TABLE IF EXISTS `t_recommends`;
CREATE TABLE `t_recommends` (
  `recommend_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `recommend_title` varchar(32) NOT NULL COMMENT '标题',
  `district_id` tinyint(4) NOT NULL COMMENT '城市ID',
  `recommend_path` varchar(45) NOT NULL COMMENT '图片路径',
  `recommend_logo` varchar(45) COMMENT '品牌logo',
  `product_code` varchar(200) COMMENT '商品CODE',
  `recommend_sort` int(5) NOT NULL DEFAULT '0' COMMENT '排序',
  `recommend_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `recommend_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='首页推荐';

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
  `task_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `task_title` varchar(200) NOT NULL COMMENT '标题',
  `task_desc` text COMMENT '问题描述',
  `task_banner` varchar(45) NOT NULL COMMENT '缩略图路径',
  `task_score` mediumint(8) NOT NULL DEFAULT '0' COMMENT '答题积分',
  `task_num` tinyint(4) NOT NULL DEFAULT '1' COMMENT '每天答题次数',
  `task_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `task_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='答题任务';

-- ----------------------------
-- 题库表 t_questions
-- ----------------------------
DROP TABLE IF EXISTS `t_questions`;
CREATE TABLE `t_questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `task_id` int(4) NOT NULL COMMENT '答题任务ID',
  `question_title` varchar(200) NOT NULL COMMENT '问题',
  `question_a` varchar(200) NOT NULL COMMENT '选项A',
  `question_b` varchar(200) NOT NULL COMMENT '选项B',
  `question_c` varchar(200) NOT NULL COMMENT '选项C',
  `question_d` varchar(200) NOT NULL COMMENT '选项D',
  `question_e` varchar(200) NOT NULL COMMENT '选项E',
  `question_f` varchar(200) NOT NULL COMMENT '选项F',
  `question_answer` varchar(20) NOT NULL COMMENT '答案',
  `question_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '答案类型：1单选；2多选',
  `question_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `question_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='题库表';

-- ----------------------------
-- 用户答题记录表 t_task_log
-- ----------------------------
DROP TABLE IF EXISTS `t_task_log`;
CREATE TABLE `t_task_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `key_id` int(4) NOT NULL COMMENT '任务ID',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `log_type` enum('read','question') NOT NULL COMMENT '状态：read阅读；question答题',
  `log_question_answer` varchar(255) NOT NULL COMMENT '答案',
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `log_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='用户答题记录表';


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
-- 积分兑换抽奖机会表 t_turntable_chance
-- ----------------------------
DROP TABLE IF EXISTS `t_turntable_chance`;
CREATE TABLE `t_turntable_chance` (
  `chance_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `chance_num` mediumint(8) NOT NULL COMMENT '次数',
  `chance_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='积分兑换抽奖机会';

-- ----------------------------
-- 分享日志表 t_share_log
-- ----------------------------
DROP TABLE IF EXISTS `t_share_log`;
CREATE TABLE `t_share_log` (
  `share_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `share_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `share_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分享日志';

-- ----------------------------
-- 会员奖品表 t_prizes
-- ----------------------------
DROP TABLE IF EXISTS `t_prizes`;
CREATE TABLE `t_prizes` (
  `prize_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `customer_id` int(11) NOT NULL COMMENT '会员ID',
  `turntablep_id` int(11) NOT NULL COMMENT '奖品ID',
  `prize_customer_name` varchar(16) DEFAULT '' COMMENT '收货人名字',
  `district_id` tinyint(4) DEFAULT '0' COMMENT '收货城市ID',
  `district_name` varchar(255)  COMMENT '收货城市名称',
  `prize_address` varchar(200) DEFAULT '' COMMENT '收货地址',
  `prize_tel` varchar(32) DEFAULT '' COMMENT '联系电话',
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
-- 商家登录表 t_shop_users
-- ----------------------------
DROP TABLE IF EXISTS `t_shop_users`;
CREATE TABLE `t_shop_users` (
  `suser_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `shop_id` int(11) NOT NULL COMMENT '商家ID',
  `suser_name` varchar(90) NOT NULL UNIQUE KEY COMMENT '用户名',
  `suser_password` char(35) NOT NULL COMMENT '密码',
  `suser_regtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `suser_logtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后一次登录时间',
  `suser_logip` char(15) NOT NULL DEFAULT '' COMMENT '最后一次登录ip',
  `suser_lognum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '登录次数',
  `suser_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商家登录表';

-- ----------------------------
-- Table structure for t_products
-- ----------------------------
DROP TABLE IF EXISTS `t_products`;
CREATE TABLE `t_products` (
  `product_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '商品ID',
  `district_id` tinyint(4) NOT NULL COMMENT '城市ID',
  `product_code` char(12) NOT NULL UNIQUE KEY COMMENT '商品CODE',
  `product_logo_name` varchar(150) NOT NULL COMMENT 'logo名称',
  `product_name` varchar(150) NOT NULL COMMENT '商品名称',
  `product_quantity` smallint(6) NOT NULL DEFAULT '0' COMMENT '库存数量',
  `product_price` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `product_weight` varchar(20) COMMENT '商品重量',
  `product_virtual_price` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '商品虚拟价格',
  `attr_id` int(11) NOT NULL COMMENT '商品类别',
  `product_type` enum('1', '2') NOT NULL COMMENT '领取方式：1:白领；2:组团领',
  `product_group_num` int(3) COMMENT '组团人数',
  `product_group_time` int(3) COMMENT '组团过期时间（天）',
  `product_desc` varchar(225) NOT NULL DEFAULT '' COMMENT '商品推荐说明',
  `product_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '商品添加时间',
  `product_end` timestamp NOT NULL COMMENT '商品下架时间',
  `product_qr_code_day` int(3) NOT NULL COMMENT '领取二维码有效时长(天)',
  `product_sort` int(3) DEFAULT 0 COMMENT '商品排序',
  `product_shaping_status` tinyint(1) NOT NULL COMMENT '是否支持配送',
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
  `image_type` enum('logo','home','banner','detail') NOT NULL COMMENT '图片类型：logo品牌；home缩略图；banner详情顶部图片；detail详情底部图片',
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
-- Table structure for t_product_refill
-- ----------------------------
DROP TABLE IF EXISTS `t_product_refill`;
CREATE TABLE `t_product_refill` (
  `refill_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `shop_id` int(11) NOT NULL COMMENT '商家ID',
  `refill_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  `refill_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='补货通知表';

-- ----------------------------
-- Table structure for t_hot_products
-- ----------------------------
DROP TABLE IF EXISTS `t_hot_products`;
CREATE TABLE `t_hot_products` (
  `hot_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `district_id` tinyint(4) NOT NULL COMMENT '城市ID',
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `hot_type` enum('main','minor') NOT NULL COMMENT '类型：main主要；minor次要',
  `quantity_num` smallint(6) NOT NULL DEFAULT '0' COMMENT '商家库存数量'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='热门推荐商品';

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
  `review_id` int(11) NOT NULL COMMENT '评论ID',
  `image_path` varchar(45) NOT NULL COMMENT '评论图片',
  `image_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='评论图片表';

-- ----------------------------
-- Table structure for t_review_logs
-- ----------------------------
DROP TABLE IF EXISTS `t_review_logs`;
CREATE TABLE `t_review_logs` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `review_id` int(11) NOT NULL COMMENT '评论ID',
  `customer_id` int(11) NOT NULL COMMENT '顾客ID'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='评论记录表';

-- ----------------------------
-- Table structure for t_order_groups
-- ----------------------------
DROP TABLE IF EXISTS `t_order_groups`;
CREATE TABLE `t_order_groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '团购ID',
  `customer_id` varchar(45) NOT NULL COMMENT '顾客ID (1,2,3) 第一个为发起人',
  `product_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `group_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '开始时间',
  `group_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='团购表';

-- ----------------------------
-- Table structure for t_orders
-- ----------------------------
DROP TABLE IF EXISTS `t_orders`;
CREATE TABLE `t_orders` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '',
  `order_number` varchar(32) NOT NULL COMMENT '订单号',
  `product_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `product_name` varchar(150) NOT NULL COMMENT '商品名称',
  `product_quantity` smallint(6) NOT NULL DEFAULT '1' COMMENT '购买数量',
  `product_price` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `customer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下单顾客ID',
  `shinging_type` enum('self','logistics') NOT NULL COMMENT '取货方式：self自提；logistics配送',
  `shop_id` int(4) DEFAULT '0' COMMENT '自提时商家ID',
  `order_customer_name` varchar(16) NOT NULL DEFAULT '' COMMENT '收货人名字',
  `district_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '收货城市ID',
  `district_name` varchar(255) NOT NULL COMMENT '收货城市名称',
  `order_address` varchar(200) NOT NULL DEFAULT '' COMMENT '收货地址',
  `order_tel` varchar(32) NOT NULL DEFAULT '' COMMENT '联系电话',
  `order_desc` varchar(200) COMMENT '特殊说明',
  `order_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '下单时间',
  `order_shipped_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发货时间',
  `order_received_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '收货时间',
  `order_type` enum('group','pending','shipped','received', 'review') NOT NULL DEFAULT 'pending' COMMENT '订单状态：group组团中；pending正在发货；shipped已发货；received已收货；review已评论',
  `order_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态'
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='订单表';