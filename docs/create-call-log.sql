CREATE TABLE `finished` (
  `call_id` int(11) NOT NULL AUTO_INCREMENT,
  `call_start` datetime NOT NULL,
  `call_end` datetime NOT NULL,
  `call_type` varchar(1) NOT NULL,
  `call_from` varchar(32) DEFAULT NULL,
  `call_from_name` varchar(32) DEFAULT NULL,
  `call_from_location` varchar(32) DEFAULT NULL,
  `call_to` varchar(32) DEFAULT NULL,
  `call_to_name` varchar(32) DEFAULT NULL,
  `call_to_location` varchar(32) DEFAULT NULL,
  `call_length` int(11) NOT NULL,
  PRIMARY KEY (`call_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='finished calls';
