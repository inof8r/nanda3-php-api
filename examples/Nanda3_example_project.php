<?php
session_start();
// override include
// include("config.php");
// Host running Nanda API
//$apiHostname = "http://api-test.nanda.io";
$apiHostname = "http://nanda3-vm.local:8080";
// Nanda 3 Account username
$apiUserEmail = "mbolt@roboinvest.nl";
// Nanda 3 Account password
$apiUserPassword = "test";

include("../src/Nanda/Nanda3API.php");

?>
<!DOCTYPE html>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="UTF-8">
</head>
<body>
Nanda3 PHP Client API examples:<br>
<?
// Simple list processor
function processItemList($lst) {
	foreach($lst as $ln => $lv) {
		$out .= "$ln: ";
		foreach($lv as $lvn => $lvv) {		
			$out .= " | $lvn = $lvv";
		}
		$out .= "<br>";		
	}
	return $out;
}

$params = Array();
$params["apiHost"] = $apiHostname;
$params["userEmail"] = $apiUserEmail;
$params["userPassword"] = $apiUserPassword;
$Nanda3Service = new Nanda3APIObject($params);
$auth = $Nanda3Service->auth();


$project_params = Array();
$project_params["select"] = "id,name,time_limit,archived";
$project_params["where"] = "";
$project_params["order_field"] = "name";
$project_params["order_direction"] = "1";
print "<br><strong>Projects:</strong><br>";
$projects = $Nanda3Service->getProjects($project_params);
$projectHTML = processItemList($projects);
print "$projectHTML<br>";




?>

</body></html>