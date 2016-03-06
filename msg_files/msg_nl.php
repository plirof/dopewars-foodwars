<?php

		$language = "NL";

		$places = array(
			"Spangen",
			"Tussendijken",
			"Dijkzigt/Cool",
			"Oude Westen",
			"Centraal Station",
			"Tarwewijk",
			"Hillesluis",
			"Zuidplein");

		$special = array(
			0 => "de woekeraar op de Mathenesserdijk",
			1 => "de hero&iuml;nehoeren op de Keileweg",
			2 => "de polikliniek",
			4 => "het GWK",
			5 => "de wapenhandelaar in de Millinxbuurt");

		$drugs = array(
			array("name" => "LSD",			"min" => 1000,	"max"=> 4400,	"minmsg" => "", "maxmsg"=>"LSD is comming back to parties!"),
			array("name" => "cocaine",	"min" => 13000,	"max"=> 40000,	"minmsg" => "Underground people have discovered Rotterdam Airport: cocaine in abundance.",	"maxmsg" => "In de haven is een lading Columbiaanse coke onderschept."),
			array("name" => "heroine",	"min" => 5500,	"max"=> 13000,	"minmsg" => "In de Pauluskerk wordt gratis methadon verstrekt, de hero&iuml;ne markt is ingestort.",	"maxmsg" => "Hero&iuml;ne-junks komen hier massaal naar toe, er is een tekort aan smack."),
			array("name" => "hash",			"min" => 480,	"max"=> 1280,	"minmsg" => "Een Marokkaans schip heeft grote hoeveelheden hash afgeleverd.",	"maxmsg" => "Een container maroc is door de douane vernietigd."),
			array("name" => "wiet",			"min" => 315,	"max"=> 890,	"minmsg" => "",	"maxmsg" => "Een hennepkwekerij is opgerold, de wietprijzen zijn omhooggeschoten!"),
			array("name" => "speed",		"min" => 90,	"max"=> 250,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "XTC",			"min" => 2800,	"max"=> 3700,	"minmsg" => "Een nieuw XTC laboratorium dumpt pillen voor weinig.", "maxmsg" => "De politie heeft een XTC laboratorium ontmanteld."),
			array("name" => "valium",		"min" => 11,	"max"=> 60,		"minmsg" => "Rivaliserende dealers hebben een apotheek beroofd en verkopen goedkoop valium!",	"maxmsg" => ""),
			array("name" => "paddo's",		"min" => 630,	"max"=> 1300,	"minmsg" => "",	"maxmsg" => "In een proefproces zijn paddo's verboden, de prijzen schieten omhoog."),
			array("name" => "peyote",		"min" => 220,	"max"=> 700,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "PCP",			"min" => 1000,	"max"=> 2500,	"minmsg" => "",	"maxmsg" => ""));

		$bitchactions = array (
			array("name" => "geile neukseks",	"price" => 20),
			array("name" => "spion",			"price" => 6500),
			array("name" => "verklikker",		"price" => 10000),
			array("name" => "drugskoerier",		"price" => 35000));

		$fight		= Array("blijven staan",
							"over geven",
							"vluchten",
							"schieten");	

		$maxmsgs	= Array("%s is in de mode!",
							"Een lading %s is onderschept, er is schaarste!",
							"Verslaafden betalen belachelijke prijzen voor %s!"
							);
		$minmsgs	= Array("De markt wordt overspoeld met %s!",
							"%s is gvd niet duur vandaag!!"
							);

		$str["nospace"]		= "Je hebt geen ruimte voor %s drugs.";
		$str["nodrug"]		= "Je hebt geen  %s eenheden %s.";

		$str["morespace"]	= "Je kunt nu 10 extra units meenemen.";

		$str["doctor"]		= "Je zelf laten opereren kost &euro; %s.";
		$str["recovered"]	= "Je bent kerngezond.";
		$str["disease"]		= "Je hebt een SOA opgelopen.";
		$str["mugged"]		= "Je zakken zijn gerold: je geld is weg!";
		$str["ooh"]			= "'Ooh, was het ook goed voor jou?'";
		$str["operate"]		= "opereren";

		$str["nogunspace"]	= "Je kunt niet meer wapens dragen.";
		$str["noguncash"]	= "Dit wapen is te duur.";
		$str["nomoney"]		= "Je hebt daar geen geld voor.";
		$str["sell"]		= "verkoop";
		$str["buy"]			= "koop";
		$str["available"]	= "markt:";
		$str["carried"]		= "in bezit:";
		$str["to"]			= "naar";
		$str["at"]			= "bij";
		$str["amount"]		= "bedrag";
		$str["quantity"]	= "hoeveelheid";
		$str["withdraw"]	= "opnemen";
		$str["deposit"]		= "storten";
		$str["invalid"]		= "Ongeldige transactie";

		$str["dump"]		= "<b>Let op:</b> er wordt hier geen %s verhandeld!<br>Je dumpt dus je %s als je verkoopt.";

		$str["loan"]		= "lenen";
		$str["pay"]			= "betalen";
		$str["leave"]		= "uitgang";

		$str["hire"]		= "huren";
		$str["hirebitch"]	= "huur hoer voor/als:";
		$str["maxbitch"]	= "Je niet meer dan 10 hoeren huren als drugskoerier.";
		$str["maxloan"]		= "De woekeraar wil nog je maximaal &euro; %s lenen.";	

		$str["cash"]		= "contanten";
		$str["bank"]		= "bank";
		$str["debt"]		= "schuld";
		$str["total"]		= "totale vermogen";
		$str["name"]		= "naam";

		$str["bitches"]		= "hoeren";
		$str["life"]		= "gezondheid";
		$str["space"]		= "ruimte";
		$str["guns"]		= "wapens";

		$str["status"]		= "toestand";
		$str["goto"]		= "ga naar";
		$str["instruct"]	= "instructies";
		$str["logout"]		= "log uit";

		$str["chase"]		= "%s politie-agenten achtervolgen je! Wat doe je?";
		$str["surrender"]	= "overgeven";
		$str["fight"]		= "schieten";
		$str["run"]			= "vluchten";
		$str["bribe"]		= "omkopen";

		$str["nobribe"]		= "Je hebt niet genoeg cash om alle agenten om te kopen (&euro;20.000 per agent).";
		$str["bribed"]		= "Je hebt de agenten voor &euro;%s omgekocht en door ze de helft van je drugs te geven.";

		$str["youkilledcop"]	= "Je hebt een agent doodgeschoten!";
		$str["allcopskilled"]	= "Alle agenten zijn dood! Je vindt &euro; %s in hun portefeuilles.";
		$str["youmissed"]	= "Je schoot en miste.";
		$str["escaped"]		= "Je bent ontsnapt.";
		$str["cantescape"]	= "Je kunt niet wegkomen.";
		$str["copsshoot"]	= "De politie schiet met %s agenten...";
		$str["copshoot"]	= "De laatste agent schiet...";
		$str["bitchkilled"]	= "E&eacute;n van je hoeren is doodgeschoten.";
		$str["yourhit"]		= "Je bent geraakt.";	
		$str["missed"]		= "Niet geraakt!";	
		$str["forfeit"]		= "Door een plukze-maatregel wordt &euro; %s van je bankrekening gevorderd door het <a href=\"http://www.openbaarministerie.nl/over_om/over_om.php#31\" target=\"_blank\">BOOM</a>.";

		$str["continue"]	= "verder";	

		$str["onthemove"]	= "Onderweg naar";
		$str["lostdrugs"]	= "Je werd achtervolgd door een <b>stadsmarinier</b>. Onderweg ben je je %s kwijtgeraakt.";
		if (mt_rand(0,1))
			$str["lostdrugs"]	   = "Je werd achtervolgd door de <b>Nachtwacht</b>. Onderweg ben je je %s kwijtgeraakt.";
		$str["foundbody"]	= "Je vindt het lijk van een dode hero&iuml;nehoer met %s x %s.";
		$str["dead"]		= "Je bent <b>dood</b>!";

		$str["invalidname"]	= "Ongeldige naam";
		$str["name"]		= "naam";
		$str["hiresnitch"]	= "Huur een hero&iuml;nehoer om een andere dealer bij de politie aan te geven.<br>De politie zal deze dealer aanpakken.<br>De resultaten krijg je gemeld zodra de politie actie heeft ondernomen.";
		$str["snitchhired"]	= "%s wordt verklikt.";
		$str["snitched"]	= "Je bent verraden door %s.";
		$str["report"]		= "<b>Rapportage verklikker</b>";

		$str["spyreport"]	= "<p><b>Rapportage spion</b></p>Dealer %s bevindt zich in %s,<br>heeft &euro; %s aan contanten, &euro; %s op de bank en een schuld van &euro; %s bij de woekeraar.<br>%s heeft %s hero&iuml;nehoeren, %s wapens en nog ruimte voor %s drugs.<br>De gezondheid is %s%%.";
		$str["hirespy"]		= "Huur een hero&iuml;nehoer om de toestand van een andere dealer te achterhalen.<br>Je krijgt meteen antwoord.";

		$str["reloading"]	= "Je wapens worden opnieuw geladen ...";
		$str["reloaded"]	= "Je wapens zijn opnieuw geladen.";

		$str["loanhit1"]	= "Je komt wat mannetjes van de woekeraar tegen.<br>Ze breken je vingers en maken je duidelijk je schuld af te lossen.";
		$str["loanhit2"]	= "Het is de woekeraar menes!<br>Je wordt met een loden pijp bewerkt.";
		$str["loanhit3"]	= "De woekeraar heeft je te pakken!<br>Je voeten worden in beton gestort en je wordt in de Maas gegooid.";

		$str["d_killed"]	= "Dealer %s is gedood.";
		$str["d_hit"]		= "Dealer %s is beschoten en gewond geraakt.";
		$str["d_escaped"]	= "%s kon ontkomen.";
		$str["d_arrested"]	= "%s is gearresteerd.";
		$str["d_cop"]		= "E&eacute;n agent kwam om.";
		$str["d_cops"]		= "%s agenten kwamen om.";
		$str["d_allcops"]	= "Alle agenten zijn doodgeschoten door %s.";
		$str["d_bitch"]		= "E&eacute;n hoer is doodgeschoten.";
		$str["d_bitches"]	= "%s hoeren zijn doodgeschoten.";

		$str["dateformat"]	= "%d %B, %H:%M";
		$str["arrested"]	= "Je bent gearresteerd.";
		$str["prison"]		= "de gevangenis";
		$str["inprison"]	= "Je zit in de gevangenis tot %s.";
		$str["released"]	= "Je bent vrijgelaten.";

		$str["op_cantescape"]	= "%s probeert te vluchten, maar kan niet wegkomen.";
		$str["op_escaped"]	= "%s is gevlucht.";
		$str["op_stands"]	= "%s staat als een idioot toe te kijken.";
		$str["op_shoots"]	= "%s schiet...";

		$str["youkilledbitch"]	= "Je hebt &eacute;&eacute;n van de hoeren van %s doodgeschoten.";
		$str["youkilledopponent"]	= "Je hebt %s doodgeschoten. Je vindt &euro; %s in de portefeuille.";
		$str["youshotopponent"]	= "Je hebt %s geraakt.";
		$str["opponentdead"]	= "%s is dood.";

		$str["bitchgone"]	= "E&eacute;n van je hoeren is er vandoor gegaan.";

		$str["encounter"]	= "Je komt %s tegen, wat doe je?";

		$str["op_status"]	= "Toestand van %s:<br>hoeren: %s<br>wapens: %s<br>gezondheid: %s%%";

		$str["qod"]		= "Wil je echt een overdosis nemen (en wellicht legendarisch worden)?";
		$str["yes"]		= "ja";
		$str["od"]		= "overdosis";

		$currency = "&euro;";

		?>