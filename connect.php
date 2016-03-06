<?php
	 //connect to mySQL database test_ao as john
		  $db_link = mysql_connect("mysql5.000webhost.com", "a9624305_test","ere22530");
		  mysql_select_db("a9624305_test",$db_link);
		  /* SET NAMES 'utf-8' COLLATE 'collation_name' */
		  @mysql_query("SET NAMES 'utf8'",$db_link);

?>
