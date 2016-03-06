<?php
		$language = "EN";

		$places = array(
			"the Bronx",
			"the Ghetto",
			"Central Park",
			"Coney Island",
			"Manhattan",
			"Brooklyn",
			"Queens",
			"Staten Island");

		$special = array(
			0 => "the loanshark",
			1 => "the pub",
			2 => "the hospital",
			4 => "the bank",
			5 => "Dan's House of Guns");

		$drugs = array(
			array("name" => "acid",		"min" => 1000,	"max"=> 4400,	"minmsg" => "The market is flooded with cheap home-made acid!"),
			array("name" => "cocaine",	"min" => 15000,	"max"=> 29000,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "heroin",	"min" => 5500,	"max"=> 13000,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "hashish",	"min" => 480,	"max"=> 1280,	"minmsg" => "The Marrakesh Express has arrived!",	"maxmsg" => ""),
			array("name" => "weed",		"min" => 315,	"max"=> 890,	"minmsg" => "Columbian freighter dusted the Coast Guard!",	"maxmsg" => "Weed prices have bottomed out!"),
			array("name" => "speed",	"min" => 90,	"max"=> 250,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "ecstacy",	"min" => 2800,	"max"=> 3700,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "ludes",	"min" => 11,	"max"=> 60,		"minmsg" => "Rival drug dealers raided a pharmacy and are selling cheap ludes!",	"maxmsg" => ""),
			array("name" => "shrooms",	"min" => 630,	"max"=> 1300,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "peyote",	"min" => 220,	"max"=> 700,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "PCP",		"min" => 1000,	"max"=> 2500,	"minmsg" => "",	"maxmsg" => ""));

		   $bitchactions = array (
				array("name" => "sex",		"price" => 20),
				array("name" => "spy",		"price" => 6500),
				array("name" => "snitch",	"price" => 10000),
				array("name" => "drug runner",	"price" => 35000)
				);

		$str["sell"]	= "sell";
		$str["buy"]	= "buy";
		$str["available"]	= "available:";
		$str["carried"]		= "carried:";
		$str["to"]	= "to";
		$str["at"]	= "at";
		$str["amount"]	= "amount";
		$str["quantity"]	= "quantity";
		$str["withdraw"]	= "withdraw";
		$str["loan"]	= "loan";
		$str["pay"]	= "pay";
		$str["invalid"]	= "Invalid transaction";
		$str["operate"]	= "operate";

		$str["instruct"] = "instructions";
		$str["logout"]  = "log out";

		$str["deposit"]	= "deposit";
		$str["onthemove"]	= "jetting to";

		$fight = array("stand", "surrender", "run", "fire");

		$maxmsgs = array("Cops made a big %s bust! Prices are outrageous!", "Addicts are buying %s at ridiculous prices!", "The addicts are going nuts for %s!");
		$minmsgs = array("The market is flooded with cheap %s.");

		$str["nospace"]	= "You can't carry %s more drugs.";
		$str["nodrug"]	= "You're shittin' me right? You don't have %s units of %s.";

		$str["morespace"]	= "Now you can carry 10 more units.";

		$str["doctor"]		= "The doctor can fix you up for \$ %s.";
		$str["recovered"]	= "You are healthy.";

		$str["disease"]	= "You caught a venereal disease.";
		$str["mugged"]	= "You got mugged! Your money is gone.";
		$str["ooh"]	= "'Ooh, was it goof for you too?'";
		$str["operate"]	= "fix me up";

		$str["nogunspace"]	= "You can't carry more guns.";
		$str["noguncash"]	= "You can't afford that gun.";
		$str["nomoney"]	= "You don't have enough money for that.";
		$str["sell"]	= "sell";
		$str["buy"]	= "buy";
		$str["available"]	= "available:";
		$str["carried"]		= "carried:";
		$str["to"]	= "to";
		$str["at"]	= "at";
		$str["amount"]	= "amount";
		$str["quantity"]	= "quantity";
		$str["withdraw"]	= "withdraw";
		$str["deposit"]	= "deposit";
		$str["invalid"]	= "Invalid transaction";

		$str["loan"]	= "loan";
		$str["pay"]	= "pay";
		$str["leave"]	= "leave";

		$str["dump"]	= "<b>Warning:</b> %s is not sold here!<br>You're dumping your %s if you sell.";


		$str["hire"]	= "hire";
		$str["hirebitch"]	= "hire bitch to / for:";
		$str["maxbitch"]	= "You can't hire more than 10 bitches to carry drugs.";
		$str["maxloan"]	= "The loan shark only want to loan you \$; %s more.";	

		$str["cash"]	= "cash";
		$str["bank"]	= "bank";
		$str["debt"]	= "debt";
		$str["total"]	= "score";
		$str["name"]	= "name";

		$str["bitches"]	= "bitches";
		$str["life"]	= "health";
		$str["space"]	= "space";
		$str["guns"]	= "guns";

		$str["status"]	= "status";
		$str["goto"]	= "go to";
		$str["instruct"] = "instructions";
		$str["logout"]	= "log out";

		$str["chase"]	= "%s cops are chasing you! What do you do?";
		$str["surrender"] = "surrender";
		$str["fight"]	= "fire";
		$str["run"]	 = "run";
		$str["bribe"]	= "bribe";

		$str["nobribe"]	= "You don't have enough money to bribe all cops (\$ 20,000 a cop).";
		$str["bribed"]  = "You've bribed the cops (\$ %s and half of your drugs).";

		$str["youkilledcop"]	= "You killed a cop!";
		$str["allcopskilled"]	= "All cops are dead! You find \$ %s on them.";
		$str["youmissed"]	= "You failed to hit.";
		$str["escaped"]		= "You escaped.";
		$str["cantescape"]	= "You can't escape.";
		$str["copsshoot"]	= "%s cops are shooting...";
		$str["copshoot"]	= "The last cop shoots...";
		$str["bitchkilled"]	= "One of your bitches got killed.";
		$str["yourhit"]		= "You've been hit.";	
		$str["missed"]		= "Miss!";	
		$str["forfeit"]		 = "The <a href=\"http://www.usdoj.gov/dea/programs/af.htm\" target=\"_blank\">DEA</a> forfeits $ %s from your bank account.";

		$str["continue"]	= "continue";	

		$str["lostdrugs"]	= "They chased you! You lost the %s.";
		$str["foundbody"]	= "You find the dead body of a bitch with %s x %s.";
		$str["dead"]		= "You're <b>dead</b>!";

		$str["invalidname"]	= "Invalid name";
		$str["name"]		= "name";
		$str["hiresnitch"]	= "Hire a bitch to tip off a dealer to the cops.<br>The cops will attack that dealer.<br>Later you will be informed on the encounter.";
		$str["snitchhired"]	= "%s is being tipped of.";
		$str["snitched"]	= "You were tipped off by %s.";
		$str["report"]		= "<b>Snitch report</b>";

		$str["spyreport"]	= "<p><b>Spy report</b></p>Dealer %s is located in %s,<br>has \$ %s in cash, \$ %s in the bank and a debt of \$ %s.<br>%s has %s bitches, %s guns and space left for %s drugs.<br>Health is %s%%.";
		$str["hirespy"]		= "Hire a bitch to find out the status of another dealer. You receive an answer immediately.";

		$str["reloading"]	= "Your guns are being reloaded ...";
		$str["reloaded"]	= "Your guns are reloaded.";

		$str["loanhit1"]	= "The loan shark send some of his men.<br>They break your fingers and tell you to pay off the debt.";
		$str["loanhit2"]	= "The loan shark is serious!<br>You got beaten up by his men.";
		$str["loanhit3"]	= "The loan shark wasted you!";

		$str["d_killed"]	= "Dealer %s is dead.";
		$str["d_hit"]		= "Dealer %s was shot at and got hit.";
		$str["d_escaped"]	= "%s got away.";
		$str["d_arrested"]	= "%s is arrested.";
		$str["d_cop"]		= "A cop was wasted.";
		$str["d_cops"]		= "%s cops were wasted.";
		$str["d_allcops"]	= "All were wasted by %s.";
		$str["d_bitch"]		= "One bitch got killed.";
		$str["d_bitches"]	= "%s bitches got killed.";

		$str["dateformat"]	= "%A, %B %e, %T";
		$str["arrested"]	= "You are arrested.";
		$str["prison"]		= "prison";
		$str["inprison"]	= "You are in prison until %s CET.";
		$str["released"]	= "You are released.";

		$str["op_cantescape"]	= "%s tries to escape but can't get away.";
		$str["op_escaped"]	= "%s has escaped.";
		$str["op_stands"]	= "%s stands there like an idiot.";
		$str["op_shoots"]	= "%s fires...";

		$str["youkilledbitch"]	= "You wasted one of the bitches of %s.";
		$str["youkilledopponent"]	= "You killed %s. You find \$ %s on him.";
		$str["youshotopponent"]	   = "You hit %s.";
		$str["opponentdead"]	= "%s is dead.";

		$str["bitchgone"]	= "One of your bitches ran away.";

		$str["encounter"]	= "You run into %s, what do you do?";

		$str["op_status"]	= "Status of %s:<br>bitches: %s<br>weapons: %s<br>health: %s%%";

		$str["qod"]		= "Do you really want to overdose (and maybe become legendary)?";
		$str["yes"]		= "yes";
		$str["od"]		= "overdose";

		$currency = "\$";
?>