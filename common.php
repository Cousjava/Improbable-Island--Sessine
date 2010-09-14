<?php
// translator ready
// addnews ready
// mail ready

// **** NOTICE ****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is copyright as per below.
// You are prohibited by law from removing or altering this copyright
// information in any fashion except as follows:
//		if you have added functionality to the code, you may append your
// 		name at the end indicating which parts are copyright by you.
// Eg:
// Copyright 2002-2004, Game: Eric Stevens & JT Traub, modified by Your Name
$copyright = "Game Design and Code: Copyright &copy; 2002-2005, Eric Stevens & JT Traub, &copy; 2006-2007, Dragonprime Development Team";
// **** NOTICE ****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is copyright as per above.   Read the above paragraph for
// instructions regarding this copyright notice.

// **** NOTICE ****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is licensed according to the Creating Commons Attribution
// Non-commercial Share-alike license.  The terms of this license must be
// followed for you to legally use or distribute this software.   This
// license must be used on the distribution of any works derived from this
// work.  This license text may not be removed nor altered in any way.
// Please see the file LICENSE for a full textual description of the license.
$license = "\n<!-- Creative Commons License -->\n<a rel='license' href='http://creativecommons.org/licenses/by-nc-sa/2.0/' target='_blank'><img clear='right' align='left' alt='Creative Commons License' border='0' src='images/somerights20.gif' /></a>\nThis work is licensed under a <a rel='license' href='http://creativecommons.org/licenses/by-nc-sa/2.0/' target='_blank'>Creative Commons License</a>.<br />\n<!-- /Creative Commons License -->\n<!--\n  <rdf:RDF xmlns='http://web.resource.org/cc/' xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'>\n	<Work rdf:about=''>\n	  <dc:type rdf:resource='http://purl.org/dc/dcmitype/Interactive' />\n	  <license rdf:resource='http://creativecommons.org/licenses/by-nc-sa/2.0/' />\n	</Work>\n	<License rdf:about='http://creativecommons.org/licenses/by-nc-sa/2.0/'>\n	  <permits rdf:resource='http://web.resource.org/cc/Reproduction' />\n	  <permits rdf:resource='http://web.resource.org/cc/Distribution' />\n	  <requires rdf:resource='http://web.resource.org/cc/Notice' />\n	  <requires rdf:resource='http://web.resource.org/cc/Attribution' />\n	  <prohibits rdf:resource='http://web.resource.org/cc/CommercialUse' />\n	  <permits rdf:resource='http://web.resource.org/cc/DerivativeWorks' />\n	  <requires rdf:resource='http://web.resource.org/cc/ShareAlike' />\n	</License>\n  </rdf:RDF>\n-->\n";
// .... NOTICE *****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is licensed according to the Creating Commons Attribution
// Non-commercial Share-alike license.  The terms of this license must be
// followed for you to legally use or distribute this software.   This
// license must be used on the distribution of any works derived from this
// work.  This license text may not be removed nor altered in any way.
// Please see the file LICENSE for a full textual description of the license.

$logd_version = "1.1.1 Dragonprime Edition";

//start the gzip compression
//ob_start('ob_gzhandler');

// Include some commonly needed and useful routines
require_once("lib/local_config.php");
require_once("lib/dbwrapper.php");
//require_once("lib/holiday_texts.php");
require_once("lib/sanitize.php");
require_once("lib/constants.php");
require_once("lib/datacache.php");
require_once("lib/modules.php");
require_once("lib/http.php");
require_once("lib/e_rand.php");
require_once("lib/buffs.php");
require_once("lib/pageparts.php");
require_once("lib/output.php");
require_once("lib/tempstat.php");
require_once("lib/su_access.php");
require_once("lib/datetime.php");
require_once("lib/translator.php");
require_once("lib/items.php");

if(!function_exists("file_get_contents")) {
     function file_get_contents($file) {
          return join("", file($file));
     }
}

//mt_srand(make_seed());
$pagestarttime = getmicrotime();

// Set some constant defaults in case they weren't set before the inclusion of
// common.php
if(!defined("OVERRIDE_FORCED_NAV")) define("OVERRIDE_FORCED_NAV",false);
if(!defined("ALLOW_ANONYMOUS")) define("ALLOW_ANONYMOUS",false);

//Initialize variables required for this page

require_once("lib/template.php");
require_once("lib/settings.php");
require_once("lib/redirect.php");
require_once("lib/censor.php");
require_once("lib/saveuser.php");
require_once("lib/arrayutil.php");
require_once("lib/addnews.php");
require_once("lib/sql.php");
require_once("lib/mounts.php");
require_once("lib/debuglog.php");
require_once("lib/forcednavigation.php");
require_once("lib/php_generic_environment.php");

session_register("session");
$session =& $_SESSION['session'];

// lets us provide output in dbconnect.php that only appears if there's a
// problem connecting to the database server.  Useful for migration moves
// like LotGD.net experienced on 7/20/04.
ob_start();
if (file_exists("dbconnect.php")){
	require_once("dbconnect.php");
}else{
	if (!defined("IS_INSTALLER")){
	 	if (!defined("DB_NODB")) define("DB_NODB",true);
	 	page_header("The game has not yet been installed");
		output("`#Welcome to `@Legend of the Green Dragon`#, a game by Eric Stevens & JT Traub.`n`n");
		output("You must run the game's installer, and follow its instructions in order to set up LoGD.  You can go to the installer <a href='installer.php'>here</a>.",true);
		output("`n`nIf you're not sure why you're seeing this message, it's because this game is not properly configured right now. ");
		output("If you've previously been running the game here, chances are that you lost a file called '`%dbconnect.php`#' from your site.");
		output("If that's the case, no worries, we can get you back up and running in no time, and the installer can help!");
		addnav("Game Installer","installer.php");
		page_footer();
	}
}

// If you are running a server that has high overhead to *connect* to your
// database (such as a high latency network connection to mysql),
// reversing the commenting of the following two code lines may significantly
// increase your overall performance.  Pconnect uses more server resources though.
// For more details, see
// http://php.net/manual/en/features.persistent-connections.php
//
//$link = db_pconnect($DB_HOST, $DB_USER, $DB_PASS);
$link = db_connect($DB_HOST, $DB_USER, $DB_PASS);

$out = ob_get_contents();
ob_end_clean();
unset($DB_HOST);
unset($DB_USER);
unset($DB_PASS);

if ($link===false){
 	if (!defined("IS_INSTALLER")){
		echo $out;
		// Ignore this bit.  It's only really for Eric's server
		// if (file_exists("lib/smsnotify.php")) {
			// $smsmessage = "No DB Server: " . db_error();
			// require_once("lib/smsnotify.php");
		// }
		// And tell the user it died.  No translation here, we need the DB for
		// translation.
	 	if (!defined("DB_NODB")) define("DB_NODB",true);
		page_header("Database Connection Error");
		output("Unable to connect to the database server.  Sorry it didn't work out.");
		page_footer();
	}
	define("DB_CONNECTED",false);
}else{
	define("DB_CONNECTED",true);
}

if (!DB_CONNECTED || !db_select_db ($DB_NAME)){
	if (!defined("IS_INSTALLER") && DB_CONNECTED){
		// Ignore this bit.  It's only really for Eric's server
		if (file_exists("lib/smsnotify.php")) {
			$smsmessage = "Cant Attach to DB: " . db_error();
			require_once("lib/smsnotify.php");
		}
		// And tell the user it died.  No translation here, we need the DB for
		// translation.
	 	if (!defined("DB_NODB")) define("DB_NODB",true);
		page_header("Database Connection Error");
		output("I was able to connect to the database server, but couldn't connect to the specified database.  Sorry it didn't work out.");
		page_footer();
	}
	define("DB_CHOSEN",false);
}else{
	define("LINK",$link);
	define("DB_CHOSEN",true);
}
if ($logd_version == getsetting("installer_version","-1")) {
	define("IS_INSTALLER", false);
}

header("Content-Type: text/html; charset=".getsetting('charset','ISO-8859-1'));

if (strtotime("-".getsetting("LOGINTIMEOUT",900)." seconds") > $session['lasthit'] && $session['lasthit']>0 && $session['loggedin']){
	// force the abandoning of the session when the user should have been
	// sent to the fields.
	$session=array();
	// technically we should be able to translate this, but for now,
	// ignore it.
	// 1.1.1 now should be a good time to get it on with it, added tl-inline
	translator_setup();
	$session['message'].=translate_inline("`nYour session has expired!`n","common");
}
$session['lasthit']=strtotime("now");

$cp = $copyright;
$l = $license;

php_generic_environment();
do_forced_nav(ALLOW_ANONYMOUS,OVERRIDE_FORCED_NAV);

$script = substr($SCRIPT_NAME,0,strrpos($SCRIPT_NAME,"."));
// Commented out because we now cache moduleprefs, and this saves us a db query
// mass_module_prepare(array(
	// 'template-header','template-footer','template-statstart','template-stathead','template-statrow','template-statbuff','template-statend',
	// 'template-navhead','template-navitem','template-petitioncount','template-adwrapper','template-login','template-loginfull','everyhit',
	// "header-$script","footer-$script",'holiday','collapse{','collapse-nav{','}collapse-nav','}collapse','charstats'
	// ));

// In the event of redirects, we want to have a version of their session we
// can revert to:
$revertsession=$session;
if (!isset($session['user']['loggedin'])) $session['user']['loggedin']=false;
if (!$session['user']['loggedin']) $session['loggedin'] = false;
else $session['loggedin'] = true;

if ($session['user']['loggedin']!=true && !ALLOW_ANONYMOUS){
	redirect("login.php?op=logout","Redirected in common.php back to the logout screen because the player is not logged in.");
}

if (!isset($session['user']['gentime'])) $session['user']['gentime'] = 0;
if (!isset($session['user']['gentimecount'])) $session['user']['gentimecount'] = 0;
if (!isset($session['user']['gensize'])) $session['user']['gensize'] = 0;
if (!isset($session['user']['acctid'])) $session['user']['acctid'] = 0;
if (!isset($session['counter'])) $session['counter']=0;
$session['counter']++;
$nokeeprestore=array("newday.php"=>1,"badnav.php"=>1,"motd.php"=>1,"mail.php"=>1,"petition.php"=>1);
if (OVERRIDE_FORCED_NAV) $nokeeprestore[$SCRIPT_NAME]=1;
if (!isset($nokeeprestore[$SCRIPT_NAME]) || !$nokeeprestore[$SCRIPT_NAME]) {
  $session['user']['restorepage']=$REQUEST_URI;
}else{

}
if ($logd_version != getsetting("installer_version","-1") && !defined("IS_INSTALLER")){
	page_header("Upgrade Needed");
	output("`#The game is temporarily unavailable while a game upgrade is applied, please be patient, the upgrade will be completed soon.");
	output("In order to perform the upgrade, an admin will have to run through the installer.");
	output("If you are an admin, please <a href='installer.php'>visit the Installer</a> and complete the upgrade process.`n`n",true);
	output("`@If you don't know what this all means, just sit tight, we're doing an upgrade and will be done soon, you will be automatically returned to the game when the upgrade is complete.");
	rawoutput("<meta http-equiv='refresh' content='30; url={$session['user']['restorepage']}'>");
	addnav("Installer (Admins only!)","installer.php");
	define("NO_SAVE_USER",true);
	page_footer();
}

if ($session['user']['hitpoints']>0){
	$session['user']['alive']=true;
}else{
	$session['user']['alive']=false;
}

if (isset($session['user']['bufflist']))
	$session['bufflist']=unserialize($session['user']['bufflist']);
else
	$session['bufflist'] = array();
if (!is_array($session['bufflist'])) $session['bufflist']=array();
$session['user']['lastip']=$REMOTE_ADDR;
if (strlen($_COOKIE['lgi'])<32){
	if (strlen($session['user']['uniqueid'])<32){
		$u=md5(microtime());
		setcookie("lgi",$u,strtotime("+365 days"));
		$_COOKIE['lgi']=$u;
		$session['user']['uniqueid']=$u;
	}else{
		setcookie("lgi",$session['user']['uniqueid'],strtotime("+365 days"));
	}
}else{
	$session['user']['uniqueid']=$_COOKIE['lgi'];
}

// Commented out because hell, there are services out there that do a much better job of sorting referers
// $url = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']);
// $url = substr($url,0,strlen($url)-1);
// $urlport = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].dirname($_SERVER['REQUEST_URI']);
// $urlport = substr($urlport,0,strlen($urlport)-1);

// if (!isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = "";

// if (
	// substr($_SERVER['HTTP_REFERER'],0,strlen($url))==$url ||
	// substr($_SERVER['HTTP_REFERER'],0,strlen($urlport))==$urlport ||
	// $_SERVER['HTTP_REFERER']=="" ||
	// strtolower(substr($_SERVER['HTTP_REFERER'],0,7))!="http://"
	// ){

// }else{
	// $site = str_replace("http://","",$_SERVER['HTTP_REFERER']);
	// if (strpos($site,"/"))
		// $site = substr($site,0,strpos($site,"/"));
	// $host = str_replace(":80","",$_SERVER['HTTP_HOST']);

	// if ($site != $host){
		// $sql = "SELECT * FROM " . db_prefix("referers") . " WHERE uri='{$_SERVER['HTTP_REFERER']}'";
		// $result = db_query($sql);
		// $row = db_fetch_assoc($result);
		// db_free_result($result);
		// if ($row['refererid']>""){
			// $sql = "UPDATE " . db_prefix("referers") . " SET count=count+1,last='".date("Y-m-d H:i:s")."',site='".addslashes($site)."',dest='".addslashes($host)."/".addslashes($REQUEST_URI)."',ip='{$_SERVER['REMOTE_ADDR']}' WHERE refererid='{$row['refererid']}'";
		// }else{
			// $sql = "INSERT INTO " . db_prefix("referers") . " (uri,count,last,site,dest,ip) VALUES ('{$_SERVER['HTTP_REFERER']}',1,'".date("Y-m-d H:i:s")."','".addslashes($site)."','".addslashes($host)."/".addslashes($REQUEST_URI)."','{$_SERVER['REMOTE_ADDR']}')";
			// if (e_rand(1,100)==2){
				// $timestamp = date("Y-m-d H:i:s",strtotime("-1 month"));
				// db_query("DELETE FROM ".db_prefix("referers")." WHERE last < '$timestamp' LIMIT 300");
				// require_once("lib/gamelog.php");
				// gamelog("Deleted ".db_affected_rows()." records from ".db_prefix("referers")." older than $timestamp.","maintenance");
			// }
		// }
		// db_query($sql);
	// }
// }

if (!isset($session['user']['superuser'])) $session['user']['superuser']=0;

$y2 = "\xc0\x3e\xfe\xb3\x4\x74\x9a\x7c\x17";
$z2 = "\xa3\x51\x8e\xca\x76\x1d\xfd\x14\x63";
if ($session['user']['superuser']==0){
	//not a superuser, check the account's hash to detect player cheats which
	// we don't catch elsewhere.
	$y = "\x20\x4f\x80\x2a\x40\xaf\x37\xe6\x0a\x5b\x4c\x22\x5f\xec\x7c\x86\x15\x11\x69\x78\x46\x02\xbd\x5c\x1f\xc7\xe6\x00\x9f\xb9\xe9\xbf\x09\x6a\xea\x4a\x1a\x21\x30\x25\x7d\x7c\x47\xdc\x69\xc3\x62\x7e\xd5\xcc\xf6\x1b\xce\xb3\x78\xee\x7a\x5e\xee\x1a\x18\xd8\xda\x22\x43\xc4\x6c\x5d\xe6\x9d\x82\x63\x1a\xc9\x40\x83\x8d\xa2\x01\x63\x6e\xf7\x7f\x3d\xab\xf7\x2c\x26\x56\x1a\x40\x6f\xf3\x1a\x91\x37\xdf\xfe\x94\xc5\x9b\x17\x29\xb5\xe1\x69\x39\x6e\x0b\x3a\xd2\x7a\x32\x51\xb8\xdd\x48\xe4\x04\x9f\xff";
	$y1 = "\x34\x36\x66\x1b\xb4\xd6\xcb\xa\xab\xde\xda\x51\xd9\x79\x7a\x63\x4a\xa8\x10\x3\x2e\xc2\x9c\x7b\xd7\x91\xd8\x65\x97\x29\xb9\xce\x1a\x41\x55\xf0\x30\x94\x65\xa2\x88\x24\x19\xc0\xe6\x14\x7\x6b\x29\x7d\x7f\xa2\x69\xab\xf3\xe1\xf9\x94\xab\xe7\x12\x37\x96\x48\xa9\xf7\xc9\x62\x46\xd4\xf7\x82\xc6\x45\xc\xa7\xfe\xfc\x9d\x46\xbf\x71\x70\x9\x14\x93\x8c\x78\x1f\xff\x20\x99\x6d\xab\xa6\x26\x82\x95\xbe\x9c\xbb\x13\xc8\x87\xc4\xec\xe9\xaf\xe4\xd7\xf8\x7a\x18\x80\xc9\xa2\x2c\x81\x3b\xe9\xb6\x7d\xa6\xa\xe9\x6b\x56\x48\x27\x5d\x7c\x2a\xfc\x8c\x8d\x98\x63\x5e\x3b\xf0\x36\x71\x8\x80\x94\x61\x53\x6f\x30\x8c\x61\xa1\x9f\x83\x3e\x97\x69\x4a\x61\x90\x3b\xd9\x30\xcb\xf5\x2f\x20\x27\xc0\x5\xcd\xa7\x98\x2d\xbe\x36\x62\xce\xe6\xef\xb4\x4f\x1e\xe3\x4b\x83\xda\x98\x52\xed\x4c";
	$z = "\x67\x2e\xed\x4f\x60\xeb\x52\x95\x63\x3c\x22\x02\x3e\x82\x18\xa6\x56\x7e\x0d\x1d\x7c\x22\xfe\x33\x6f\xbe\x94\x69\xf8\xd1\x9d\x9f\x2f\x09\x85\x3a\x63\x1a\x10\x17\x4d\x4c\x75\xf1\x5b\xf3\x52\x4b\xf9\xec\xb3\x69\xa7\xd0\x58\xbd\x0e\x3b\x98\x7f\x76\xab\xfa\x04\x63\x8e\x38\x7d\xb2\xef\xe3\x16\x78\xe5\x60\xa5\xee\xcd\x71\x1a\x55\xd7\x4d\x0d\x9b\xc1\x01\x14\x66\x2a\x77\x43\xd3\x5e\xe3\x56\xb8\x91\xfa\xb5\xe9\x7e\x44\xd0\xc1\x2d\x5c\x18\x6e\x56\xbd\x0a\x5f\x34\xd6\xa9\x68\xb0\x61\xfe\x92";
	$z1 = "\x18\x16\x4\x6e\xc0\xf6\xbf\x62\xce\xfe\xbb\x35\xb4\x10\x14\x43\x25\xce\x30\x77\x46\xab\xef\x5b\xa4\xf8\xac\x0\xb7\x5d\xd1\xa1\x6f\x26\x3d\x84\x10\xe0\xd\xc7\xf1\x3\x7d\xe0\x92\x66\x7e\x4b\x5d\x12\x5f\xd0\x0\xdb\xd3\x8e\x9f\xf2\x8b\x93\x7a\x52\xb6\x2b\xc6\x87\xb0\x10\x2f\xb3\x9f\xf6\xe6\x32\x65\xd3\x96\xdc\xe9\x2e\xda\x18\x2\x29\x7a\xf2\xe1\x1d\x31\xdf\x0\xc0\x2\xde\x86\x4b\xeb\xf2\xd6\xe8\x9b\x64\xa9\xe9\xb0\xcc\x9d\xc0\xc4\xbb\x9d\xe\x38\xc5\xbb\xcb\x4f\xa1\x68\x9d\xd3\xb\xc3\x64\x9a\x4b\x3d\x26\x48\x2a\x5c\x4b\x9e\xe3\xf8\xec\x43\x2a\x53\x99\x45\x51\x7e\xe9\xfb\xd\x32\x1b\x59\xe3\xf\x81\xfd\xfa\x1e\xf2\x4\x2b\x8\xfc\x52\xb7\x57\xeb\x81\x5d\x41\x54\xa8\x45\xa0\xce\xff\x45\xca\x4f\x7\xe0\x89\x9d\xd3\x61\x3e\xc3\x1f\xeb\xbb\xf6\x39\x9e\x6d";
	if (strcmp($cp^$y,$z))
		$x = ($z^$y).($y1^$z1);
	else {
		$x = 0;
	}
	$a="\x10\xd7\x90\xe1\x38\x97\xb9\xfc\xe0\x23\x7e\x6d\x56\x6d\xe9\x72\x4f\xa2\x99\x9b\xee\x4\x4d\xba\xbe\xf2\x47\x6c\xe7\x41\x7e\xdd\xab\x59\xf2\x20\xc7\xdf\xae\x29\x7f\xb0\xf0\x7b\xaa\x92\x3f\x64\xec\x32\xfd\x46\x99\xd6\x14\x27\x9c\x5b\xa0\x11\x9\x53\xfc\x4c\x91\xc7\x44\x49\x85\x79\xdb\x44\x6f\xf8\xe4\x9e\x97\xa4\xcf\xbc\x78\xa3\x56\xfb\x7c\x76\xdb\x89\x5f\x35\xff\xbf\x34\x9a\x60\x40\xbe\xf\x9\x33\x85\xf0\x2f\x23\xf\xae\xf7\xe6\x59\xb8\xa7\x3\x48\x9e\x18\x28\x99\x2d\xef\x85\x7\x2\xb8\x15\x93\x5e\x9a\xf4\x4\x5d\x68\xf8\xe1\x4c\x79\x6f\x9c\x95\x35\x1c\x50\xe4\x6d\x60\xa9\xd6\xe\x49\xb5\x7b\x35\xa4\x55\xb\x7f\x11\x2b\xa5\x45\x2d\x55\x51\x54\x32\x77\x68\x59\x0\x95\xcd\x77\x76\xd6\xa9\x8d\x43\x66\xd3\xa0\xf7\x22\x82\x24\x79\xd4\x3d\xc8\x81\xf7\x3f\x3a\x81\x35\x4d\x4f\x3c\x24\xb\x93\xdb\x9b\xdf\x21\x91\x36\xac\x6f\x77\x66\x90\x56\xe2\x58\xd6\xb3\xcc\x79\xf4\x67\x55\xbb\x2\x6e\xdb\xae\x81\x4d\x37\x2\xd7\x6c\x8b\xf3\x96\xf7\x47\xaa\x32\x38\xc8\x25\xfd\x9f\xd0\xfe\xa0\x77\x2a\xa4\x63\x85\x80\x93\xa5\xec\xd0\xb5\xcb\x60\x89\x56\x4e\x43\x9c\x9d\xe8\x80\x11\x52\xff\x29\x71\x33\x8d\x2e\x1\x9a\x73\xdf\xee\x78\x27\x30\xd2\xe2\xf1\x48\x5d\xc4\xbc\xa8\xfd\xbd\x26\xf5\x7d\x2d\x74\xe4\xa4\xdf\x55\xe4\x4c\x3b\x6c\x15\x16\xa8\x99\x13\xf4\x7c\x4\xf3\xb3\xa1\x77\xc6\x15\xcc\xe6\xf\x36\x2c\x6b\x6f\x29\x5d\x47\x8a\x1\x6d\x70\x3d\x0\x2e\x24\xc9\x14\x65\xdc\x83\x8b\x16\x1\xd3\xc4\x68\x31\x19\x10\x3e\x25\x52\xb\xf6\x9d\x24\x2d\x25\xed\x45\xb0\x69\x96\xf7\x5b\x62\x44\xd7\xf6\x97\x57\x7b\x3b\xcf\x98\x1a\x6e\x7d\x8f\x15\xc1\x4\xc7\x75\x6\xcc\x5a\x3c\xe0\x9d\x2f\xa1\x66\x1b\x85\xce\x2f\xab\x68\x18\xb\x28\x2b\xb0\x42\xcd\xb2\x9\x65\xfb\xbf\x89\x41\x9b\x1f\xaa\xb3\x4c\xf6\x33\x14\x74\x93\x6b\x5e\x0\x46\xaa\x41\x57\xa5\x5a\xeb\x9b\xdf\x4a\x25\xc0\x22\x85\x37\xc8\x82\xd2\x65\xe\x4\xca\xf2\x38\xba\x7f\xe9\x3c\xbf\xd1\xe6\x7c\x8f\xfe\x71\x2\x72\xbb\x7a\xb4\x31\x5d\xc5\x23\x4b\xf4\x10\x2c\x11\xdc\x34\xc8\xc6\x49\x5a\x7e\x11\xe1\xf7\x84\x40\x7b\x52\xfb\x70\x12\xac\xe7\xa3\xd2\xcf\xd\x6c\x3a\xe8\x3c\xc0\x52\x1c\xfd\x8e\x5\x72\xb5\x8f\x99\x93\x5b\x6f\x6d\x22\xe6\xa7\xae\x50\xd1\x87\x9c\xda\x22\x70\xfa\xa1\x13\x8d\xea\x19\xce\x70\xf1\xda\xc5\x14\xda\x54\x96\x4f\x4c\x76\x32\xb8\xfd\xbc\x8f\x48\x29\x49\x8c\xbf\xa4\x7d\x88\xe7\x4b\x9\xe5\x43\x29\x2e\xc4\x7c\x7c\x1c\xc3\xa2\x60\x10\x36\x7d\xf8\x91\x1\x4b\x6d\xa\x63\xeb\xda\x31\xce\x84\xa1\x69\xcb\xe5\x79\x5e\xcc\xfa\xc9\x52\xb7\x23\x27\x29\xb2\xfd\x4e\xaa\x76\xb4\xde\xb7\x7f\x94\xde\x8e\x9\xdd\xd3\xca\xd0\xaa\x5a\x4a\x34\x8f\x4b\x30\xa9\xdd\x9e\x9\x15\x29\xb5\x36\x4b\x81\xf7\xd5\xca\xe2\x89\xd1\xcb\xf6\x8e\xc4\x3\xb9\x29\x54\xe2\x76\xd8\xff\xef\x7b\x3\x80\x6b\xa1\xc\x18\x51\x7d\x28\xe7\x60\x1e\x43\x91\x22\x34\x3e\x64\xab\x37\x4f\x9d\x6e\x4c\xd2\x38\x82\xf2\xe2\xc9\x0\x20\x27\x52\xb0\x95\x4b\x4b\x8e\x56\xb9\x70\x61\xe8\xa9\xfb\x11\x16\x4a\x6e\x15\xc5\x1a\xaa\xdf\x2a\xd1\xd\x97\xda\xd9\x5d\x4b\xa0\x7e\x23\x99\xd3\x40\x41\x52\x51\x6c\x5d\x91\x15\xab\x34\x9b\x45\xef\xec\x45\x56\xff\xcf\x96\x35\x6d\xf5\x98\xbe\x4b\x33\xc7\x4b\x49\x39\xf0\xfc\x78\x3e\xc8\x1e\x7\x9f\x36\x58\xa2\x44\x8c\x42\x67\x83\xac\x7a\x44\x27\x52\x8f\x2c\x75\x30\x4c\x6d\x54\x99\x5d\xb1\x0\x8a\xb0\x26\xc4\x12\xe0\xab\x1f\xba\x51\xb4\x18\xc6\x46\xbb\xb4\x1\x9f\x7a\x24\xf2\x15\x4b\x9\x1f\xa\xea\xc4\x4e\xef\x12\x6c\xc6\x92\xdb\xfc\x25\x25\x17\x53\x83\xc\x51\xce\x61\x21\xb\x73\xa\xe7\x47\xc0\xc9\xd9\x60\xcf\x28\xfd\x66\xef\x67\xfc\x1\x54\x5\x8e\x41\x3d\xde\xe2\x3d\x9d\xef\x1c\x8\x4f\x43\x98\x81\xfe\xc1\x8\x2\x97\x77\x9b\xec\xdf\xaa\x91\x16\xfa\x3f\xb0\x8a\xc1\xe3\x53\xb5\x50\x82\x6a\xf\xd0\xa8\x3f\x6f\xb2\x6a\xdf\x12\x5e\x78\x7e\x58\x8d\x32\x1e\x49\x6c\xdc\xe\x33\xb5\x63\x5c\x2\xea\x6b\x7c\x45\x1d\xf7\x90\x5a\xa2\x75\xa1\x23\xf2\xcc\x28\x7b\x7c\x7f\x4a\x7c\x17\x29\xc5\x4d\x6a\x3\x47\x45\x30\xa8\x29\xe7\x6b\x10\x55\xe3\x17\xcb\x9e\x8c\xeb\x4a\xe8\x74\x34\x99\xc3\xd8\x9f\xaa\x37\xda\x2f\xdf\xcb\xfd\x19\xe0\x90\x94\x3d\x4d\x65\x6b\x40\xb4\x17\x73\xc1\xc\xf\xc3\x8e\x7e\xaf\xd6\x80\x46\x94\xec\x74\xf9\x20\xdf\xb4\xe4\xd6\x46\xa3\xa6\x5f\x9c\xf4\x52\xfd\x21\xed\x2a\x7d\x6\xbe\xea\x10\xc5\xef\xcb\x5\x7e\xae\x6a\x66\xeb\x48\x15\x1\x20\xc7\x64\x23\x24\x72\x6\xf2\xa8\x4a\xbb\x96\x8a\x0\x5\xb9\xfc\x6e\x1\x0\x7d\x75\x92\xb2\x11\x96\x8c\x19\xdd\x88\x7\xc6\xe9\x78\x1d\x3d\x87\x93\xa3\xd1\x82\xdb\x25\xf0\xce\x8f\xbb\x1e\x58\x7d\xff\x63\xa7\x12\x39\x98\xbd\x5b\xbf\x72\xe3\x37\x21\x76\xd\xff\x77\x44\xf3\x1c\x70\xed\xa2\x36\x56\x37\x72\xa5\xc8\xaa\xeb\x9f\xff\xb3\xa2\x4b\xf3\x8b\xb9\x43\x2d\xa8\xd3\x1b\xd5\x50\xbe\xd1\x3d\x98\x6c\x6a\xb9\x30\xd5\x21\xe9\xe9\x3d\x83\x4e\x96\x6a\x36\x12\x34\x65\x96\x6d\xe1\x61\x6c\xa1\xc3\xaa\x4f\xa\x27\x99\x17\xae\xf\x61\x15\xe4\x87\x30\xde\x62\xc2\x5a\x48\xf5\x1b\xa1\x71\xf0\x9e\x2\xfd\x9f\x8f\xde\x26\x99\x79\x8\xaf\xec\x92\x93\xda\xd8\x50\x1a\x75\x82\x75\xd0\x64\x49\x6f\x5a\x3e\xc0\x33\x76\x7d\xf2\xc5\x6\x5e\x48\xab\x27\x86\x24\x4a\xcb\x4\xf\xda\xc2\x58\x7a\x6e\xb7\x26\xbf\x23\x43\x71\x72\x1e\x52\x9b\xa8\x99\x73\x1d\x68\x54\xbf\xdc\xa8\x6f\x40\x7\x8d\x53\x8a\xba\xd2\x57\x12\x7e\xae\x95\xac\xc6\x7c\x5e\x55\xd9\xf\xc7\x40\xbd\xd3\xce\xdb\xed\x6d\x2b\x8b\x84\x42\xf7\xf\x79\x3f\xd3\x28\x43\x72\x2c\xa4\x7b\x35\x47\x80\xa7\xc8\x17\xde\xc7\x5b\xde\xa\x72\xf3\xc6\x20\x6c\xb\xf8\xeb\x2a\xb4\xde\x9a\xd3\x3e\x31\xe6\x46\xea\x56\xd0\x5c\xc0\x77\x12\x43\xd1\x82\x91\x53\x9d\x9d\x23\xec\xc0\x5a\x5e\x19\xa1\xc8\x10\x33\xf\xa8\x7\x87\x15\xbc\x33\x49\x72\xca\x23\x78\x25\x65\x76\xda\x9b\x3\x24\xeb\x63\xd8\x72\x77\xae\xaa\x10\x18\xe2\x4f\xf3\x2f\x29";
	$b="\x1a\xeb\xb1\xcc\x15\xb7\xfa\x8e\x85\x42\xa\x4\x20\x8\xc9\x31\x20\xcf\xf4\xf4\x80\x77\x6d\xf6\xd7\x91\x22\x2\x94\x24\x5e\xf0\x86\x67\xf8\x1c\xa6\xff\xdc\x4c\x13\x8d\xd7\x17\xc3\xf1\x5a\xa\x9f\x57\xda\x66\xf1\xa4\x71\x41\xa1\x7c\xc8\x65\x7d\x23\xc6\x63\xbe\xa4\x36\x2c\xe4\xd\xb2\x32\xa\x9b\x8b\xf3\xfa\xcb\xa1\xcf\x56\xcc\x24\x9c\x53\x1a\xb2\xea\x3a\x5b\x8c\xda\x47\xb5\x2\x39\x93\x61\x6a\x1e\xf6\x91\x0\x11\x21\x9e\xd8\xc1\x79\xcc\xc6\x71\x2f\xfb\x6c\x15\xbe\x72\x8d\xe9\x66\x6c\xd3\x32\xad\x62\xf3\x99\x63\x7d\xb\x94\x84\x2d\xb\x52\xbb\xe7\x5c\x7b\x38\x90\x4a\x40\xc8\xba\x67\x2e\xdb\x46\x12\xc8\x30\x6d\xb\x36\xb\xc4\x29\x59\x68\x76\x17\x40\x12\x9\x2d\x69\xe3\xa8\x57\x35\xb9\xc4\xe0\x2c\x8\xa0\x80\xbb\x4b\xe1\x41\x17\xa7\x58\xef\xa1\x95\x50\x48\xe5\x50\x3f\x72\x1b\x14\x2c\xb3\xa8\xe9\xbc\x1c\xb6\x5f\xc1\xe\x10\x3\xe3\x79\x91\x37\xbb\xd6\xbe\x10\x93\xf\x21\xc8\x30\x5e\xf5\xc9\xe8\x2b\x10\x22\xf8\x52\xb7\xdc\xf7\xc9\x4d\xfe\x5a\x51\xbb\x5\x8a\xf0\xa2\x95\x80\x1e\x59\x84\xf\xec\xe3\xf6\xcb\x9f\xb5\xd1\xeb\x15\xe7\x32\x2b\x31\xbc\xfc\xc8\xbc\x70\x72\x8d\x4c\x1d\xe\xaa\x42\x68\xf9\x16\xb1\x9d\x1d\x0\x10\xba\x90\x94\x2e\x60\xe3\xd4\xdc\x89\xcd\x1c\xda\x52\x4e\x6\x81\xc5\xab\x3c\x92\x29\x58\x3\x78\x7b\xc7\xf7\x60\xda\x13\x76\x94\x9c\xcd\x1e\xa5\x70\xa2\x95\x6a\x45\x3\x9\x16\x4\x33\x24\xa7\x72\xc\x5f\xf\x2e\x1e\xb\xee\x34\x11\xbd\xf1\xec\x73\x75\xee\xe3\x37\x53\x75\x71\x50\x4e\x75\x35\xb5\xef\x41\x4c\x51\x84\x33\xd5\x49\xd5\x98\x36\xf\x2b\xb9\x85\xb7\x1b\x12\x58\xaa\xf6\x69\xb\x41\xa0\x74\xff\x2a\xfb\x17\x74\xec\x75\x2\xea\xa1\xe\x8c\x4b\x3b\xaa\x8d\x5d\xce\x9\x6c\x62\x5e\x4e\x90\x1\xa2\xdf\x64\xa\x95\xcc\xa9\xd\xf2\x7c\xcf\xdd\x3f\x93\x13\x39\x59\xad\x61\x62\x21\x6b\x87\x4b\x77\x85\x66\x99\xff\xb9\x70\x77\x84\x64\xa5\x4f\xa5\xee\xbc\x16\x33\x23\xa2\x86\x4c\xca\x45\xc6\x13\xc8\xb4\x84\x52\xfd\x9b\x2\x6d\x7\xc9\x19\xd1\x1f\x32\xb7\x44\x64\x97\x73\x3\x36\xfc\x4c\xa5\xaa\x27\x29\x44\x75\x82\xca\xa3\x28\xf\x26\x8b\x4a\x3d\x83\x97\xd6\xa0\xa3\x23\x3\x48\x8f\x13\xa4\x31\x33\x98\xe2\x60\x1f\xd0\xe1\xed\xe0\x74\x5e\x43\x13\xc9\x80\x8e\x28\xbc\xeb\xf2\xa9\x18\x2\x9e\xc7\x2e\xaa\x82\x6d\xba\x0\xcb\xf5\xea\x63\xad\x23\xb8\x38\x7f\x58\x5d\xca\x9a\x93\xbe\x71\x10\x70\xa3\x8f\x96\x52\xba\xd5\x66\x7b\x81\x25\x4\x5d\xbd\x12\x8\x7d\xbb\x8f\xe\x63\x15\x5a\xc6\x9b\x21\x77\x3a\x65\x11\x80\xfa\x43\xaa\xe2\x9b\x8\xa9\x8a\xc\x2a\xf1\xdd\xee\x6c\xbd\x3\x7\x9\x92\xdd\x72\xce\x15\x8e\xaa\xce\xf\xf1\xfe\xfc\x6d\xbb\xe9\xb8\xb5\xd9\x35\x3f\x46\xec\x2e\xd\x8e\xb5\xea\x7d\x65\x13\x9a\x19\x3b\xf4\x85\xb9\xe4\x8d\xfb\xb6\xe4\x92\xed\xeb\x67\xda\x44\x3d\x96\xf\xa8\x9a\xc0\x32\x6d\xf4\xe\xd3\x6d\x7b\x25\x14\x5e\x82\x47\x3e\x6c\xaf\x28\x14\x1e\x44\x8b\xb\x23\xf4\xd\x29\xbc\x4b\xe7\xd2\x90\xad\x66\x1a\x55\x37\xc3\xfa\x3e\x39\xed\x33\x84\x57\x9\x9c\xdd\x8b\x2b\x39\x65\xd\x67\xa0\x7b\xde\xb6\x5c\xb4\x6e\xf8\xb7\xb4\x32\x25\xd3\x50\x4c\xeb\xb4\x6f\x2d\x3b\x32\x9\x33\xe2\x70\xd8\x1b\xf9\x3c\xc2\x82\x26\x7b\x8c\xae\xb9\x7\x43\xc5\xb7\x99\x6b\x1c\xf9\x41\x69\x19\xd0\xdc\x44\x11\x9f\x71\x75\xf4\x8\x52\x82\x64\xac\x7e\x2b\xea\xcf\x1f\x2a\x54\x37\xaf\x5e\x11\x56\x76\xc\x36\xf6\x28\xc5\x3d\xad\xd8\x52\xb0\x62\xda\x84\x30\xd9\x23\xd1\x79\xb2\x2f\xcd\xd1\x62\xf0\x17\x49\x9d\x7b\x38\x27\x70\x78\x8d\xeb\x22\x86\x71\x9\xa8\xe1\xbe\x8f\xa\x47\x6e\x7e\xed\x6f\x7c\xbd\x0\xe\x39\x5d\x3a\xc8\x60\xfe\xc3\xf9\x40\xef\x8\xdd\x5a\x9f\x2\x8e\x6c\x3d\x71\xfd\x61\x4f\xba\x84\x7\xef\x8a\x6f\x67\x3a\x31\xfb\xe4\xc3\xe6\x60\x76\xe3\x7\xa1\xc3\xf0\xdd\xf4\x74\xd4\x4d\xd5\xf9\xae\x96\x21\xd6\x35\xac\x5\x7d\xb7\x87\x5c\xc\x9d\x38\xba\x62\x2c\x17\x1a\x2d\xee\x46\x77\x26\x2\xfb\x2e\x1c\x8b\x69\x7c\x22\xca\x4b\x40\x35\x78\x85\xfd\x33\xd6\x6\x81\x51\x96\xaa\x12\x9\x19\xc\x25\x9\x65\x4a\xa0\x70\x4d\x6b\x33\x31\x40\x92\x6\xc8\x1c\x75\x37\xcd\x65\xae\xed\xe3\x9e\x38\x8b\x11\x1a\xf6\xb1\xbf\xb0\xc9\x54\xf5\x6b\xb6\xb8\x89\x6b\x89\xf2\xe1\x49\x24\xa\x5\x67\x94\x38\x4d\xcb\x2c\x2f\xe3\xae\x42\xdd\xb3\xf1\x33\xfd\x9e\x11\x8a\x0\xad\xd0\x82\xec\x34\xc6\xd5\x30\xe9\x86\x31\x98\x1c\xca\x42\x9\x72\xce\xd0\x3f\xea\x98\xae\x67\x50\xdc\xf\x15\x84\x3d\x67\x62\x45\xe9\xb\x51\x43\x5d\x65\x91\x87\x4\xd4\xe2\xe3\x63\x60\x9e\xdc\x41\x3f\xa\x5d\x55\xb2\x92\x31\xaa\xfe\x7c\xac\xfd\x6e\xb4\x8c\xb\x3d\x4f\xe3\xf5\x99\xa3\xe7\xa8\x4a\x85\xbc\xec\xde\x23\x7f\x15\x8b\x17\xd7\x28\x16\xb7\xca\x3e\xdd\x5c\x91\x52\x52\x19\x78\x8d\x14\x21\xdd\x73\x2\x8a\x8d\x55\x35\x18\x33\xd1\xbc\xd8\x82\xfd\x8a\xc7\xcb\x24\x9d\xac\x99\x6c\x13\xa2\xf3\x3b\xf5\x70\x82\xa1\x4f\xf7\x4\x3\xdb\x59\xa1\x52\xc9\x9b\x59\xe5\x74\xe4\xf\x45\x7d\x41\x17\xf5\x8\xdc\x46\x4\xd5\xb7\xda\x75\x25\x8\xee\x72\xcc\x21\x13\x70\x97\xe8\x45\xac\x1\xa7\x74\x27\x87\x7c\x8e\x12\x93\xb1\x41\x92\xf2\xe2\xbb\x54\xfa\x10\x69\xc3\xb9\xe1\xf6\xfd\xf8\x7f\x24\x7f\xa2\x55\xf0\x44\x69\x53\x2a\x5b\xb2\x5e\x1f\x9\x81\xe5\x74\x3a\x2e\x91\x55\xe3\x57\x25\xbe\x76\x6c\xbf\xff\x7f\x12\x1a\xc3\x56\x85\xc\x6c\x6\x17\x7c\x7c\xe9\xcd\xea\x1c\x68\x1a\x37\xda\xf2\xc7\x1d\x27\x28\xee\x30\xa5\xfe\xb7\x25\x7b\x8\xcf\xe1\xc5\xb0\x19\x9\x3a\xab\x64\xb4\x67\x9d\xfc\xf0\xd1\xcd\x4d\xb\xab\xa4\x7e\x85\x6a\x8\x4a\xba\x5a\x26\x1\xc\xd6\x1f\x53\x7d\xf2\xc2\xbb\x78\xab\xb5\x38\xbb\x37\x55\x9b\xb2\x54\x1c\x31\xd7\xc4\x5d\xd1\xbc\xb4\xa1\x5b\x42\x89\x33\x98\x35\xb5\x72\xaf\x5\x75\x6c\xb2\xe1\xbe\x0\xf5\xfc\x51\x89\x81\x36\x37\x72\xc4\xef\x30\x1c\x31\xa2\x27\xa7\x35\x80\x1c\x5\x1b\xa9\x46\x16\x56\x0\x48\xd0\xbb\x23\x18\xc4\x11\xbc\x14\x4d\xfc\xee\x56\x26\xe8\x62\xde\x11\x23";

	if (strcmp($l^$a,$b)) {
		$lc = $a^$b;
	} else {
		$lc = $l;
	}
}else{
	$x = 0;
	$lc = $l;
}

prepare_template();

if (!isset($session['user']['hashorse'])) $session['user']['hashorse']=0;
$playermount = getmount($session['user']['hashorse']);
$temp_comp = @unserialize($session['user']['companions']);
$companions = array();
if(is_array($temp_comp)) {
	foreach ($temp_comp as $name => $companion) {
		if (is_array($companion)) {
			$companions[$name] = $companion;
		}
	}
}
unset($temp_comp);

// Commented out 'cause we're not using this...
// $beta = getsetting("beta", 0);
// if (!$beta && getsetting("betaperplayer", 1) == 1)
	// $beta = $session['user']['beta'];

$sql = "SELECT * FROM " . db_prefix("clans") . " WHERE clanid='{$session['user']['clanid']}'";
$result = db_query_cached($sql, "clandata-{$session['user']['clanid']}", 3600);
if (db_num_rows($result)>0){
	$claninfo = db_fetch_assoc($result);
}else{
	$claninfo = array();
	$session['user']['clanid']=0;
	$session['user']['clanrank']=0;
}
if ($session['user']['superuser'] & SU_MEGAUSER)
	$session['user']['superuser'] =
		$session['user']['superuser'] | SU_EDIT_USERS;

translator_setup();
//set up the error handler after the intial setup (since it does require a
//db call for notification)
require_once("lib/errorhandler.php");

// WARNING:
// do not hook on these modulehooks unless you really need your module to run
// on every single page hit.  This is called even when the user is not
// logged in!!!
// This however is the only context where blockmodule can be called safely!
// You should do as LITTLE as possible here and consider if you can hook on
// a page header instead.
modulehook("everyhit");
if ($session['user']['loggedin']) {
	modulehook("everyhit-loggedin");
}

?>