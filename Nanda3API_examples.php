<?php
session_start();
// override include
// include("config.php");
// Host running Nanda API
$apiHostname = "http://api-test.nanda.io";
// Nanda 3 Account username
$apiUserEmail = "mbolt@roboinvest.nl";
// Nanda 3 Account password
$apiUserPassword = "test";

include("Nanda3API/Nanda3API.php");

?>
<!DOCTYPE html>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="UTF-8">
<link rel='stylesheet' id='ui-css' href='css/nanda.css' type='text/css' media='all'/>
</head>
<body>
API Framework<br>
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

print "<br>Acounts:<br>";
$account_params = Array();
$acounts = $Nanda3Service->getAccounts($account_params);
$acountsHTML = processItemList($acounts);
print "$acountsHTML<br>";


$project_params = Array();
$project_params["select"] = "id,name,time_limit,archived";
$project_params["where"] = "";
$project_params["order_field"] = "name";
$project_params["order_direction"] = "1";
print "<br>Projects:<br>";
$projects = $Nanda3Service->getProjects($project_params);
$projectHTML = processItemList($projects);
print "$projectHTML<br>";


print "<br>Labels:<br>";
$label_params = Array();
$label_params["namespace"] = "user";
$labels = $Nanda3Service->getLabels($label_params);
$labelsHTML = processItemList($labels);
print "$labelsHTML<br>";

print "<br>Custom:<br>";
$method = "GET";
$endpoint = "/timelog";
$custom_params = Array();
$custom_params["select"] = "id";
$custom_params["where"] = "";
$custom_params["order_field"] = "range_from";
$custom_params["order_direction"] = "1";

$customQuery = $Nanda3Service->sendRequest($method, $endpoint, $custom_params);

$response_data = json_encode($customQuery["content"]);

$response = json_decode($response_data);	
//print_r($customQuery);
foreach($customQuery as $qv => $qq) {
	print "$qv - $qq,br>";
}
$customHTML = processItemList($response);
print "$customHTML<br>";



?>

</body></html>