  DROP TABLE IF EXISTS `dopescores`;
  CREATE TABLE `dopescores` (
    `id` int(11) NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `password` varchar(255) NOT NULL default '',
    `score` bigint(20) NOT NULL default '0',
    `date` datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

  DROP TABLE IF EXISTS `dopewars`;
  CREATE TABLE `dopewars` (
    `id` int(11) NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `password` varchar(255) NOT NULL default '',
    `score` bigint(20) NOT NULL default '0',
    `onthemove` enum('0','1') NOT NULL default '0',
    `player` text NOT NULL,
    `date` datetime NOT NULL default '0000-00-00 00:00:00',
    `gameoff` enum('0','1') NOT NULL default '0',
    PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;
INSERT INTO `dopewars` VALUES(8, 'test', 'test', 14369, '0', 'a:25:{s:4:"name";s:4:"test";s:4:"cash";d:14369;s:4:"debt";d:0;s:4:"bank";d:0;s:4:"guns";a:0:{}s:7:"bitches";i:2;s:5:"space";i:40;s:4:"held";i:0;s:4:"life";i:100;s:10:"vegetables";a:2:{i:2;i:0;i:3;i:0;}s:10:"drugprices";a:2:{i:2;d:5547;i:3;d:840;}s:6:"prices";a:11:{i:0;i:2945;i:1;i:27697;i:2;i:12594;i:3;i:1208;i:4;i:669;i:5;i:223;i:6;i:3120;i:7;i:54;i:8;i:897;i:9;i:354;i:10;i:1999;}s:11:"destination";s:0:"";s:8:"location";s:1:"2";s:8:"snitches";a:0:{}s:15:"currentsnitches";a:0:{}s:12:"snitchreport";a:0:{}s:12:"fighthistory";a:0:{}s:5:"fight";i:0;s:9:"reloading";i:0;s:8:"opponent";s:0:"";s:5:"total";d:14369;s:6:"travel";i:2;s:11:"noencounter";i:18;s:6:"threat";i:0;}', '2014-03-13 06:46:40', '0');

