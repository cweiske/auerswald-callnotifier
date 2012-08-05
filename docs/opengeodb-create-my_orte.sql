CREATE TABLE IF NOT EXISTS `my_orte` (
  `loc_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `vorwahl` varchar(8) NOT NULL,
  `bundesland` varchar(32) NOT NULL,
  `level` tinyint(4) NOT NULL,
  `einwohner` int(11) NOT NULL,
  UNIQUE KEY `loc_id` (`loc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO my_orte (loc_id, name, vorwahl, bundesland, level, einwohner)
SELECT t_vorwahl.loc_id, t_name.text_val as name, t_vorwahl.text_val as vorwahl, t_name_bundesland.text_val as bundesland, t_hier.level as level, t_einwohner.int_val as einwohner
FROM geodb_textdata as t_vorwahl
 JOIN geodb_textdata as t_name ON t_vorwahl.loc_id = t_name.loc_id
 JOIN geodb_hierarchies AS t_hier ON t_vorwahl.loc_id = t_hier.loc_id
 JOIN geodb_textdata as t_name_bundesland ON t_hier.id_lvl3 = t_name_bundesland.loc_id
 LEFT JOIN geodb_intdata as t_einwohner ON t_vorwahl.loc_id = t_einwohner.loc_id
WHERE t_vorwahl.text_type="500400000"
 AND t_name.text_type = "500100000"
 AND t_name_bundesland.text_type = "500100000"
 AND (t_einwohner.int_type = "600700000" OR t_einwohner.loc_id IS NULL);
