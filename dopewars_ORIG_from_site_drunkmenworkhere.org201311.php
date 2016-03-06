<?php

/******************************************************************************

dopewars - deal drugs to make lots and lots of money
Copyright (C) 2002 - 2003 drunkmenworkhere.org

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
http://www.gnu.org/licenses/gpl.txt

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

******************************************************************************/


/******************************************************************************

REQUIREMENTS:

  - PHP 4.2 or later
  - PostgreSQL 7.3


DATABASE:

  Dopewars uses 2 PostrgreSQL tables, you can use this SQL to create them in
  a database called 'drunkmen' (edit class db if you want to use a different 
  database name): 

    CREATE TABLE dopewars (
        id              serial,
        name            text,
        password        text,
        score           int,
        onthemove       boolean,
        player          text,
        date            datetime DEFAULT now ( )
    );

    CREATE UNIQUE INDEX dopewars_name ON dopewars (name);

    CREATE TABLE dopescores (
        id              serial,
        name            text,
        password        text,
        score           int,
        date            datetime DEFAULT now ( )
    );

  WARNING: porting Dopewars to MySQL is non-trivial since it uses row-level
  locking (select ... for update) which is not implemented in MySQL.

SCRIPT:

  The complete dopewars script is in this single file, the include file is 
  irrelevant for dopewars. The files referenced in this script located in 
  ../data are not required either.

  register_globals should be set to 'on' in your php.ini

  WARNING: the code is quite crufty!

******************************************************************************/



mt_srand ((float) microtime() * 1000000);


session_register("uid");
session_register("player");
session_register("language");

define("MAXLOAN", 2000000);
define("MAXUSERS", 20);



// ****************************************************************************
// CHECK FOR SEMAPHORE (created by database vacuum script in crontab)

$sem = @file("../data/185sem");
if ($sem[0]) {
    echo "<html><head><link rel=\"stylesheet\" href=\"default.css\" type=\"text/css\">";
    echo "<title>dopewars</title></head>\n<body>";
    echo "<h2>dopewars</h2><p>is temporary offline<br />try again in a few minutes</p>\n";
    echo "<!--\n start time: " . $sem[0] . "--></body></html>";
    exit;
}


// ****************************************************************************
// GENERIC POSTGRESQL WRAPPER

class db {
    var $db;
    
    function db() {
//        error_reporting(1); // no warnings
        $this->db=pg_connect("dbname=drunkmen");  // database name is drunkmen
        if (!$this->db) {
            echo "No database connection";
            exit;
        }
    }
    
    function qry($qry,$num=0)
    {
        if (!isset($this->db)) return FALSE;
        
        $rel = pg_query($this->db, $qry);
        if (!$rel) {
            return array();
        }
        
        $nr = pg_numrows($rel);
        if ($num==0) $num=$nr;
        else if ($num>$nr) $num=$nr;
        
        $res = array();
        
        for($i = 0; $i < $num; $i++)
            $res[] = pg_fetch_array($rel, $i, PGSQL_ASSOC);

        return $res;
    }
}

$db = new db();


// ****************************************************************************
// HIGH SCORE LIST

if ($action == "hiscore") {
    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
    echo "<html><head><title>dopewars</title><link rel=\"stylesheet\" href=\"default.css\" type=\"text/css\"></head><body><h3>dopewars - high scores</h3>";
    
    echo "<p><b>active dealers</b></p>";
    $qry = "select name, score from dopewars order by score desc";
    $results = $db->qry($qry);
    while (list($key, $val) = each ($results)) {
            echo $key+1;
            echo " - " . htmlentities($val["name"]) . " (\$" . $val["score"] . ")<br>";
    }
    
    echo "<p><b>legendary dead dealers - all time high scores</b></p><p>(top 50)</p>";
    $qry = "select name, score from dopescores order by score desc limit 50";
    $results = $db->qry($qry);
    while (list($key, $val) = each ($results)) {
            echo $key+1;
            echo " - " . htmlentities($val["name"]) . " (\$" . $val["score"] . ")<br>";
    }
    
    echo "<p><a href=\"$PHP_SELF\">back</a></p>";
    echo "</body></html>";
    exit;
}


// ****************************************************************************
// LOGIN SCREEN 

if (!$player || $logout) {
    
    $uid = "";
    $player = "";

    switch ($action) {
        case "login":
            $name = trim(str_replace(",", " ", substr($name,0,20)));
            $password = trim(substr($password,0,15));
            if (addslashes($name) != $name || (strstr($name, "  ") && $new)) {
                $error = "Invalid characters in username.";
            } else if (addslashes($password) != $password) {
                $error = "Invalid characters in password.";
            } else if ($name != "" && $password != "") {
                $language=$lang;
                $lname = strtolower($name);
                $qry = "select * from dopewars where lower(name) = '$lname';";
                $result = $db->qry($qry);
                if ($new) {
                    if ($result != array()) {
                        $error = "There already exists a user named \"$name\".";
                    } else {
                        check_max();
                        $player["name"] = $name;
                        $player["cash"] = 5000;
                        $player["debt"] = 4761;
                        $player["bank"] = 0;
                        $player["guns"] = 0;
                        $player["bitches"] = 2;
                        $player["space"] = 20 + $player["bitches"] * 10;
                        $player["held"] = 0;
                        $player["life"] = 100;
                        $player["guns"] = array();
                        $player["drugs"] = array();
                        $player["drugprices"] = array();
                        $player["prices"] = array();
                        $player["destination"] = 1;
                        $player["snitches"] = array();
                        $player["currentsnitches"] = array();
                        $player["snitchreport"] = array();
                        $player["fighthistory"] = array();


                        $pl = addslashes(serialize($player));
                        $qry = "insert into dopewars (name, password, score, player) values ('$name', '$password', 0, '$pl');";
                        $result = $db->qry($qry);

                        $uid = $name;
                    }                                            
                } else {
                    if ($result == array()) {
                        $error = "No such user";
                    } else if ($result[0]["password"] != $password) {
                        $error = "Invalid password";
                    } else {
                        check_max();
                        $uid = addslashes($result[0]["name"]);
                    }
                    
                }                    
            }
            if (!$uid) {
                echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
                echo "<html><head><title>dopewars</title><link rel=\"stylesheet\" href=\"default.css\" type=\"text/css\"></head><body><h3>dopewars - login</h3>";
                echo "<div style=\"height:20px;margin-top:30px;\">";
                if ($error) {
                    echo "<p>$error</p>";
                }
                echo "</div>";
                echo "<form action=\"$PHP_SELF\" method=\"POST\">";
                echo "<p>name: ";
                echo "<input name=\"name\" type=\"text\" value=\"\" class=\"button\" size=\"10\" maxlength=\"20\"></p>";
                echo "<p>password: ";
                echo "<input name=\"password\" type=\"password\" value=\"\" class=\"button\" size=\"10\" maxlength=\"15\"></p>";
                echo "<p>language: ";
                echo "<select name=\"lang\" class=\"button\"><option value=\"EN\">English</option>";
                if (strtolower($HTTP_ACCEPT_LANGUAGE) == "nl") {
                    echo "<option value=\"NL\" selected>Nederlands</option>";
                } else {
                    echo "<option value=\"NL\">Nederlands</option>";
                }
                echo "</select></p>";
                if ($new) {
                    echo "<p><input name=\"new\" type=\"checkbox\" checked> <label for=\"new\">create new account</label></p>";
                } else {
                    echo "<p><input name=\"new\" type=\"checkbox\"> <label for=\"new\">create new account</label></p>";
                }
                echo "<input name=\"action\" type=\"hidden\" value=\"login\">";
                echo "<input type=\"submit\" value=\"login\" class=\"button\"> ";
                echo "</form>";    
                echo "</body></html>";
                exit;
            }
            break;

        default:

            // genereric script to check for drunkmenworkhere editions
            // comment out next two lines, they're only useful on drunkmenworkhere.org
            @include("editions.php");
            @checkEdition();

            echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
            echo "<html><head><title>dopewars</title><link rel=\"stylesheet\" href=\"default.css\" type=\"text/css\"></head><body>";
            echo "<h2>dopewars</h2>";
            echo "<p>(deal drugs to make lots and lots of money)</p>";
            echo "<p><a href=\"$PHP_SELF?action=login\">login</a> | ";
            if (strtolower($HTTP_ACCEPT_LANGUAGE) == "nl") {
                echo "<a href=\"185NL.html\" target=\"_blank\">instructions</a> | ";
            } else {
                echo "<a href=\"185EN.html\" target=\"_blank\">instructions</a> | ";
            }
            echo "<a href=\"$PHP_SELF?action=hiscore\">high scores</a> | ";
            echo "<a href=\"185info.html\">info</a></p>";
            echo "</body></html>";
            exit;
    }
}


// ****************************************************************************
// SOME FUNCTIONS

function check_max(){
    global $db;

    $qry = "select count(*) from dopewars where extract(epoch from now()-date)<60;";
    $result = $db->qry($qry);
    if ($result[0]["count"] > MAXUSERS) {
        echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
        echo "<html><title>dopewars</title><head><link rel=\"stylesheet\" href=\"default.css\" type=\"text/css\"></head><body><h2>dopewars - login</h2>";
            echo "<p>Too many users connected.<br>Please try to login again in a minute.</p><p><a href=\"$PHP_SELF?action=login\">back</a></p></body></html>";
        exit;
    }
}

function save_exit(){
    global $db, $player, $uid, $onthemove, $updatedate;


    // calculate total
    $player["cash"] = round($player["cash"]);
    $player["bank"] = round($player["bank"]);
    $player["debt"] = round($player["debt"]);

    $player["total"] = $player["cash"] + $player["bank"] - $player["debt"];
    reset ($player["drugs"]);
    while (list($drug, $val) = each($player["drugs"])) {
        $player["total"] += $player["drugprices"][$drug] * $val;
    }

    // update database

    $pl = addslashes(serialize($player));
    $qry = "update dopewars set player='$pl', onthemove=$onthemove";
    $qry .= ", score=" . round($player["total"]);
    if ($updatedate) {
        $qry .= ", date=now()";
    }    
    $qry .= " where name='$uid';\ncommit;";
    $db->qry($qry);

    exit;
}

function save_player($uid, $player, $stopmoving=0) {
    global $db;

    
    // calculate total
    
    $player["total"] = $player["cash"] + $player["bank"] - $player["debt"];
    reset ($player["drugs"]);
    while (list($drug, $val) = each($player["drugs"])) {
        $player["total"] += $player["drugprices"][$drug] * $val;
    }
    
    // update database
    
    $pl = addslashes(serialize($player));
    $qry = "update dopewars set player='$pl'"; 
    if ($stopmoving) {
        $qry .= ", onthemove=FALSE";
    }
    $qry .= ", score=" . round($player["total"]);
    $qry .= " where name='$uid';";
    $db->qry($qry);
}

function banner($i, $j) {
    // this function is work in conjuction with click clock (http://drunkmenworkhere.org/187.php)
    // comment out, works on drunkmenworkhere.org only
    global $language;

    if ($i > $j) {
        $k = $j;
        $j = $i;
        $i = $k;
    }

    $foo = 0;
    for ($n=0; $n<=$i; $n++) {
        $foo += $n;
    }
    
    $foo = $i*8 - $foo + ($j - $i) - 1;
    if ($language == "NL") {
        $foo += 28;
    }
    $banners = @file("../data/dopewars_banners.txt");
    if ($banners[$foo]!="\n" && $banners[$foo]!="") {
        $b = explode("\t", $banners[$foo]);
        echo "<p><a href=\"http://" . trim($b[1]) . "\" target=\"_blank\"><img src=\"http://" . trim($b[0]) . "\" border=\"0\" alt=\"play click clock to get your free banner here\"></a>";
    }
}

function check_life() {
    global $db, $player, $uid, $str,$pass, $PHP_SELF;
    
    if ($player["life"] > 0) {
        if ($player["life"] > 100) {
            $player["life"] = 100;
        }
        return;
    } else {
        report_snitches();
        $qry = "insert into dopescores (name, password, score) values ('" . $player["name"] . "', '$pass', '" . $player["total"] . "');";
        $db->qry($qry);
        $player = "";
        $qry = "delete from dopewars where name = '$uid';\ncommit;";
        $db->qry($qry);
        echo "<br>". $str["dead"];
        echo "<p><a href=\"$PHP_SELF?logout=1\">new game</a>";
        echo "</body></html>";
        exit;    
    }
}

function dealer_list() {
    global $db;

    $qry = "select id, name from dopewars order by name;";
    $result = $db->qry($qry);
    echo "<select name=\"dealer\" class=\"button\">";
    echo "<option></option>";
    while (list ($key, $val) = each($result)) {
        echo "<option value=\"{$val['id']}\">" . htmlentities($val["name"]) . "</option>";
    }
    echo "</select>";
}

function report_snitches() {
        global $db, $uid, $player, $str;

    $player["fightreport"]["player"] = $uid;
    $foo = array_unique($player["currentsnitches"]);
    $foo = implode (", ", $foo);
    if ($foo) {
        printf("<br>" . $str["snitched"], $foo);
    }

    $qry = implode("','", array_unique($player["currentsnitches"]));
    $qry = "select * from dopewars where name in ('$qry') for update;";
    $results = $db->qry($qry);
    while (list ($key, $result) = each($results)) {    
        $subject = unserialize(stripslashes($result["player"]));
        $subject["snitchreport"][] = $player["fightreport"];
        save_player($result["name"], $subject);
    }
    $player["currentsnitches"] = array();
    $player["fightreport"] = array();
}

function lose_bitch($player) {
    global $drugs, $guns;

    // subtract proportional amount of drugs
    reset ($player["drugs"]);
    while (list($key, $val) = each($player["drugs"])) {
        $num = round($val/($player["bitches"] + 2));
        $player["drugs"][$key] -= $num;
        $player["space"] += $num;
    }

    // remove guns
    if (round(array_sum($player["guns"])/($player["bitches"] + 2))) {
        reset ($player["guns"]);
        while (list($key, $val) = each($player["guns"])) {
            if ($val) {
                $player["guns"][$key] -= 1;
                break;
            }
        }
    }

    $player["bitches"]--;
    $player["space"] -=10;

    return $player;
}

function printmenu($enabled = 1) {
    global $places, $str, $currency, $player, $special, $language, $PHP_SELF;


    // status table
    
    echo "
    <div style=\"float:right; height:300px; width:200px; text-align:right;\"><table width=\"200\">";
    
    echo "<tr><th colspan=2>" . $str["status"] . "</th></tr>";
    
    echo "<tr><td>" . $str["name"] . ": " . htmlentities($player["name"]) . "</td></tr>\n";
    echo "<tr><td>" . $str["cash"] . ": $currency " . $player["cash"] . "</td></tr>\n";
    echo "<tr><td>" . $str["bank"] . ": $currency " . $player["bank"] . "</td></tr>\n";
    echo "<tr><td>" . $str["debt"] . ": $currency " . $player["debt"] . "</td></tr>\n";
    echo "<tr><td>" . $str["bitches"] . ": " . $player["bitches"] . "</td></tr>\n";
    echo "<tr><td>" . $str["life"] . ": " . $player["life"] . "%</td></tr>\n";
    echo "<tr><td>" . $str["space"] . ": " . $player["space"] . "</td></tr>\n";
    echo "<tr><td>" . $str["guns"] . ": " . array_sum($player["guns"]) . "</td></tr>\n";
    
    
    echo "</table></div>";
    
    
    // location menu table
    
    echo "
    <div style=\"float:left; height:300px; width:200px; text-align:left; \"><table width=\"200\">";
    echo "<tr><th colspan=2>" . $str["goto"] . "</th></tr>";
    while (list($key, $val) = each($places)) {
        if ($player["location"] == $key) {
            echo "<tr><td><b>&raquo; $val &laquo;</b></td></tr>\n";
        } else {
            if ($enabled ) {
                echo "<tr><td><a href=\"$PHP_SELF?l=$key\" title=\"" . $special[$key] . "\">$val</a></td></tr>\n";
            } else {
                echo "<tr><td>$val</td></tr>\n";
            }
        }
    }
    if ($enabled) {
        echo "<tr><td><br><br><a href=\"$PHP_SELF?action=hiscore\" target=\"_blank\">high scores</a></td></tr>\n";
        echo "<tr><td><a href=\"185$language.html\" target=\"_blank\">" . $str["instruct"] . "</a></td></tr>\n";
        echo "<tr><td><a href=\"$PHP_SELF?action=od&s=0\">" . $str["od"] . "</a></td></tr>\n";
        echo "<tr><td><a href=\"$PHP_SELF?logout=1\">" . $str["logout"] . "</a></td></tr>\n";
    } else {
        echo "<tr><td><br><br>high scores</td></tr>\n";
        echo "<tr><td>" . $str["instruct"] . "</td></tr>\n";
        echo "<tr><td>" . $str["od"] . "</td></tr>\n";
        echo "<tr><td>" . $str["logout"] . "</td></tr>\n";
    }
    echo "</table></div>";
}

function print_header($reload = 0) {
    global $PHP_SELF;

    echo "<html><head><title>dopewars</title><link rel=\"stylesheet\" href=\"default.css\" type=\"text/css\">";

    if ($reload) {
        echo  "<meta http-equiv=\"refresh\" content=\"3; URL=$PHP_SELF?reload=1\">";
    }

    echo "</head><body>\n";
}

// ****************************************************************************
// LANGUAGE STUFF

if ($language == "NL") {

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
        array("name" => "LSD",        "min" => 1000,    "max"=> 4400,     "minmsg" => "", maxmsg=>"LSD is bezig aan een come-back in het party-circuit!"),
        array("name" => "coca&iuml;ne",    "min" => 15000,    "max"=> 29000,    "minmsg" => "Bolletjesslikkers hebben Rotterdam Airport ontdekt: coca&iuml;ne in overvloed.",    "maxmsg" => "In de haven is een lading Columbiaanse coke onderschept."),
        array("name" => "hero&iuml;ne",    "min" => 5500,    "max"=> 13000,    "minmsg" => "In de Pauluskerk wordt gratis methadon verstrekt, de hero&iuml;ne markt is ingestort.",    "maxmsg" => "Hero&iuml;ne-junks komen hier massaal naar toe, er is een tekort aan smack."),
        array("name" => "hash",        "min" => 480,    "max"=> 1280,    "minmsg" => "Een Marokkaans schip heeft grote hoeveelheden hash afgeleverd.",    "maxmsg" => "Een container maroc is door de douane vernietigd."),
        array("name" => "wiet",        "min" => 315,    "max"=> 890,    "minmsg" => "",    "maxmsg" => "Een hennepkwekerij is opgerold, de wietprijzen zijn omhooggeschoten!"),
        array("name" => "speed",    "min" => 90,    "max"=> 250,    "minmsg" => "",    "maxmsg" => ""),
        array("name" => "XTC",        "min" => 2800,    "max"=> 3700,    "minmsg" => "Een nieuw XTC laboratorium dumpt pillen voor weinig.", "maxmsg" => "De politie heeft een XTC laboratorium ontmanteld."),
        array("name" => "valium",    "min" => 11,    "max"=> 60,    "minmsg" => "Rivaliserende dealers hebben een apotheek beroofd en verkopen goedkoop valium!",    "maxmsg" => ""),
        array("name" => "paddo's",    "min" => 630,    "max"=> 1300,    "minmsg" => "",    "maxmsg" => "In een proefproces zijn paddo's verboden, de prijzen schieten omhoog."),
        array("name" => "peyote",    "min" => 220,    "max"=> 700,    "minmsg" => "",    "maxmsg" => ""),
        array("name" => "PCP",        "min" => 1000,    "max"=> 2500,    "minmsg" => "",    "maxmsg" => ""));

    $bitchactions = array (
        array("name" => "geile neukseks",    "price" => 20),
        array("name" => "spion",        "price" => 6500),
        array("name" => "verklikker",           "price" => 10000),
        array("name" => "drugskoerier",        "price" => 35000));

    $fight = array("blijven staan", "over geven", "vluchten", "schieten");    
        

    $maxmsgs = array("%s is in de mode!", "Een lading %s is onderschept, er is schaarste.", "Verslaafden betalen belachelijke prijzen voor %s.");
    $minmsgs = array("De markt wordt overspoeld met %s");

    $str["nospace"]    = "Je hebt geen ruimte voor %s drugs.";
    $str["nodrug"]    = "Je hebt helemaal niet  %s eenheden %s.";

    $str["morespace"]    = "Je kunt nu 10 extra units meenemen.";

    $str["doctor"]    = "Je zelf laten opereren kost &euro; %s.";
    $str["recovered"]    = "Je bent kerngezond.";
    $str["disease"]    = "Je hebt een SOA opgelopen.";
    $str["mugged"]    = "Je zakken zijn gerold: je geld is weg!";
    $str["ooh"]    = "'Ooh, was het ook goed voor jou?'";
    $str["operate"]    = "opereren";

    $str["nogunspace"]    = "Je kunt niet meer wapens dragen.";
    $str["noguncash"]    = "Dit wapen is te duur.";
    $str["nomoney"]    = "Je hebt daar geen geld voor.";
    $str["sell"]    = "verkoop";
    $str["buy"]    = "koop";
    $str["available"]    = "markt:";
    $str["carried"]        = "in bezit:";
    $str["to"]    = "naar";
    $str["at"]    = "bij";
    $str["amount"]    = "bedrag";
    $str["quantity"]    = "hoeveelheid";
    $str["withdraw"]    = "opnemen";
    $str["deposit"]    = "storten";
    $str["invalid"]    = "Ongeldige transactie";

    $str["dump"]    = "<b>Let op:</b> er wordt hier geen %s verhandeld!<br>Je dumpt dus je %s als je verkoopt.";

    $str["loan"]    = "lenen";
    $str["pay"]    = "betalen";
    $str["leave"]    = "uitgang";

    $str["hire"]    = "huren";
    $str["hirebitch"]    = "huur hoer voor/als:";
    $str["maxbitch"]    = "Je niet meer dan 10 hoeren huren als drugskoerier.";
    $str["maxloan"]    = "De woekeraar wil nog je maximaal &euro; %s lenen.";    

    $str["cash"]    = "contanten";
    $str["bank"]    = "bank";
    $str["debt"]    = "schuld";
    $str["total"]    = "totale vermogen";
    $str["name"]    = "naam";

    $str["bitches"]    = "hoeren";
    $str["life"]    = "gezondheid";
    $str["space"]    = "ruimte";
    $str["guns"]    = "wapens";

    $str["status"]    = "toestand";
    $str["goto"]    = "ga naar";
    $str["instruct"] = "instructies";
    $str["logout"]    = "log uit";

    $str["chase"]    = "%s politie-agenten achtervolgen je! Wat doe je?";
    $str["surrender"] = "overgeven";
    $str["fight"]    = "schieten";
    $str["run"]     = "vluchten";
    $str["bribe"]    = "omkopen";

    $str["nobribe"]    = "Je hebt niet genoeg cash om alle agenten om te kopen (&euro;20.000 per agent).";
    $str["bribed"]    = "Je hebt de agenten voor &euro;%s omgekocht en door ze de helft van je drugs te geven.";

    $str["youkilledcop"]    = "Je hebt een agent doodgeschoten!";
    $str["allcopskilled"]    = "Alle agenten zijn dood! Je vindt &euro; %s in hun portefeuilles.";
    $str["youmissed"]    = "Je schoot en miste.";
    $str["escaped"]        = "Je bent ontsnapt.";
    $str["cantescape"]    = "Je kunt niet wegkomen.";
    $str["copsshoot"]    = "De politie schiet met %s agenten...";
    $str["copshoot"]    = "De laatste agent schiet...";
    $str["bitchkilled"]    = "E&eacute;n van je hoeren is doodgeschoten.";
    $str["yourhit"]        = "Je bent geraakt.";    
    $str["missed"]        = "Niet geraakt!";    
    $str["forfeit"]        = "Door een plukze-maatregel wordt &euro; %s van je bankrekening gevorderd door het <a href=\"http://www.openbaarministerie.nl/over_om/over_om.php#31\" target=\"_blank\">BOOM</a>.";
    
    $str["continue"]    = "verder";    

    $str["onthemove"]    = "onderweg naar";
    $str["lostdrugs"]    = "Je werd achtervolgd door een <a href=\"http://www.stadhuis.rotterdam.nl/read/3647?menuprefix=968\" target=\"_blank\">stadsmarinier</a>. Onderweg ben je je %s kwijtgeraakt.";
    if (mt_rand(0,1)) {
        $str["lostdrugs"]       = "Je werd achtervolgd door de <a href=\"http://w3s.rotterdamsdagblad.nl/modules/w3s-merge.phtml?w3s_dbms=msql&w3s_host=w3s.rotterdamsdagblad.nl&w3s_database=rdagblad&w3s_table=artikel&w3s_id=660&w3s_template=Artikel\" target=\"_blank\">Nachtwacht</a>. Onderweg ben je je %s kwijtgeraakt.";
    }
    $str["foundbody"]    = "Je vindt het lijk van een dode hero&iuml;nehoer met %s x %s.";
    $str["dead"]        = "Je bent <b>dood</b>!";

    $str["invalidname"]    = "Ongeldige naam";
    $str["name"]        = "naam";
    $str["hiresnitch"]    = "Huur een hero&iuml;nehoer om een andere dealer bij de politie aan te geven.<br>De politie zal deze dealer aanpakken.<br>De resultaten krijg je gemeld zodra de politie actie heeft ondernomen.";
    $str["snitchhired"]    = "%s wordt verklikt.";
    $str["snitched"]    = "Je bent verraden door %s.";
    $str["report"]        = "<b>Rapportage verklikker</b>";

    $str["spyreport"]    = "<p><b>Rapportage spion</b></p>Dealer %s bevindt zich in %s,<br>heeft &euro; %s aan contanten, &euro; %s op de bank en een schuld van &euro; %s bij de woekeraar.<br>%s heeft %s hero&iuml;nehoeren, %s wapens en nog ruimte voor %s drugs.<br>De gezondheid is %s%%.";
    $str["hirespy"]        = "Huur een hero&iuml;nehoer om de toestand van een andere dealer te achterhalen.<br>Je krijgt meteen antwoord.";

    $str["reloading"]    = "Je wapens worden opnieuw geladen ...";
    $str["reloaded"]    = "Je wapens zijn opnieuw geladen.";

    $str["loanhit1"]    = "Je komt wat mannetjes van de woekeraar tegen.<br>Ze breken je vingers en maken je duidelijk je schuld af te lossen.";
    $str["loanhit2"]    = "Het is de woekeraar menes!<br>Je wordt met een loden pijp bewerkt.";
    $str["loanhit3"]    = "De woekeraar heeft je te pakken!<br>Je voeten worden in beton gestort en je wordt in de Maas gegooid.";


    $str["d_killed"]    = "Dealer %s is gedood.";
    $str["d_hit"]        = "Dealer %s is beschoten en gewond geraakt.";
    $str["d_escaped"]    = "%s kon ontkomen.";
    $str["d_arrested"]    = "%s is gearresteerd.";
    $str["d_cop"]        = "E&eacute;n agent kwam om.";
    $str["d_cops"]        = "%s agenten kwamen om.";
    $str["d_allcops"]    = "Alle agenten zijn doodgeschoten door %s.";
    $str["d_bitch"]        = "E&eacute;n hoer is doodgeschoten.";
    $str["d_bitches"]    = "%s hoeren zijn doodgeschoten.";

    $str["dateformat"]    = "%A %e %B, %T";
    $str["arrested"]    = "Je bent gearresteerd.";
    $str["prison"]        = "de gevangenis";
    $str["inprison"]    = "Je zit in de gevangenis tot %s.";
    $str["released"]    = "Je bent vrijgelaten.";

    $str["op_cantescape"]    = "%s probeert te vluchten, maar kan niet wegkomen.";
    $str["op_escaped"]    = "%s is gevlucht.";
    $str["op_stands"]    = "%s staat als een idioot toe te kijken.";
    $str["op_shoots"]    = "%s schiet...";

    $str["youkilledbitch"]    = "Je hebt &eacute;&eacute;n van de hoeren van %s doodgeschoten.";
    $str["youkilledopponent"]    = "Je hebt %s doodgeschoten. Je vindt &euro; %s in de portefeuille.";
    $str["youshotopponent"]       = "Je hebt %s geraakt.";
    $str["opponentdead"]    = "%s is dood.";
    
    $str["bitchgone"]    = "E&eacute;n van je hoeren is er vandoor gegaan.";
    
    $str["encounter"]    = "Je komt %s tegen, wat doe je?";
    if (mt_rand(0,1)) {
        $str["encounter"]       = "Je komt %s tegen in een <a href=\"http://www.groetzone.nl\" target=\"_blank\"><span style=\"color:red;\">groet</span><span style=\"color:green;\">zone</span></a>, wat doe je?";
    }
    
    $str["op_status"]    = "Toestand van %s:<br>hoeren: %s<br>wapens: %s<br>gezondheid: %s%%";

    $str["qod"]        = "Wil je echt een overdosis nemen (en wellicht legendarisch worden)?";
    $str["yes"]        = "ja";
    $str["od"]        = "overdosis";
    
    
    $currency = "&euro;";
    

} else {
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
        array("name" => "acid",        "min" => 1000,    "max"=> 4400,     "minmsg" => "The market is flooded with cheap home-made acid!"),
        array("name" => "cocaine",    "min" => 15000,    "max"=> 29000,    "minmsg" => "",    "maxmsg" => ""),
        array("name" => "heroin",    "min" => 5500,    "max"=> 13000,    "minmsg" => "",    "maxmsg" => ""),
        array("name" => "hashish",    "min" => 480,    "max"=> 1280,    "minmsg" => "The Marrakesh Express has arrived!",    "maxmsg" => ""),
        array("name" => "weed",        "min" => 315,    "max"=> 890,    "minmsg" => "Weed prices have bottomed out!",    "maxmsg" => "Weed prices are ridiculously high."),
        array("name" => "speed",    "min" => 90,    "max"=> 250,    "minmsg" => "",    "maxmsg" => ""),
        array("name" => "ecstacy",    "min" => 2800,    "max"=> 3700,    "minmsg" => "",    "maxmsg" => ""),
        array("name" => "ludes",    "min" => 11,    "max"=> 60,    "minmsg" => "Rival drug dealers raided a pharmacy and are selling cheap ludes!",    "maxmsg" => ""),
        array("name" => "shrooms",    "min" => 630,    "max"=> 1300,    "minmsg" => "",    "maxmsg" => ""),
        array("name" => "peyote",    "min" => 220,    "max"=> 700,    "minmsg" => "",    "maxmsg" => ""),
        array("name" => "PCP",        "min" => 1000,    "max"=> 2500,    "minmsg" => "",    "maxmsg" => ""));

       $bitchactions = array (
                array("name" => "sex",       "price" => 20),
                array("name" => "spy",                "price" => 6500),
                array("name" => "snitch",           "price" => 10000),
                array("name" => "drug runner",         "price" => 35000));



    $str["sell"]    = "sell";
    $str["buy"]    = "buy";
    $str["available"]    = "available:";
    $str["carried"]        = "carried:";
    $str["to"]    = "to";
    $str["at"]    = "at";
    $str["amount"]    = "amount";
    $str["quantity"]    = "quantity";
    $str["withdraw"]    = "withdraw";
    $str["loan"]    = "loan";
    $str["pay"]    = "pay";
    $str["invalid"]    = "Invalid transaction";
    $str["operate"]    = "operate";

        $str["instruct"] = "instructions";
        $str["logout"]  = "log out";


    $str["deposit"]    = "deposit";
    $str["onthemove"]    = "jetting to";



    $fight = array("stand", "surrender", "run", "fire");    
        

    $maxmsgs = array("Cops made a big %s bust! Prices are outrageous!", "Addicts are buying %s at ridiculous prices!", "The addicts are going nuts for %s!");
    $minmsgs = array("The market is flooded with cheap %s.");


    $str["nospace"]    = "You can't carry %s more drugs.";
    $str["nodrug"]    = "You're shittin' me right? You don't have %s units of %s.";

    $str["morespace"]    = "Now you can carry 10 more units.";

    $str["doctor"]        = "The doctor can fix you up for \$ %s.";
    $str["recovered"]    = "You are healthy.";

    $str["disease"]    = "You caught a venereal disease.";
    $str["mugged"]    = "You got mugged! Your money is gone.";
    $str["ooh"]    = "'Ooh, was it goof for you too?'";
    $str["operate"]    = "fix me up";


    $str["nogunspace"]    = "You can't carry more guns.";
    $str["noguncash"]    = "You can't afford that gun.";
    $str["nomoney"]    = "You don't have enough money for that.";
    $str["sell"]    = "sell";
    $str["buy"]    = "buy";
    $str["available"]    = "available:";
    $str["carried"]        = "carried:";
    $str["to"]    = "to";
    $str["at"]    = "at";
    $str["amount"]    = "amount";
    $str["quantity"]    = "quantity";
    $str["withdraw"]    = "withdraw";
    $str["deposit"]    = "deposit";
    $str["invalid"]    = "Invalid transaction";

    $str["loan"]    = "loan";
    $str["pay"]    = "pay";
    $str["leave"]    = "leave";

    $str["dump"]    = "<b>Warning:</b> %s is not sold here!<br>You're dumping your %s if you sell.";


    $str["hire"]    = "hire";
    $str["hirebitch"]    = "hire bitch to / for:";
    $str["maxbitch"]    = "You can't hire more than 10 bitches to carry drugs.";
    $str["maxloan"]    = "The loan shark only want to loan you \$; %s more.";    

    $str["cash"]    = "cash";
    $str["bank"]    = "bank";
    $str["debt"]    = "debt";
    $str["total"]    = "score";
    $str["name"]    = "name";

    $str["bitches"]    = "bitches";
    $str["life"]    = "health";
    $str["space"]    = "space";
    $str["guns"]    = "guns";

    $str["status"]    = "status";
    $str["goto"]    = "go to";
    $str["instruct"] = "instructions";
    $str["logout"]    = "log out";

    $str["chase"]    = "%s cops are chasing you! What do you do?";
    $str["surrender"] = "surrender";
    $str["fight"]    = "fire";
    $str["run"]     = "run";
    $str["bribe"]    = "bribe";

    $str["nobribe"]    = "You don't have enough money to bribe all cops (\$ 20,000 a cop).";
    $str["bribed"]  = "You've bribed the cops (\$ %s and half of your drugs).";

    $str["youkilledcop"]    = "You killed a cop!";
    $str["allcopskilled"]    = "All cops are dead! You find \$ %s on them.";
    $str["youmissed"]    = "You failed to hit.";
    $str["escaped"]        = "You escaped.";
    $str["cantescape"]    = "You can't escape.";
    $str["copsshoot"]    = "%s cops are shooting...";
    $str["copshoot"]    = "The last cop shoots...";
    $str["bitchkilled"]    = "One of your bitches got killed.";
    $str["yourhit"]        = "You've been hit.";    
    $str["missed"]        = "Miss!";    
    $str["forfeit"]         = "The <a href=\"http://www.usdoj.gov/dea/programs/af.htm\" target=\"_blank\">DEA</a> forfeits $ %s from your bank account.";
    
    $str["continue"]    = "continue";    

    $str["lostdrugs"]    = "They chased you! You lost the %s.";
    $str["foundbody"]    = "You find the dead body of a bitch with %s x %s.";
    $str["dead"]        = "You're <b>dead</b>!";

    $str["invalidname"]    = "Invalid name";
    $str["name"]        = "name";
    $str["hiresnitch"]    = "Hire a bitch to tip off a dealer to the cops.<br>The cops will attack that dealer.<br>Later you will be informed on the encounter.";
    $str["snitchhired"]    = "%s is being tipped of.";
    $str["snitched"]    = "You were tipped off by %s.";
    $str["report"]        = "<b>Snitch report</b>";

    $str["spyreport"]    = "<p><b>Spy report</b></p>Dealer %s is located in %s,<br>has \$ %s in cash, \$ %s in the bank and a debt of \$ %s.<br>%s has %s bitches, %s guns and space left for %s drugs.<br>Health is %s%%.";
    $str["hirespy"]        = "Hire a bitch to find out the status of another dealer. You receive an answer immediately.";

    $str["reloading"]    = "Your guns are being reloaded ...";
    $str["reloaded"]    = "Your guns are reloaded.";

    $str["loanhit1"]    = "The loan shark send some of his men.<br>They break your fingers and tell you to pay off the debt.";
    $str["loanhit2"]    = "The loan shark is serious!<br>You got beaten up by his men.";
    $str["loanhit3"]    = "The loan shark wasted you!";


    $str["d_killed"]    = "Dealer %s is dead.";
    $str["d_hit"]        = "Dealer %s was shot at and got hit.";
    $str["d_escaped"]    = "%s got away.";
    $str["d_arrested"]    = "%s is arrested.";
    $str["d_cop"]        = "A cop was wasted.";
    $str["d_cops"]        = "%s cops were wasted.";
    $str["d_allcops"]    = "All were wasted by %s.";
    $str["d_bitch"]        = "One bitch got killed.";
    $str["d_bitches"]    = "%s bitches got killed.";

    $str["dateformat"]    = "%A, %B %e, %T";
    $str["arrested"]    = "You are arrested.";
    $str["prison"]        = "prison";
    $str["inprison"]    = "You are in prison until %s CET.";
    $str["released"]    = "You are released.";

    $str["op_cantescape"]    = "%s tries to escape but can't get away.";
    $str["op_escaped"]    = "%s has escaped.";
    $str["op_stands"]    = "%s stands there like an idiot.";
    $str["op_shoots"]    = "%s fires...";

    $str["youkilledbitch"]    = "You wasted one of the bitches of %s.";
    $str["youkilledopponent"]    = "You killed %s. You find \$ %s on him.";
    $str["youshotopponent"]       = "You hit %s.";
    $str["opponentdead"]    = "%s is dead.";
    
    $str["bitchgone"]    = "One of your bitches ran away.";
    
    $str["encounter"]    = "You run into %s, what do you do?";
    
    $str["op_status"]    = "Status of %s:<br>bitches: %s<br>weapons: %s<br>health: %s%%";

    $str["qod"]        = "Do you really want to overdose (and maybe become legendary)?";
    $str["yes"]        = "yes";
    $str["od"]        = "overdose";

    $currency = "\$";


}

$guns = array(
    array("name" => "Ruger MK4", "price" => 2500),
    array("name" => "Baretta 8357", "price" => 3500),
    array("name" => "S&amp;W Magnum", "price" => 4500),
    array("name" => "Glock 21", "price" => 6000),
    array("name" => "HK MP5", "price" => 15000));


// ****************************************************************************
// READ DATABASE

if ($player["opponent"]) {
    // prevent deadlock by selecting the opponent as well

    $qry = "begin;\nselect * from dopewars where name in ('$uid','" . $player["opponent"] . "') for update;";
    $result = $db->qry($qry);
    if ($result[0]["name"] == $uid) {
        $player = unserialize(stripslashes($result[0]["player"]));
        $pass = $result[0]["password"];
        if ($result[1]["name"] == $player["opponent"]) {
            $opponent =  unserialize(stripslashes($result[1]["player"]));
        }
    } else if ($result[1]["name"] == $uid) {
        $player = unserialize(stripslashes($result[1]["player"]));
        $pass = $result[1]["password"];
        if ($result[0]["name"] == $player["opponent"]) {
            $opponent =  unserialize(stripslashes($result[0]["player"]));
        }
    }

} else {
    $qry = "begin;\nselect * from dopewars where name = '$uid' for update;";
    $result = $db->qry($qry);
    $player = unserialize(stripslashes($result[0]["player"]));
    $pass = $result[0]["password"];
}

if ($player == '') {
    header("Location: $PHP_SELF");
    exit;
}

$updatedate = 0;
$onthemove = "FALSE";


// ****************************************************************************
// TO ANOTHER LOCATION

if ($l!="" && !$player["prison"] && !$player["fight"]) {

    if ($player["noencounter"] > 5) {
        // at least 5 moves between fights with other players

        $qry = "select * from dopewars where onthemove = TRUE for update;";
        $result = $db->qry("$qry");
    }
    if ($result[0] != array() && $result[0]["name"] != $uid && $player["noencounter"] > 5) {
        // start fight with another player

        $player["opponent"] = $result[0]["name"];
        $player["fight"] = 1;
        $opponent = unserialize(stripslashes($result[0]["player"]));
        $opponent["opponent"] = $uid;
        $opponent["fight"] = 1;

        $player["destination"] = $l;
        $player["travel"]--;

        $player["noencounter"] = 0;
    } else {
        // redirect to new location
        $zinnen =@file("../data/185$language.txt");
        if ($zinnen)
            $zin = htmlentities($zinnen[mt_rand(0, count($zinnen)-1)]);

        $player["destination"] = $l;
        echo "<html><head><title>dopewars</title>
        <link rel=\"stylesheet\" href=\"default.css\" type=\"text/css\">
        <meta http-equiv=\"refresh\" content=\"1; URL=$PHP_SELF\">
        </head><body>
        <h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>
        <div style=\"width:400px; text-align:left;margin-top:30px;margin-left:auto;margin-right:auto\"><p>$zin</p>";
        echo "</div>";

        banner($l, $player["location"]);

        echo "</body></html>";
        $player["travel"]--;
        $player["noencounter"]++;
        if ($player["noencounter"] > 5) {
            $onthemove = "TRUE";
        }
        save_exit();
    }
}


echo "<html><head><title>dopewars</title><link rel=\"stylesheet\" href=\"default.css\" type=\"text/css\">";
echo "</head><body>";

// ****************************************************************************
// PRISON

if ($player["prison"]) {
    echo "<h3>" . $str["prison"] . "</h3>";
    echo "<div style=\"height:100px;margin-top:30px;\"></div>";
    printmenu(0);

    if ($player["prison"] > time()) {
        if ($language == "NL") {
            setlocale (LC_TIME, "nl_NL");
        } else {
            setlocale (LC_TIME, "en_EN");
        }
        printf("<p>" . $str["inprison"] . "</p>", strftime ($str["dateformat"], $player["prison"]) );
                echo "<form><input type=\"hidden\" name=\"logout\" value=\"1\"><input type=\"submit\" value=\"" . $str["logout"]. "\" class=\"button\"></form>";
     } else {
        $player["prison"] = 0;
        $player["travel"] = 0;
        echo $str["released"];
        echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\" class=\"button\"></form>";
    }
    echo "</body></html>";
    save_exit();
}


// ****************************************************************************
// BETWEEN TWO LOCATIONS
    
if ($player["destination"] !="" && $player["location"] != $player["destination"] && $player["travel"]) {

    
    if ($player["fight"]) {

        if ($player["opponent"]) {

            // ****************************************************************************
            // FIGHT ANOTHER PLAYER

            $player["noencounter"] = 0;

            if ($opponent == "") {
                $qry = "select * from dopewars where name = '" . $player["opponent"] . "' for update;";
                $result = $db->qry("$qry");
                if ($result[0] != array()) {
                    $opponent = unserialize(stripslashes($result[0]["player"]));
                }
            }

            if ($opponent == "" ) {
                $player["travel"] = 0;
                $player["fight"] = 0;
                $player["reloading"] = 0;
            } else {
                 echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3><div style=\"height:100px;margin-top:30px;\">";
                
                while (list($key, $event) = each ($player["fighthistory"])) {
                    printf ($str[$event] . "<br>", htmlentities($opponent["name"]));
                }
    
                check_life();

                if ($opponent["opponent"]) {
    
                    if ($action == "fight" && !$player["reloading"]) {
                        $opponent["fighthistory"][] = "op_shoots";
                        $damage = 0;
                        while (list($gun, $num) = each($player["guns"])) {
                            $damage += mt_rand(1, sqrt($guns[$gun]["price"]) * $num / 10);
                        }
                        if ($opponent["total"] > 200000000) {
                            // damage amplification 
                            $damage *= 1 + ($opponent["total"] - 200000000)/70000000;
                        }
                        if ($damage >=  5 - $opponent["bitches"]/2) {
        
                            if (mt_rand(0,$opponent["bitches"])) {
                                $opponent = lose_bitch($opponent);
                                printf ($str["youkilledbitch"]. "<br>", htmlentities($opponent["name"]));
                                $opponent["fighthistory"][] = "bitchkilled";
                                $player["reloading"] = 1;
                            } else {
                                if ($opponent["total"] > 1000000) {
                                    $damage *= .7;
                                    $damage = min(97,$damage);
                                    $opponent["life"] -= round($damage);
                                } else {
                                    // damage reduction 
                                    $opponent["life"] -= round($damage * .4 + 0.3 * max(0,$opponent["total"])/1000000 );
                                }
                                $opponent["fighthistory"][] = "yourhit";
        
                                if ($opponent["life"] <= 0) {
                                    $opponent["life"] = 0;
                                    $amount =  min(mt_rand(7500, 9000), $opponent["cash"]);
                                    printf ($str["youkilledopponent"]."<br>", htmlentities($opponent["name"]), $amount);
                                    $player["cash"] += $amount;
                                    $player["fight"] = 0;
                                } else {
                                    printf ($str["youshotopponent"]."<br>", htmlentities($opponent["name"]));
                                    $player["reloading"] = 1;
                                }
                            }
                        } else {
                            echo $str["youmissed"]."<br>";
                            $opponent["fighthistory"][] = "missed";
                            $player["reloading"] = 1;
                        }
                    }
    
                    if ($action == "run") {
                        if ( (mt_rand(0,100) > 20 + $opponent["bitches"]* 7) || end($player["fighthistory"]) ==  "op_cantescape") {
                            echo $str["escaped"];
                            $opponent["fighthistory"][] = "op_escaped";
                            $player["fight"] = 0;
                        } else {
                            echo $str["cantescape"];
                            $opponent["fighthistory"][] = "op_cantescape";
                        }
                    }
                    if ($action == "stand") {
                        echo $str["youstand"];
                        $opponent["fighthistory"][] = "op_stands";
                    }
                    if ($action == "") {
                        if ($reload) {
                            echo $str["reloaded"] . "<br>";
                            $player["reloading"] = 0;
                        } else {
                            printf($str["encounter"] . "<br>", htmlentities($opponent["name"]));
                        }
                    }
                } else {
                    $player["reloading"] = 0;
                    $player["fight"] = 0;
                }
                
                if ($opponent["life"] <= 0) {
                    printf ($str["opponentdead"], $player["opponent"]);
                    $player["fight"] = 0;
                    $player["reloading"] = 0;
                }

                $player["fighthistory"] = array();
    
                save_player($player["opponent"], $opponent, 1);

                if ($player["reloading"]) {
                    echo  "<meta http-equiv=\"refresh\" content=\"3; URL=$PHP_SELF?reload=1\">";
                    echo $str["reloading"];
                    echo "</body></html>";
                    save_exit();
                }
                
                if (!$player["fight"]) {
                    $player["travel"] = 0; // to new location
                    $player["opponent"] = "";
                    echo "</div>";
                    printmenu(0);
                    echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\" class=\"button\"></form>";
                    echo "</body></html>";
                    save_exit();
                }
            }

        } else if ($action) {

            // ****************************************************************************
            // FIGHT THE POLICE
    
            echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
            echo "<div style=\"height:100px;margin-top:30px;\">";
    
            if ($action == "fight") {
                $damage = 0;
                while (list($gun, $num) = each($player["guns"])) {
                    $damage += mt_rand(1, sqrt($guns[$gun]["price"]) * $num / 10);
                }
                if ($damage >=  5 - $player["cops"]/2) {
                    $player["cops"]--;
                    $player["fightreport"]["deadcops"]++;
                    if ($player["cops"] <= 0) {
                        $amount = 0;
                        for ($i = 0; $i < $player["fightreport"]["deadcops"]; $i++) {
                            $amount += mt_rand(500,20000);
                        }
                        $player["cash"] += $amount;
                        printf ($str["allcopskilled"], $amount);
                        $player["fight"] = 0;
                    } else {
                        echo $str["youkilledcop"];
                    }
                } else {
                    echo $str["youmissed"];
                }
                $player["fightreport"]["shots"]++;
                    
            }
            if ($action == "bribe") {
                if ($player["fightreport"]["shots"]==0) {
                    if ($player["cash"] > $player["cops"] * 20000 && $player["fightreport"]["shots"]==0) {
                        printf($str["bribed"],  $player["cops"] * 20000);
                        $player["cash"] -= $player["cops"] * 20000;
                        $player["fight"] = 0;
                        reset ($player["drugs"]);
                        while (list ($key, $val) = each($player["drugs"])) {
                            $foo = round($val/2.0);
                            $player["space"] += $foo;
                            $player["drugs"][$key] -= $foo;
                        }
                    } else {
                        echo $str["nobribe"];
                    }
                }
            }
            if ($action == "run") {
                if (mt_rand(0,100) > 20 + $player["cops"]* 7) {
                    echo $str["escaped"];
                    $player["fight"] = 0;
                } else {
                    echo $str["cantescape"];
                }
            }
            if ($action == "surrender") {
                $player["fight"] = 0;
                $player["fightreport"]["arrested"]=1;
                $prisontime = 1;
                $prisontime += $player["fightreport"]["shots"];
                $prisontime += $player["fightreport"]["deadcops"]*2;
                $prisontime += array_sum($player["drugs"])/10;

                $player["prison"] = ceil(time() / 3600 + $prisontime) * 3600;
                $player["guns"] = array();
                $player["space"] += array_sum($player["drugs"]);
                $player["drugs"] = array();

                report_snitches();
                echo "<br>" . $str["arrested"];

                if ($player["bank"] > 2000000000 && !mt_rand(0,4)) {
                    $foo = mt_rand(15000000, 500000000);
                    $player["bank"] -= $foo;
                    printf("<br>" . $str["forfeit"], $foo);
                }

                                echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\" class=\"button\"></form>";
                                echo "</body></html>";
                                save_exit();
                
            } else if (!$player["fight"]) {
                $foo = $player["currentsnitches"];
                report_snitches();
                $player["travel"] = 0;    // to new location
                if ($player["total"] > -1000000000 && mt_rand(0,2) && $player["cash"]>0 && $foo != array()) {
                    // mugged
                    $player["cash"] = 0;
                    echo "<br>" . $str["mugged"];
                }
                echo "</div>";
                printmenu(0);
                echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\" class=\"button\"></form>";
                echo "</body></html>";
                save_exit();

            } else {
                // police attacks
                if ($player["cops"] > 1) {
                    printf("<br>" . $str["copsshoot"] . "<br>", $player["cops"]);
                } else {
                    echo "<br>" . $str["copshoot"] . "<br>";
                }
                if (mt_rand(0, $player["cops"])) {
                    if (mt_rand(0,$player["bitches"])) {
                        $player = lose_bitch($player);
                        $player["fightreport"]["bitchlost"]++;
                        echo $str["bitchkilled"];
                    } else {
                        $damage = 1;
                        for ($i = 0; $i < $player["cops"]; $i++) {
                            $damage += mt_rand(0, 4);
                        }
                        if ($player["total"] > 200000000) {
                            $damage *= 1 + ($player["total"] - 200000000)/70000000;
                        }
                        $damage = round(min(97,$damage));
                        $player["life"] -= $damage;
                        $player["fightreport"]["life"] = $player["life"];
                        echo $str["yourhit"];
                        check_life();
                    }
                } else {
                    echo $str["missed"];
                }
            }


        } else {
            echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
            echo "<div style=\"height:100px;margin-top:30px;\">";

            printf ("<p>" . $str["chase"] . "</p>", $player["cops"]);

        }
    } else {
    
        if ($player["total"] > 0) {
            $foo = round((20 - log($player["total"]))) ;
        } else {
            $foo = 20;
        }
        $foo = max($foo, 2);

        $threat = 0;
        if ($player["destination"] != 0 && mt_rand(0,1)) {
            if ($player["debt"] > MAXLOAN && $player["threat"] == 0) {
                $threat = 1;    
            } else if ($player["debt"] > MAXLOAN * 1.2 && $player["threat"] == 1) {
                $threat = 2;
            } else if ($player["debt"] > MAXLOAN * 1.4 && $player["threat"] == 2) {
                $threat = 3;
            }
        }


        if ($player["threat"] < $threat) {
            // loanshark threat
            
            echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
            echo "<div style=\"height:100px;margin-top:30px;\">";
            echo $str["loanhit" . $threat];
            $player["life"] -= pow($threat, 3) * 4;
            $player["threat"] = $threat;
            $player["travel"] = 0;
        
            check_life();

            echo "</div>";
            printmenu(0);
            echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\" class=\"button\"></form>";
            
            echo "</body></html>";
            save_exit();                        
        } else if (mt_rand(0, $foo) == 0 || $player["snitches"]!=array()) {
            // start fight with police

            if ($player["snitches"] != array()) {
                $player["cops"] = 6;
                $player["currentsnitches"] = $player["snitches"];
                $player["snitches"] = array();
            } else {
                $player["currentsnitches"] = array();
                $player["cops"] = mt_rand(2, 12 - min(9, $foo-2));
            }
            
            $player["fight"] = time();
    
            echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
            echo "<div style=\"height:100px;margin-top:30px;\">";

            printf ("<p>" . $str["chase"] . "</p>", $player["cops"]);
        } else {

            $r = mt_rand(0,100);
    
            if ($r < 5) {
                // random events

                echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
                echo "<div style=\"height:100px;margin-top:30px;\">";

                if (array_sum($player["drugs"]) && $r < 2) {
                    // lose drugs in chase
                    reset ($player["drugs"]);
                    while (list($key, $val) = each($player["drugs"])) {
                        if ($val) {
                            $player["space"] += $player["drugs"][$key];
                            $player["drugs"][$key] = 0;
                            $player["drugprices"][$key] = 0;
                            printf($str["lostdrugs"], $drugs[$key]["name"]);
                            break;
                        }
                    }    
                } else if ($player["space"] && $r < 4) {
                    // find drugs
                    $drug = mt_rand(0, count($drugs)-1);
                    $quantity = mt_rand(1, min(10, $player["space"]));
                    $player["space"] -= $quantity;
                    $drugamount = round( $player["drugs"][$drug] * $player["drugprices"][$drug] );
                    $player["drugs"][$drug] += $quantity; 
                    $player["drugprices"][$drug] = round($drugamount/$player["drugs"][$drug]);
                    printf($str["foundbody"], $quantity, $drugs[$drug]["name"]);
                } else {
                    // mugged
                    $player["cash"] = 0;
                    echo $str["mugged"];
                }

                $player["travel"] = 0;
                echo "</div>";
                printmenu(0);
                echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\" class=\"button\"></form>";
                echo "</body></html>";
                save_exit();
            } else if ($r < 10 && $player["bitches"] > 5) {
                $player = lose_bitch($player);
                echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
                echo "<div style=\"height:100px;margin-top:30px;\">";
                echo $str["bitchgone"];
                echo "</div>";
                                printmenu(0);
                                echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\" class=\"button\"></form>";
                                echo "</body></html>";
                $player["travel"] = 0;
                                save_exit();
            } else {
                // to new location
                $player["travel"] = 0;
            }
        }

    }


    if ($player["fight"]) {
        // fight menu

        echo "</div>";
        printmenu(0);
    
        if (array_sum($player["guns"])) {
            echo "<form><input type=\"hidden\" name=\"action\" value=\"fight\"><input type=\"submit\" value=\"" . $str["fight"]. "\" class=\"button\"></form>";
        }
        echo "<form><input type=\"hidden\" name=\"action\" value=\"run\"><input type=\"submit\" value=\"" . $str["run"]. "\" class=\"button\"></form>";
        if (!$player["opponent"]) {
            echo "<form><input type=\"hidden\" name=\"action\" value=\"surrender\"><input type=\"submit\" value=\"" . $str["surrender"]. "\" class=\"button\"></form>";
            if ($player["fightreport"]["shots"]==0) {
                echo "<form><input type=\"hidden\" name=\"action\" value=\"bribe\"><input type=\"submit\" value=\"" . $str["bribe"]. "\" class=\"button\"></form>";
            }
        } else {
            printf("<p>". $str["op_status"] . "</p>", htmlentities($opponent["name"]), $opponent["bitches"], array_sum($opponent["guns"]), $opponent["life"]);
        }
        echo "</body></html>";
        save_exit();
    }
    
}

// ****************************************************************************
// PRINT SNITCH REPORTS

if ($player["destination"] !="" && $player["location"] != $player["destination"]) {
    if ($player["snitchreport"]) {
    
        echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
        echo "<div style=\"height:100px;margin-top:30px;\">";

        while (list($key, $snitchreport) = each($player["snitchreport"])) {
            $snitchreport["player"] = htmlentities($snitchreport["player"]);
            echo "<p>" . $str["report"] . "</p>";

            echo "<p>";
            if ($snitchreport["life"] != "" && $snitchreport["life"] <= 0) {
                printf($str["d_killed"] . "<br>",  $snitchreport["player"]);
            } else {
                if ($snitchreport["life"]) {
                    printf($str["d_hit"] . "<br>",  $snitchreport["player"]);
                }
                if ($snitchreport["arrested"]) {
                    printf($str["d_arrested"] . "<br>",  $snitchreport["player"]);
                } else {
                    printf($str["d_escaped"] . "<br>",  $snitchreport["player"]);
                    if ($snitchreport["bitchlost"] == 1) {
                        printf($str["d_bitch"] . "<br>");
                    } else if ($snitchreport["bitchlost"] > 1) {
                        printf($str["d_bitches"] . "<br>", $snitchreport["bitchlost"]);
                    }
                }
            }
            if ($snitchreport["deadcops"] == 1) {
                echo $str["d_cop"] . "<br>";
            } else if ($snitchreport["deadcops"] == 6) {
                printf($str["d_allcops"] . "<br>", $snitchreport["player"]);
            } else if ($snitchreport["deadcops"]) {
                printf($str["d_cops"] . "<br>", $snitchreport["deadcops"]);
            }
            echo "</p>";
        }
        $player["snitchreport"] = array();

        echo "</div>";
        printmenu(0);
        echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\" class=\"button\"></form>";
        echo "</body></html>";
        
        save_exit();
    }
}


if ($player["destination"] !="" && $player["location"] != $player["destination"]) {
    echo "<h3>" . $places[$player["destination"]] . "</h3>";
} else {
    if ($s) {
        echo "<h3>" . $places[$player["location"]] . ", " . $str["at"] . " " . $special[$player["location"]] . "</h3>";
    } else {
        echo "<h3>" . $places[$player["location"]] . "</h3>";
    }
}

echo "<div style=\"height:100px;margin-top:30px;\">";

check_life();

if ($player["destination"] !="" && $player["location"] != $player["destination"]) {
    

    // ****************************************************************************
    // GENERATE NEW PRICES

    $updatedate = 1;
    
    for ($i = 0; $i < count($drugs); $i++) {
        $prices[$i] = mt_rand($drugs[$i]["min"], $drugs[$i]["max"]);
        $foo = mt_rand(0,30);
        if ($foo == 1) {
            // very high
            $prices[$i] *= 3;
        } else if ($foo == 0) {
            // very low
            $prices[$i] /= 3;
            $prices[$i] = round($prices[$i]);
        }
    }
    
    $drugcnt = mt_rand(count($drugs)/2, count($drugs));
    
    while ($drugcnt < count($prices)) {
        unset($prices[mt_rand(0, count($drugs))]);
    }
    
    $player["location"] = $player["destination"];
    $player["destination"] = "";
    $player["prices"] = $prices;

    reset ($prices);
    while (list($drug, $val) = each($prices)) {
        if ($val < $drugs[$drug]["min"]) {
            if ($drugs[$drug]["minmsg"]) {
                echo $drugs[$drug]["minmsg"] . "<br>\n";
            } else {
                $foo = mt_rand(0, count($minmsgs)-1);
                printf($minmsgs[$foo] . "<br>", $drugs[$drug]["name"]);
            }
        }
        if ($val > $drugs[$drug]["max"]) {
            if ($drugs[$drug]["maxmsg"]) {
                echo $drugs[$drug]["maxmsg"] . "<br>\n";
            } else {
                $foo = mt_rand(0, count($maxmsgs)-1);
                printf($maxmsgs[$foo] . "<br>", $drugs[$drug]["name"]);
            }
        }
    }
    $player["travel"] = 2;

    // interest on debt

    $player["debt"] = ceil ($player["debt"] * 1.05);
}


// ****************************************************************************
// OVERDOSE

if ($action == "od") {
    if ($confirm) {
        $player["life"] = 0;
        check_life();
    }
}

// ****************************************************************************
// BANK
if ($action == "bank" && $player["location"] == 4) {
    if ($deposit) {
        if ($amount > 0 && $amount <= $player["cash"]) {
            $player["cash"] -= $amount;
            $player["bank"] += $amount;
        } else {
            echo "<p>" . $str["invalid"] . "</p>";
        }
    } else {
        if ($amount > 0 && $amount <= $player["bank"]) {
            $player["cash"] += $amount;
            $player["bank"] -= $amount;
        } else {
            echo "<p>" . $str["invalid"] . "</p>";
        }
    }
}

// ****************************************************************************
// DEBT
if ($action == "debt" && $player["location"] == 0) {
    if ($deposit) {
        if ($amount > 0 && $amount <= $player["cash"] && $amount <= $player["debt"]) {
            $player["cash"] -= $amount;
            $player["debt"] -= $amount;
        } else {
            echo "<p>" . $str["invalid"] . "</p>";
        }
    } else {
        if ($amount > 0) {
            if ($amount + $player["debt"] > MAXLOAN) {
                printf("<p>" . $str["maxloan"] . "</p>", MAXLOAN - $player["debt"]);
            } else {
                $player["cash"] += $amount;
                $player["debt"] += $amount;
            }
        } else {
            echo "<p>" . $str["invalid"] . "</p>";
        }
    }
    if ($player["debt"] <= MAXLOAN) {
        $player["threat"] = 0;
    }
}


// ****************************************************************************
// OPERATE
if ($action == "operate" && $player["location"] == 2) {
    $price = (100 - $player["life"]) * 1500 + 1500;
    if ($price <= $player["cash"]) {
        $player["cash"] -= $price;
        $player["life"] = 100;
    } else {
        echo $str["nomoney"] . "<br>";
    }
}


// ****************************************************************************
// BITCHES

if ($action == "hire" && $player["location"] == 1) {
    $price = $bitchactions[$activity]["price"];
    if ($price) {
        if ($player["cash"] >= $price) {
            switch ($activity) {
                case 0:
                    // SEX

                    switch (mt_rand(0,3)) {
                        case 0:
                            $player["cash"] -= $price;
                            $player["life"] -= 10;
                            echo $str["disease"];
                            check_life();
                            break;
                        case 1:
                            $player["cash"] = 0;
                            echo $str["mugged"];
                            break;
                        default:
                            $player["cash"] -= $price;
                            echo $str["ooh"];
                            break;
                    }
                    break;                        
                case 1:
                    // SPY 
                    if (is_numeric($dealer)) {
                        $qry = "select * from dopewars where id = $dealer;";
                        $result = $db->qry($qry);
                        if ($result[0]) {
                            $subject = unserialize(stripslashes($result[0]["player"]));
                            if ($subject["prison"]) {
                                $loc = $str["prison"];
                            } else {
                                $loc = $places[$subject["location"]];
                            }
                            printf($str["spyreport"], 
                                htmlentities($subject["name"]),
                                $loc,
                                $subject["cash"],
                                $subject["bank"],
                                $subject["debt"],
                                htmlentities($subject["name"]),
                                $subject["bitches"],
                                array_sum($subject["guns"]),
                                $subject["space"],
                                $subject["life"]);
                            $player["cash"] -= $price;
                        } else {
                            echo "<p>" . $str["invalidname"] . "</p>";
                        }
                        break;
                    }
                    
                    echo "</div>";
                    printmenu();
                    echo "<form action=\"$PHP_SELF\">";
                    echo "<input type=\"hidden\" name=\"action\" value=\"hire\">";
                    echo "<input type=\"hidden\" name=\"activity\" value=\"1\">";
                    echo "<input type=\"hidden\" name=\"s\" value=\"1\">";
                    echo "<p>" . $str["hirespy"] . "</p><p>" . $str["name"] . ": ";
                    dealer_list();
                    echo "</p><p>";
                    echo "<input type=\"submit\" value=\"" . $str["hire"]  . "\" class=\"button\">";
                    echo "<p><a href=\"$PHP_SELF\">" . $str["leave"] . "</a></p>";
                    
                    echo "</form></body></html>";
                    save_exit();
                    
                case 2:
                    // SNITCH
                    if (is_numeric($dealer)) {
                        $qry = "select * from dopewars where id = $dealer for update;";
                        $result = $db->qry($qry);
                        if ($result[0]) {
                            $subject = unserialize(stripslashes($result[0]["player"]));
                            $subject["snitches"][] = $uid;
                            save_player($result[0]["name"], $subject);
                            printf($str["snitchhired"], htmlentities($subject["name"]));
                            $player["cash"] -= $price;
                        } else {
                            echo "<p>" . $str["invalidname"] . "</p>";
                        }
                        break;
                    }

                    echo "</div>";
                    printmenu();
                    echo "<form action=\"$PHP_SELF\">";
                    echo "<input type=\"hidden\" name=\"action\" value=\"hire\">";
                    echo "<input type=\"hidden\" name=\"activity\" value=\"2\">";
                    echo "<input type=\"hidden\" name=\"s\" value=\"1\">";
                    echo "<p>" . $str["hiresnitch"] . "</p><p>" . $str["name"] . ": ";
                    dealer_list();
                    echo "</p><p>";
                    echo "<input type=\"submit\" value=\"" . $str["hire"]  . "\" class=\"button\">";
                    echo "<p><a href=\"$PHP_SELF\">" . $str["leave"] . "</a></p>";
                
                    echo "</form></body></html>";                    
                    save_exit();
    
                case 3:
                    // DRUGS RUNNER

                    if ($player["bitches"] >= 10) {
                        echo $str["maxbitch"];
                    }else {
                        $player["cash"] -= $price;
                        $player["bitches"] ++;
                        $player["space"] +=10;
                        echo $str["morespace"];
                    }
                    break;
            }
        } else {
            echo $str["nomoney"];
        }

    }

}

if (($action == "buy" || $action == "sell") && $quantity != "") {
    // ****************************************************************************
    // BUY / SELL

    if ($drug != "") {
        $realprice = $quantity * $player["prices"][$drug];
        
        if (($player["prices"][$drug] || $action!="buy") && $quantity > 0) {
            if ($action == "buy") {
                // buy drugs
                if ($realprice > $player["cash"]) {
                    $maxquantity = floor ($player["cash"] / $player["prices"][$drug]);
                    echo $str["nomoney"] . "<br>";
                } else if ($quantity > $player["space"]) {
                    printf($str["nospace"], $quantity) . "<br>";
                } else {
                    $player["cash"] -= $realprice;
                    $player["held"] += $quantity;
                    $player["space"] -= $quantity;
        
                    $drugamount = round( $player["drugs"][$drug] * $player["drugprices"][$drug] );
                    $drugamount += $realprice;
        
                    $player["drugs"][$drug] += $quantity;
                    $player["drugprices"][$drug] = round($drugamount/$player["drugs"][$drug]);
    
                }
            } else {
                // sell drugs
                if ($quantity > $player["drugs"][$drug]) {
                    printf($str["nodrug"], $quantity, $drugs[$drug]["name"]) . "<br>";
                } else {
                    $player["cash"] += $realprice;
                    $player["held"] -= $quantity;
                    $player["space"] += $quantity;
                    $player["drugs"][$drug] -= $quantity;
                }
            }            
        }
        $drug = "";

    } else if (($gun != "") && $quantity > 0 && $player["location"] == 5) {

        $realprice = $quantity * $guns[$gun]["price"];
        reset ($player["guns"]);
        $totalguns = array_sum($player["guns"]);
        
        if ($action == "buy") {
            // buy guns
            if ($realprice > $player["cash"]) {
                echo $str["noguncash"] . "<br>";
            } else if ($quantity > $player["bitches"] + 2 - $totalguns) {
                echo $str["nogunspace"] . "<br>";
            } else {
                $player["cash"] -= $realprice;
                $player["guns"][$gun] += $quantity;
            }
        } else {
            // sell guns
            if ($quantity > $player["guns"][$gun]) {
                printf($str["nodrug"], $quantity, $drugs[$drug]["name"]) . "<br>";
            } else {
                $player["cash"] += $realprice;
                $player["guns"][$gun] -= $quantity;
            }
        }            
    }
}

echo "</div>";

printmenu();

if ($s) {
    
    switch ($player["location"]) {

        case 0:
            // ****************************************************************************
            // LOANSHARK

            echo "<form action=\"$PHP_SELF\">";
            echo "<input type=\"hidden\" name=\"action\" value=\"debt\">";
            echo "<p>" . $str["amount"] . ": $currency ";
            echo "<input name=\"amount\" type=\"text\" value=\"0\" class=\"button\" size=\"8\"></p><p>";
            echo "<input type=\"submit\" name=\"withdraw\" value=\"" . $str["loan"] . "\" class=\"button\"> ";
            echo "<input type=\"submit\" name=\"deposit\"  value=\"" . $str["pay"]  . "\" class=\"button\">";
            echo "<input name=\"s\" type=\"hidden\" value=\"1\">";
        
            echo "</form>";    
            break;
        
        case 1:
            // ****************************************************************************
            // BITCHES


            echo "<form action=\"$PHP_SELF\" name=\"hire\"><input type=\"hidden\" name=\"action\" value=\"hire\">";
            echo $str["hirebitch"];
            echo "<br><br><select name=\"activity\" size=\"4\" style=\"width:180px;\">";
            reset ($bitchactions);
            while (list($key, $val) = each($bitchactions)) {
                echo "<option value=\"$key\">" . $val["name"] . " - $currency " . $val["price"] . "</option>";
            }
            
            echo "</select><br><br>";
            echo "<input name=\"s\" type=\"hidden\" value=\"1\">";
            echo "<input type=\"submit\" value=\"". $str["hire"] . "\" class=\"button\"></form></td>";
            
            break;
            
        case 2:
            // ****************************************************************************
            // HOSPITAL

            if ($player["life"] < 100) {
                $price = (100 - $player["life"]) * 1500 + 1500;
                printf ("<p>" . $str["doctor"] . "</p>", $price);

                echo "<form action=\"$PHP_SELF\">";
                echo "<input type=\"hidden\" name=\"action\" value=\"operate\">";
                echo "<input type=\"submit\" value=\"" . $str["operate"] . "\" class=\"button\"> ";
                echo "<input name=\"s\" type=\"hidden\" value=\"1\">";
                echo "</form>";    
            } else {
                echo "<p>" . $str["recovered"] . "</p>";

            }
            break;

        case 4:
            // ****************************************************************************
            // BANK

            echo "<form action=\"$PHP_SELF\">";
            echo "<input type=\"hidden\" name=\"action\" value=\"bank\">";
            echo "<p>" . $str["amount"] . ": $currency ";
            echo "<input name=\"amount\" type=\"text\" value=\"0\" class=\"button\" size=\"8\"></p><p>";
            echo "<input type=\"submit\" name=\"withdraw\" value=\"" . $str["withdraw"] . "\" class=\"button\"> ";
            echo "<input type=\"submit\" name=\"deposit\"  value=\"" . $str["deposit"]  . "\" class=\"button\">";
            echo "<input name=\"s\" type=\"hidden\" value=\"1\">";
        
            echo "</form>";    
            break;
        case 5:
            // ****************************************************************************
            // GUNSHOP
            
            echo "<table><tr><td width=\"50%\"></td>";
            
            echo "<td><form action=\"$PHP_SELF\" name=\"buy\"><input type=\"hidden\" name=\"action\" value=\"buy\">";
            echo $str["available"];
            echo "<br><br><select name=\"gun\" size=\"5\" style=\"width:160px;\">";
            reset ($guns);
            while (list($gun, $val) = each($guns)) {
                echo "<option value=\"$gun\">" . $val["name"] . " - $currency " . $val["price"] . "</option>";
            }
            
            echo "</select><br><br>";
            echo "<input name=\"quantity\" type=\"hidden\" value=\"1\">";
            echo "<input name=\"s\" type=\"hidden\" value=\"1\">";
            echo "<input type=\"submit\" value=\"". $str["buy"] . " &gt;\" class=\"button\"></form></td>";
            
            
            echo "<td><form action=\"$PHP_SELF\" name=\"sell\"><input type=\"hidden\" name=\"action\" value=\"sell\">";
            echo $str["carried"];
            echo "<br><br><select name=\"gun\" size=\"5\" style=\"width:160px;\">";
            reset ($player["guns"]);
            while (list($gun, $val) = each($player["guns"])) {
                $gunname = $guns[$gun]["name"];
                if ($val) {
                    echo "<option value=\"$gun\">$gunname - $val</option>";
                }
            }
            if ($player["guns"] == array()) {
                echo "<option></option>";
            }
            
            echo "</select><br><br>";
            echo "<input name=\"quantity\" type=\"hidden\" value=\"1\">";
            echo "<input name=\"s\" type=\"hidden\" value=\"1\">";
            echo "<input type=\"submit\" value=\"&lt; ". $str["sell"] . "\" class=\"button\"></form></td>";
            
            echo "<td width=\"50%\"></td></table>";
            break;
    }

    
    echo "<p><a href=\"$PHP_SELF\">" . $str["leave"] . "</a></p>";

} else {

    // ****************************************************************************
    // OVERDOSE
    if ($action == "od") {

        echo "<p>" . $str["qod"] . "</p>";
        echo "<form action=\"$PHP_SELF\">";
        echo "<input type=\"hidden\" name=\"action\" value=\"od\">";
        echo "<input type=\"submit\" name=\"confirm\" value=\"" . $str["yes"]  . "\" class=\"button\">";
        echo "<p><a href=\"$PHP_SELF\">" . $str["leave"] . "</a></p>";

    // ****************************************************************************
    // DEALING DRUNGS
    
    } else if ( ($action == "buy" || $action == "sell") && $drug !="") {
        echo "<form action=\"$PHP_SELF\">";
        echo "<input type=\"hidden\" name=\"action\" value=\"$action\">";
        echo "<input type=\"hidden\" name=\"drug\" value=\"$drug\">";
        $drugname = $drugs[$drug]["name"];
        $price = $player["prices"][$drug];
        if ($action == "sell") {
            $max = $player["drugs"][$drug];
        } else {
            $max = min(floor ($player["cash"] / $price), $player["space"]);
        }
        if ($price == "") {
            $price = 0;
            printf("<p>" . $str["dump"] . "</p>", $drugname, $drugname);
        }
        echo "<p>$drugname @ $currency $price</p>";
        echo "<p>" . $str["quantity"] . ": ";
        echo "<input name=\"quantity\" type=\"text\" value=\"$max\" class=\"button\" size=\"3\"><br>";
        echo "(maximum: $max)</p>";
        if ($action == "buy") {
            echo "<input type=\"submit\" value=\"" . $str["buy"] . "\" class=\"button\">";
        } else {
            echo "<input type=\"submit\" value=\"" . $str["sell"] . "\" class=\"button\">";
        }
    
        echo "</form>";    
    } else {
    
        echo "<table><tr><td width=\"50%\"></td>";
        
        echo "<td><form action=\"$PHP_SELF\" name=\"buy\"><input type=\"hidden\" name=\"action\" value=\"buy\">";
        echo $str["available"];
        echo "<br><br><select name=\"drug\" size=\"11\" style=\"width:160px;\">";
        reset ($player["prices"]);
        while (list($drug, $val) = each($player["prices"])) {
            $drugname = $drugs[$drug]["name"];
            echo "<option value=\"$drug\">$drugname - $currency $val</option>";
        }
        
        echo "</select><br><br>";
        echo "<input type=\"submit\" value=\"". $str["buy"] . " &gt;\" class=\"button\"></form></td>";
        
        
        echo "<td><form action=\"$PHP_SELF\" name=\"sell\"><input type=\"hidden\" name=\"action\" value=\"sell\">";
        echo $str["carried"];
        echo "<br><br><select name=\"drug\" size=\"11\" style=\"width:160px;\">";
        reset ($player["drugs"]);
        while (list($drug, $val) = each($player["drugs"])) {
            $drugname = $drugs[$drug]["name"];
            $price = $player["drugprices"][$drug];
            if ($val) {
                echo "<option value=\"$drug\">$drugname - $val @ $currency $price</option>";
            }
        }
        if ($player["drugs"] == array()) {
            echo "<option></option>";
        }
        
        echo "</select><br><br>";
        echo "<input type=\"submit\" value=\"&lt; ". $str["sell"] . "\" class=\"button\"></form></td>";
        
        echo "<td width=\"50%\"></td></table>";
    
        if ($special[$player["location"]]) {
            echo "<p><a href=\"$PHP_SELF?s=1\">" . $str["to"] . " ". $special[$player["location"]] . "</a></p>";
        }
    }
}

echo "</body></html>";

$player["fight"] = 0;
$player["reloading"] = 0;
$player["opponent"] = "";

save_exit();

?>