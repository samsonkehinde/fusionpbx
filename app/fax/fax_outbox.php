<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2021
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	include "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

//check permissions
	if (permission_exists('fax_extension_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//additional includes
	$document['title'] = 'Fax Outbox';
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>Fax Outbox</b></div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo "This is the Fax Outbox\n";
	echo "<br /><br />\n";

	//echo "<form id='form_list' method='post'>\n";
	echo "<form id='form_list' onsubmit='mySubmit()' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";
	
	echo "<input type='hidden' id='myId' name='myId' value='' />";

	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";

	echo "	<th>Destination</th>";
	echo "	<th>Status</th>";
	echo "	<th>Preview</th>";
	echo "	<th>Path</th>";
	
	echo "</tr>\n";
	
	
	//get fax extension
	if (is_uuid($_REQUEST["id"])) {
		$fax_uuid = $_REQUEST["id"];
		if (permission_exists('fax_extension_view_domain')) {
			//show all fax extensions
			$sql = "select fax_name, fax_extension from v_fax ";
			$sql .= "where domain_uuid = :domain_uuid ";
			$sql .= "and fax_uuid = :fax_uuid ";
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
			$parameters['fax_uuid'] = $fax_uuid;
		}
		else {
			//show only assigned fax extensions
			$sql = "select fax_name, fax_extension from v_fax as f, v_fax_users as u ";
			$sql .= "where f.fax_uuid = u.fax_uuid ";
			$sql .= "and f.domain_uuid = :domain_uuid ";
			$sql .= "and f.fax_uuid = :fax_uuid ";
			$sql .= "and u.user_uuid = :user_uuid ";
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
			$parameters['fax_uuid'] = $fax_uuid;
			$parameters['user_uuid'] = $_SESSION['user_uuid'];
		}
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row) && @sizeof($row) != 0) {
			//set database fields as variables
				$fax_name = $row["fax_name"];
				$faxext = $row["fax_extension"];
		}
		else {
			if (!permission_exists('fax_extension_view_domain')) {
				echo "access denied";
				exit;
			}
		}
		unset($sql, $parameters, $row);
	}
	
	
	
	$myId = $_SESSION['/var/lib/freeswitch/storage/fax/'.$_SESSION['domain_name'].'/'.$faxext.'/temp'];

	$sql = "select * from v_fax_files ";
	$sql .= "where fax_uuid = :fax_uuid ";
	$sql .= "and domain_uuid = :domain_uuid ";
	$sql .= "and fax_mode = 'tx' ";
	$parameters['fax_uuid'] = $_REQUEST["id"];
	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$database = new database;
	$fax_files = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);
	

//Test if currently sending:
//set the command
	$switch_cmd = 'show channels as json';

//create the event socket connection
	$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);

//send the event socket command and get the array
	if ($fp) {
		$json = trim(event_socket_request($fp, 'api '.$switch_cmd));
		
		$results = json_decode($json, "true");
	}

if (isset($results["rows"])) {
	
	if (is_array($results["rows"]) && @sizeof($results["rows"]) != 0) {
		$x = 0;
		foreach ($results["rows"] as $row) {
			
			$file = basename($row['application_data']);
			if (strtolower(substr($file, -3)) == "tif" || strtolower(substr($file, -3)) == "pdf") {
				$file_name = substr($file, 0, (strlen($file) -4));
			}
			
			
			if (strlen($row['fax_base64']) <= 0) {
				echo "<tr class='list-row' >\n";

				echo "<td>".$row['dest']."</td>";
				echo "<td>Sending...</td>";
				echo "<td><a href='https://dev.auravoice.us/app/fax/fax_files.php?id=".$_REQUEST["id"]."&a=download&type=fax_sent&t=bin&ext=".$faxext."&filename=".$file_name.".tif'>Fax PDF - ".$file_name."</a></td>";
				echo "<td>".$row['application_data']."</td>";
				echo "</tr>\n";
			}
			$x++;
		}
	}
}
	
	echo "</table>\n";
	echo "<br />\n";

	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>\n";


	echo "<span id='output'></span>\n";
	echo "<script type='text/javascript'>\n";	
    echo "function AutoRefresh( t ) { setTimeout('location.reload(true);', t); }";
	echo "AutoRefresh(10000);";
	
	echo "</script>\n";


	if (file_exists('/var/lib/freeswitch/storage/fax/'.$_SESSION['domain_name'].'/'.$faxext.'/temp/'.$myId.'.tif')) {
		rename('/var/lib/freeswitch/storage/fax/'.$_SESSION['domain_name'].'/'.$faxext.'/temp/'.$myId.'.tif', '/var/lib/freeswitch/storage/fax/'.$_SESSION['domain_name'].'/'.$faxext.'/sent/'.$myId.'.tif');
	}
	
	if (file_exists('/var/lib/freeswitch/storage/fax/'.$_SESSION['domain_name'].'/'.$faxext.'/temp/'.$myId.'.pdf')) {
		rename('/var/lib/freeswitch/storage/fax/'.$_SESSION['domain_name'].'/'.$faxext.'/temp/'.$myId.'.pdf', '/var/lib/freeswitch/storage/fax/'.$_SESSION['domain_name'].'/'.$faxext.'/sent/'.$myId.'.pdf');
	}



//include the footer
	require_once "resources/footer.php";

?>
