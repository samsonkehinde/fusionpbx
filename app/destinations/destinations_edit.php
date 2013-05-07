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
	Portions created by the Initial Developer are Copyright (C) 2008-2012
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "root.php";
require_once "includes/require.php";
require_once "includes/checkauth.php";
if (permission_exists('destination_add') || permission_exists('destination_edit')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
	require_once "app_languages.php";
	foreach($text as $key => $value) {
		$text[$key] = $value[$_SESSION['domain']['language']['code']];
	}

//action add or update
	if (isset($_REQUEST["id"])) {
		$action = "update";
		$destination_uuid = check_str($_REQUEST["id"]);
	}
	else {
		$action = "add";
	}

//get http post variables and set them to php variables
	if (count($_POST)>0) {
		$dialplan_uuid = check_str($_POST["dialplan_uuid"]);
		$destination_type = check_str($_POST["destination_type"]);
		$destination_number = check_str($_POST["destination_number"]);
		$destination_caller_id_name = check_str($_POST["destination_caller_id_name"]);
		$destination_caller_id_number = check_str($_POST["destination_caller_id_number"]);
		$destination_context = check_str($_POST["destination_context"]);
		$fax_uuid = check_str($_POST["fax_uuid"]);
		$destination_enabled = check_str($_POST["destination_enabled"]);
		$destination_description = check_str($_POST["destination_description"]);
	}

if (count($_POST)>0 && strlen($_POST["persistformvar"]) == 0) {

	$msg = '';
	if ($action == "update") {
		$destination_uuid = check_str($_POST["destination_uuid"]);
	}

	//check for all required data
		//if (strlen($dialplan_uuid) == 0) { $msg .= "Please provide: Dialplan UUID<br>\n"; }
		//if (strlen($destination_type) == 0) { $msg .= "Please provide: Name<br>\n"; }
		//if (strlen($destination_number) == 0) { $msg .= "Please provide: Extension<br>\n"; }
		//if (strlen($destination_caller_id_name) == 0) { $msg .= "Please provide: Caller ID Name<br>\n"; }
		//if (strlen($destination_caller_id_number) == 0) { $msg .= "Please provide: Caller ID Number<br>\n"; }
		//if (strlen($destination_context) == 0) { $msg .= "Please provide: Context<br>\n"; }
		//if (strlen($destination_enabled) == 0) { $msg .= "Please provide: Enabled<br>\n"; }
		//if (strlen($destination_description) == 0) { $msg .= "Please provide: Description<br>\n"; }
		if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
			require_once "includes/header.php";
			require_once "includes/persistformvar.php";
			echo "<div align='center'>\n";
			echo "<table><tr><td>\n";
			echo $msg."<br />";
			echo "</td></tr></table>\n";
			persistformvar($_POST);
			echo "</div>\n";
			require_once "includes/footer.php";
			return;
		}

	//add or update the database
		if ($_POST["persistformvar"] != "true") {
			if ($action == "add") {
				$sql = "insert into v_destinations ";
				$sql .= "(";
				$sql .= "domain_uuid, ";
				if (strlen($dialplan_uuid) > 0) {
					$sql .= "dialplan_uuid, ";
				}
				$sql .= "destination_uuid, ";
				$sql .= "destination_type, ";
				$sql .= "destination_number, ";
				$sql .= "destination_caller_id_name, ";
				$sql .= "destination_caller_id_number, ";
				$sql .= "destination_context, ";
				if (strlen($fax_uuid) > 0) {
					$sql .= "fax_uuid, ";
				}
				$sql .= "destination_enabled, ";
				$sql .= "destination_description ";
				$sql .= ")";
				$sql .= "values ";
				$sql .= "(";
				$sql .= "'$domain_uuid', ";
				if (strlen($dialplan_uuid) > 0) {
					$sql .= "'$dialplan_uuid', ";
				}
				$sql .= "'".uuid()."', ";
				$sql .= "'$destination_type', ";
				$sql .= "'$destination_number', ";
				$sql .= "'$destination_caller_id_name', ";
				$sql .= "'$destination_caller_id_number', ";
				$sql .= "'$destination_context', ";
				if (strlen($fax_uuid) > 0) {
					$sql .= "'$fax_uuid', ";
				}
				$sql .= "'$destination_enabled', ";
				$sql .= "'$destination_description' ";
				$sql .= ")";
				$db->exec(check_sql($sql));
				unset($sql);

				require_once "includes/header.php";
				echo "<meta http-equiv=\"refresh\" content=\"2;url=destinations.php\">\n";
				echo "<div align='center'>\n";
				echo $text['message-add']."\n";
				echo "</div>\n";
				require_once "includes/footer.php";
				return;
			} //if ($action == "add")

			if ($action == "update") {
				$sql = "update v_destinations set ";
				if (strlen($dialplan_uuid) > 0) {
					$sql .= "dialplan_uuid = '$dialplan_uuid', ";
				}
				$sql .= "destination_type = '$destination_type', ";
				$sql .= "destination_number = '$destination_number', ";
				$sql .= "destination_caller_id_name = '$destination_caller_id_name', ";
				$sql .= "destination_caller_id_number = '$destination_caller_id_number', ";
				$sql .= "destination_context = '$destination_context', ";
				if (strlen($fax_uuid) > 0) {
					$sql .= "fax_uuid = '$fax_uuid', ";
				}
				$sql .= "destination_enabled = '$destination_enabled', ";
				$sql .= "destination_description = '$destination_description' ";
				$sql .= "where domain_uuid = '$domain_uuid' ";
				$sql .= "and destination_uuid = '$destination_uuid'";
				$db->exec(check_sql($sql));
				unset($sql);

				require_once "includes/header.php";
				echo "<meta http-equiv=\"refresh\" content=\"2;url=destinations.php\">\n";
				echo "<div align='center'>\n";
				echo $text['message-update']."\n";
				echo "</div>\n";
				require_once "includes/footer.php";
				return;
			} //if ($action == "update")
		} //if ($_POST["persistformvar"] != "true")
} //(count($_POST)>0 && strlen($_POST["persistformvar"]) == 0)

//pre-populate the form
	if (count($_GET)>0 && $_POST["persistformvar"] != "true") {
		$destination_uuid = $_GET["id"];
		$sql = "select * from v_destinations ";
		$sql .= "where domain_uuid = '$domain_uuid' ";
		$sql .= "and destination_uuid = '$destination_uuid' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll();
		foreach ($result as &$row) {
			$dialplan_uuid = $row["dialplan_uuid"];
			$destination_type = $row["destination_type"];
			$destination_number = $row["destination_number"];
			$destination_caller_id_name = $row["destination_caller_id_name"];
			$destination_caller_id_number = $row["destination_caller_id_number"];
			$destination_context = $row["destination_context"];
			$fax_uuid = $row["fax_uuid"];
			$destination_enabled = $row["destination_enabled"];
			$destination_description = $row["destination_description"];
			break; //limit to 1 row
		}
		unset ($prep_statement);
	}

//set the defaults
	if (strlen($destination_type) == 0) { $destination_type = 'inbound'; }
	if (strlen($destination_context) == 0) { $destination_context = 'public'; }

//show the header
	require_once "includes/header.php";
	if ($action == "update") {
		$page["title"] = $text['title-destination-edit'];
	}
	else if ($action == "add") {
		$page["title"] = $text['title-destination-add'];
	}


//show the content
	echo "<div align='center'>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing=''>\n";
	echo "<tr class='border'>\n";
	echo "	<td align=\"left\">\n";
	echo "	  <br>";

	echo "<form method='post' name='frm' action=''>\n";
	echo "<div align='center'>\n";
	echo "<table width='100%'  border='0' cellpadding='6' cellspacing='0'>\n";
	echo "<tr>\n";
	if ($action == "add") {
		echo "<td align='left' width='30%' nowrap='nowrap'><b>".$text['header-destination-add']."</b></td>\n";
	}
	if ($action == "update") {
		echo "<td align='left' width='30%' nowrap='nowrap'><b>".$text['header-destination-edit']."</b></td>\n";
	}
	echo "<td width='70%' align='right'><input type='button' class='btn' name='' alt='back' onclick=\"window.location='destinations.php'\" value='".$text['button-back']."'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align='left' colspan='2'>\n";
	echo $text['description-destinations']."<br /><br />\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-destination_type'].":\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='destination_type'>\n";
	echo "	<option value=''></option>\n";
	switch ($destination_type) {
		case "inbound" : 	$selected[1] = "selected='selected'";	break;
		case "outbound" : 	$selected[2] = "selected='selected'";	break;
	}
	echo "	<option value='inbound' ".$selected[1].">".$text['option-type_inbound']."</option>\n";
	echo "	<option value='outbound' ".$selected[2].">".$text['option-type_outbound']."</option>\n";
	unset($selected);
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-destination_type']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-destination_number'].":\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='destination_number' maxlength='255' value=\"$destination_number\">\n";
	echo "<br />\n";
	echo $text['description-destination_number']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-destination_caller_id_name'].":\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='destination_caller_id_name' maxlength='255' value=\"$destination_caller_id_name\">\n";
	echo "<br />\n";
	echo $text['description-destination_caller_id_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-destination_caller_id_number'].":\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='destination_caller_id_number' maxlength='255' value=\"$destination_caller_id_number\">\n";
	echo "<br />\n";
	echo $text['description-destination_caller_id_number']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-destination_context'].":\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='destination_context' maxlength='255' value=\"$destination_context\">\n";
	echo "<br />\n";
	echo $text['description-destination_context']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "	".$text['label-fax_uuid'].":\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	$sql = "select * from v_fax ";
	$sql .= "where domain_uuid = '".$_SESSION["domain_uuid"]."' ";
	$sql .= "order by fax_name asc ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll(PDO::FETCH_ASSOC);
	echo "	<select name='fax_uuid' id='fax_uuid' class='formfld' style='".$select_style."'>\n";
	echo "	<option value=''></option>\n";
	foreach ($result as &$row) {
		if ($row["fax_uuid"] == $fax_uuid) {
			echo "		<option value='".$row["fax_uuid"]."' selected='selected'>".$row["fax_extension"]." ".$row["fax_name"]."</option>\n";
		}
		else {
			echo "		<option value='".$row["fax_uuid"]."'>".$row["fax_extension"]." ".$row["fax_name"]."</option>\n";
		}
	}
	echo "	</select>\n";
	unset ($prep_statement, $extension);
	echo "	<br />\n";
	echo "	".$text['description-fax_uuid']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-destination_enabled'].":\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='destination_enabled'>\n";
	echo "	<option value=''></option>\n";
	switch ($destination_enabled) {
		case "true" :	$selected[1] = "selected='selected'";	break;
		case "false" :	$selected[2] = "selected='selected'";	break;
	}
	echo "	<option value='true' ".$selected[1].">".$text['label-true']."</option>\n";
	echo "	<option value='false' ".$selected[2].">".$text['label-false']."</option>\n";
	unset($selected);
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-destination_enabled']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-destination_description'].":\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='destination_description' maxlength='255' value=\"$destination_description\">\n";
	echo "<br />\n";
	echo $text['description-destination_description']."\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	if ($action == "update") {
		echo "				<input type='hidden' name='dialplan_uuid' value='$dialplan_uuid'>\n";
		echo "				<input type='hidden' name='destination_uuid' value='$destination_uuid'>\n";
	}
	echo "				<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>\n";
	echo "		</td>\n";
	echo "	</tr>";
	echo "</table>";
	echo "</form>";

	echo "	</td>";
	echo "	</tr>";
	echo "</table>";
	echo "</div>";

//include the footer
	require_once "includes/footer.php";
?>