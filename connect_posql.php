<?php

$posql = new Posql("posql_dopewars_db.php");
  if ($posql->isError()) {
    die($posql->lastError());
  } 

//++++++++++++++++++++++++++++JON DEMO posql ++++++++++++++++++++++++++++++++
/* 
$posql = new Posql("posql_shoplist_db");
  if ($posql->isError()) {
    die($posql->lastError());
  } 


  $thequery="select * from ".$table_prefix."thelist order by purchased,priority asc";

  $query_results = $posql->query($thequery); //= $posql->query($thequery);
  while($row = $query_results->fetch()){
  }
  	if($posql->query("delete from ".$table_prefix."thelist where itemid='".(int)$_GET['itemid']."'")){
		$output = '<b>Item deleted successfully!</b><br/><br/>';
	}else{
		$output = '<b>An Error Occurred: ' . $posql->lastError() . '</b><br><br>';
	}

	if(isset($_GET['itemid'])){
	 
		// if we're editing we need to grab the stuff from the database

		// convert to integer (if its not a number it'll become zero
		$itemid= (int)$_GET['itemid'];
		$thequery="select * from ".$table_prefix."thelist where itemid='" . $itemid."'";
		
		if($debug) echo "<h1>thequery=$thequery</h1>";
		//$query_results = mysql_query("select * from ".$table_prefix."thelist where itemid='" . $itemid . "' limit 1"); //orig line
		$query_results = $posql->query($thequery);
		if($debug) print_r($query_results);
		$row = $query_results->fetch();
		if($debug) print_r($row);
	}else{
		// set up blank array
		$row['itemid'] = '';
		$row['name'] = '';
		$row['quantity'] = '';
		$row['price_estimated']='';	
		$row['category']='';		
		$row['purchased'] = '';
		$row['comment'] = '';
		$row['priority'] = '';
		$row['reoccuring'] = '';

	}	
	
*/	
//---------------------------------------	

?>
