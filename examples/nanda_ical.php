<?php
session_start();
include("config.php");
include("../src/Nanda/Nanda3API.php");


$LBC = "\n";
$ical_headers = Array();
$ical_headers["VERSION"] = "2.0";
$ical_headers["PRODID"] = "nl.urenoverzicht";
$ical_headers["CALSCALE"] = "GREGORIAN";
$ical_headers["METHOD"] = "PUBLISH";
$ical_headers["X-WR-TIMEZONE"] = "Europe/Amsterdam";
$ical_headers["X-WR-CALNAME"] = "Nanda agenda";


$output = "BEGIN:VCALENDAR" . $LBC;
foreach($ical_headers as $h => $v) {
	$output .= "$h:$v" . $LBC;

}


$postvars["select"] = "*,owner";
$postvars["where"] = "";
$postvars["order_field"] = "range_from";
$postvars["order_direction"] = "-1";
$postvars["limit_by"] = "";

$params = Array();
$params["apiHost"] = $apiHostname;
$params["userEmail"] = $apiUserEmail;
$params["userPassword"] = $apiUserPassword;
$Nanda3Service = new Nanda3APIObject($params);
$auth = $Nanda3Service->auth();


$response = $Nanda3Service->sendRequest("GET", "/timelog", $postvars);


$cont = json_decode($response['content']);
$records = $cont->result->timelog;
//print_r($records);
foreach($records as $record) {
	$uid = "nl.urenoverzicht/Calendar/" . $record->id;
	$summary = $record->description;;
	$description = "labels:";
	$dtstamp = str_replace(Array("-",":"), "", $record->range_from);
	$dtstart = str_replace(Array("-",":","Z"), "", $record->range_from);
	$dtend = str_replace(Array("-",":","Z"), "", $record->range_until);
	$sequence = "0";

	$output .= "BEGIN:VEVENT" . $LBC;
	$output .= "DTSTAMP:" . $dtstamp . $LBC;	
	$output .= "UID:" . $uid . $LBC;	
	$output .= "DTSTART;TZID=Europe/Amsterdam:" . $dtstart . $LBC;	
	$output .= "DTEND;TZID=Europe/Amsterdam:" . $dtend . $LBC;	
	$output .= "SEQUENCE:" . $sequence . $LBC;	
	$output .= "DESCRIPTION:" . $description . $LBC;	
	$output .= "SUMMARY:" . $summary . $LBC;
	$output .= "END:VEVENT" . $LBC;
}

$output .= "END:VCALENDAR" . $LBC;


$the_filename = date("Y-M-d_H-i:s", time()) . ".ics";
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=' . $the_filename);
print $output;
exit;




?>