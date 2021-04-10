<?php
	require_once "root.php";
	require_once "resources/require.php";

//Check Permission
	require_once "resources/check_auth.php";
	if (permission_exists('cc_report_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();


//additional includes
	require_once "resources/header.php";
	require_once "resources/paging.php";


	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	require_once "cc_report_inc.php";
	
//javascript to toggle export select box
	echo "<script language='javascript' type='text/javascript'>\n";
	echo "	var fade_speed = 400;\n";
	echo "	function toggle_select(select_id) {\n";
	echo "		$('#' + select_id).fadeToggle(fade_speed, function() {\n";
	echo "			document.getElementById(select_id).selectedIndex = 0;\n";
	echo "		});\n";
	echo "	}\n";
	echo "	function process_export() {\n";
	echo "		toggle_select('export_format');\n";
	echo "		if(document.getElementById('export_format').value == 'email'){\n";
	echo "			var recpt = prompt('Enter recipient email address.');\n";
	echo "			if(!validateEmail(recpt)){\n";
	echo "				display_message('" .$text['wrong-email'] ."','negative');\n";
	echo "				return;\n";
	echo "			}\n";
//	echo "			display_message('".$text['message-processing']."',0);\n";
	echo "			$.ajax({\n"; 
    echo "				type: 'POST',\n";
    echo "				url: 'cc_report_export.php',\n";
    echo "				data: {\n";
    echo "					agent: document.getElementById('agent').value,\n";
    echo "					start_stamp_begin: document.getElementById('start_stamp_begin').value,\n";
    echo "					start_stamp_end: document.getElementById('start_stamp_end').value,\n";
    echo "					caller_id: document.getElementById('caller_id').value,\n";
    echo "					report_recipient: recpt,\n";
    echo "					status: document.getElementById('status').value,\n";
    echo "					export_format: document.getElementById('export_format').value\n";
    echo "				},\n";
    echo "				success: function(response) {\n";
	echo "					display_message('".$text['message-email-success']."');\n";
    echo "				},\n";
    echo "				error: function(response) {\n";
	echo "					display_message('".$text['message-email-failed']."','negative');\n";
    echo "				}\n";
	echo "			});\n";
	echo "		}else{";
	echo "			display_message('".$text['message-processing']."');\n";
	echo "			document.getElementById('frm_export').submit();\n";
	echo "		}";
	echo "	};\n";
	echo "	function validateEmail(email) {\n";
	echo "		const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;\n";
	echo "		return re.test(email);\n";
	echo "	};\n";
	echo "</script>\n";

//page title and description

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
	echo "		<td align='left' nowrap='nowrap' style='vertical-align: top;'><b>".$text['title']."</b><br><br><br></td>\n";
	echo "		<td align='right' width='100%' style='vertical-align: top;'>\n";
	echo "			<form id='frm_export' method='post' action='cc_report_export.php'>\n";
	echo "				<input type='hidden' id='agent' name='agent' value='".$agent."'>\n";
	echo "				<input type='hidden' id='start_stamp_begin' name='start_stamp_begin' value='".$start_stamp_begin."'>\n";
	echo "				<input type='hidden' id='start_stamp_end' name='start_stamp_end' value='".$start_stamp_end."'>\n";
	echo "				<input type='hidden' id='caller_id' name='caller_id' value='".$caller_id."'>\n";
	echo "				<input type='hidden' id='status' name='status' value='".$status."'>\n";
	echo "				<table cellpadding='0' cellspacing='0' border='0'>\n";
	echo "					<tr>\n";
	echo "						<td style='vertical-align: top;'>\n";
	echo "							<input type='button' class='btn' value='".$text['button-compare']."' onclick=\"document.location.href='cc_report_compare.php';\" />\n";
	echo "							<input type='button' class='btn' value='".$text['button-export']."' onclick=\"toggle_select('export_format');\">\n";
	echo "							<input type='button' class='btn' value='".$text['button-refresh']."' onclick=\"document.location.href='cc_report.php';\" />\n";
	echo "						</td>\n";
	echo "						<td style='vertical-align: top;'>\n";
	echo "							<select class='formfld' style='display: none; width: auto; margin-left: 3px;' name='export_format' id='export_format' onchange=\"process_export();\">\n";
	echo "								<option value=''>...</option>\n";
	echo "								<option value='excel'>EXCEL</option>\n";
	echo "								<option value='pdf'>PDF</option>\n";
	echo "								<option value='email'>EMAIL</option>\n";
	echo "							</select>\n";
	echo "						</td>\n";
	echo "					</tr>\n";
	echo "				</table>\n";
	echo "			</form>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "		<td align='left' colspan='2'>\n";
	
	echo  		$text['label-cc-report-description']." \n";

	echo "		<br /><br />\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";
	
	
//basic search of Call Center call records
	echo "<form method='get' action=''>\n";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
			// Column 1
			echo "<td width='35%' style='vertical-align: top;'>\n";

				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";

				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-agent']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left'>\n";
				echo "			<select name='agent' class='formfld'>\n";
				echo "				<option value='all'" . (($agent == 'all') ? " selected='selected'" : null) . ">".$text['label-all']."</option>\n";
								// Load all Agents from the DB
									$sql = "select agent_name from v_call_center_agents ";
									$sql .= "where domain_uuid = '" . $domain_uuid . "'";
									$prep_statement = $db->prepare(check_sql($sql));
									if ($prep_statement) {
										$prep_statement->execute();
										$agents = $prep_statement->fetchAll(PDO::FETCH_ASSOC);
										if (is_array($agents)) {
											foreach($agents as $row) {
												echo "				<option value='" . $row['agent_name'] . "'" . (($agent == $row['agent_name']) ? " selected='selected'" : null) . ">" . $row['agent_name']. "</option>\n";
											}
										}
										unset($prep_statement, $agents, $sql);
									}

				echo "			</select>\n";
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-status']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left'>\n";
				echo "			<select name='status' class='formfld'>\n";
				echo "				<option value='all'" . (($status == 'all') ? " selected='selected'" : null) . ">".$text['label-all']."</option>\n";
				echo "				<option value='answered'" . (($status == 'answered') ? " selected='selected'" : null) . ">" . $text['label-answered']."</option>\n";
				echo "				<option value='missed'" . (($status == 'missed') ? " selected='selected'" : null) . ">" . $text['label-missed']."</option>\n";
				echo "			</select>\n";
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "</table>\n";

			echo "</td>";
			
			// Column 2
			echo "<td width='35%' style='vertical-align: top;'>\n";

				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-date']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left' style='position: relative; min-width: 250px;'>\n";
				echo "			<input type='text' class='formfld datetimepicker' style='min-width: 115px; width: 115px;' name='start_stamp_begin' placeholder='".$text['label-from'] . "' value='$start_stamp_begin'>\n";
				echo "			<input type='text' class='formfld datetimepicker' style='min-width: 115px; width: 115px;' name='start_stamp_end' placeholder='".$text['label-to'] . "' value='$start_stamp_end'>\n";
				echo "		</td>\n";
				echo "	</tr>\n";

				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-number']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left'>\n";
				echo "			<input type='text' class='formfld' name='caller_id' placeholder='".$text['label-cid']."' value='" . $caller_id . "'>\n";
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "</table>\n";

			echo "</td>";			

	echo "</tr>";
	echo "</table>";

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>";
	echo "<td style='padding-top: 8px;' align='left'>";
	echo 	$text['description_search'];
	echo "</td>";
	echo "<td style='padding-top: 8px;' align='right' nowrap>";
	echo "<input type='button' class='btn' value='".$text['button-reset']."' onclick=\"document.location.href='cc_report.php';\">\n";
	echo "<input type='submit' class='btn' name='submit' value='".$text['button-search']."'>\n";

	echo "</td>";
	echo "</tr>";
	echo "</table>";

	echo "</form>";
	echo "<br /><br />";

?>

<!--  Graph UI-->
	<table width='100%'>
		<tr>
			<td style="width:34%;" align='center'>
				<canvas id="maChart"></canvas>	<!-- Missed/Answered Call Chart -->
			</td>
			<td style="width:33%;" align='center'>
				<canvas id="ttChart"></canvas>	<!-- Ring/Talk Time Chart -->
			</td>
			<td style="width:33%;" align='center'>
				<canvas id="atChart"></canvas>	<!-- Average Ring/Talk Time Chart -->
			</td>
		</tr>
	</table>
	<br/><br/>
<!--  End Graph UI-->

<?php

	//***************************	Table Header Start	**************************************//
	echo "<table class='tr_hover' width='100%' cellpadding='0' cellspacing='0' border='0'>\n";
	echo "<tr>\n";
	echo "<th>&nbsp;</th>\n";
	echo th_order_by('caller_id', $text['label-caller_id'], $order_by, $order, null, null, $param);
	echo th_order_by('agent', $text['label-agent_name'], $order_by, $order, null, null, $param);
	echo th_order_by('ring_time', $text['label-ring_time'], $order_by, $order, null, null, $param);
	echo th_order_by('answer_time', $text['label-answer_time'], $order_by, $order, null, null, $param);
	echo th_order_by('call_end', $text['label-call_end'], $order_by, $order, null, null, $param);
	echo th_order_by('duration', $text['label-duration'], $order_by, $order, null, null, $param);
	echo th_order_by('mos_score', $text['label-mos_score'], $order_by, $order, null, null, $param);
	echo th_order_by('call_status', $text['label-finalize'], $order_by, $order, null, null, $param);
	echo th_order_by('hangup_cause', $text['label-hangup_cause'], $order_by, $order, null, null, $param);
	echo "</tr>\n";
	//***************************	Table Header End	**************************************//

//alternate the row style
	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

	//***************************	Table Content Start	**************************************//
	if ($result_count > 0) {

			list($paging_controls, $rows_per_page, $var_3) = paging($num_rows, $param, $rows_per_page); //bottom
			
		//determine if theme images exist
		$theme_image_path = $_SERVER["DOCUMENT_ROOT"]."/themes/".$_SESSION['domain']['template']['name']."/images/";
		$theme_cdr_images_exist = (
			file_exists($theme_image_path."icon_cdr_inbound_answered.png") &&
			file_exists($theme_image_path."icon_cdr_inbound_voicemail.png") &&
			file_exists($theme_image_path."icon_cdr_inbound_cancelled.png") &&
			file_exists($theme_image_path."icon_cdr_inbound_failed.png") &&
			file_exists($theme_image_path."icon_cdr_outbound_answered.png") &&
			file_exists($theme_image_path."icon_cdr_outbound_cancelled.png") &&
			file_exists($theme_image_path."icon_cdr_outbound_failed.png") &&
			file_exists($theme_image_path."icon_cdr_local_answered.png") &&
			file_exists($theme_image_path."icon_cdr_local_voicemail.png") &&
			file_exists($theme_image_path."icon_cdr_local_cancelled.png") &&
			file_exists($theme_image_path."icon_cdr_local_failed.png")
		) ? true : false;
		
		// Call Statistics
		$maData = ['answered' => 0,'missed' => 0];
		$ttData = ['ring' => 0,'talk' => 0];
		
		//print_r($maData['answered']);
		foreach($summary_data as $call_record) {
			
			echo "<tr ".$call_record['tr_link'].">\n";
			
			echo "<td valign='top' class='".$row_style[$c]."'>\n";
			if ($theme_cdr_images_exist) {
				echo "<img src='".PROJECT_PATH."/themes/".$_SESSION['domain']['template']['name']."/images/icon_cdr_inbound"."_".$call_record['call_result'].".png' width='16' style='border: none; cursor: help;' title='".$text['label-'.$call_record['call_result']]."'>\n";
			}else{
				echo "&nbsp;";
			}
			echo "</td>\n";
			
			if($call_record['call_result'] == 'answered'){
				$maData['answered'] += 1;
			}else{
				$maData['missed'] += 1;
			}
			
			$ttData['ring'] += $call_record['hold_time'];
			$ttData['talk'] += $call_record['duration'];
			
			//caller id name
			echo "	<td valign='top' class='".$row_style[$c]."' >".$call_record['caller_id']."</td>\n";
			
			//caller agent name
			echo "	<td valign='top' class='".$row_style[$c]."' >". $call_record['agent_name'] . "&nbsp;</td>\n";
			
			//Ring Time
			echo "	<td valign='top' class='" . $row_style[$c] ."' nowrap='nowrap'>" . gmdate("G:i:s", $call_record['hold_time']) . "</td>\n";
			
			//Pickup
			//echo date('Y-m-d H:i:s',$call_record['start']);
			$start_epoch = ($_SESSION['domain']['time_format']['text'] == '12h') ? date("j M Y g:i:sa", $call_record['start']) : date("j M Y H:i:s", $call_record['start']);
			echo "	<td valign='top' class='" . $row_style[$c] ."' nowrap='nowrap'>" . $start_epoch . "</td>\n";	
			
			//Call End
			$end_epoch = ($_SESSION['domain']['time_format']['text'] == '12h') ? date("j M Y g:i:sa", $call_record['end']) : date("j M Y H:i:s", $call_record['end']);
			echo "	<td valign='top' class='" . $row_style[$c] . "' nowrap='nowrap'>" . $end_epoch . "</td>\n";
			
			//Duration
			echo "	<td valign='top' class='" . $row_style[$c] . "' style='text-align: left;'>" . gmdate("G:i:s", $call_record['duration']) . "</td>\n";
			
			//MOS Score(Mean opinion score)
			echo "	<td valign='top' class='".$row_style[$c]."' ".((strlen($row['rtp_audio_in_mos']) > 0) ? "title='".($call_record['mos'] / 5 * 100)."%'" : null).">".((strlen($call_record['mos']) > 0) ? $call_record['mos'] : "&nbsp;")."</td>\n";
			
			//Termination Status
			echo "	<td valign='top' class='" . $row_style[$c] . "' >" . $call_record['hangup_source'] . "</td>\n";	
			
			//Hangup Cause
			echo "	<td valign='top' class='".$row_style[$c]."' nowrap='nowrap'>".$call_record['hangup_cause']."</td>\n";
			
			echo "</tr>\n";
			
			$c = ($c) ? 0 : 1;
			
		}//end foreach
		//***************************	Table Content End	**************************************//
	} //end if result_count
	echo "</table>\n";
	echo "<br><br>";

/*******************************  GRAPH DATA	**********************************/

	echo "<script language='javascript' type='text/javascript'>\n";
	echo "	var maData = [". $maData['missed'] . "," . $maData['answered'] . "];\n";
	echo "	var rtData = [". $ttData['ring']/60 . "," . $ttData['talk']/60 . "];\n";
	//echo "	var rtData = [". $ttData['ring'] . "," . $ttData['talk'] . "];\n";
	echo "	var artData = [". $ttData['ring']/$result_count . "," . $ttData['talk']/$maData['answered'] . "];\n";
	echo "</script>\n";
	
/*******************************  END GRAPH DATA	**********************************/

	if ($result_count == $rows_per_page) {
		echo $paging_controls;
	}
	unset($result, $result_count);
?>

<!--	Graph JS	--->
	<script language="javascript" type="text/javascript" src="<?php echo PROJECT_PATH; ?>/resources/chartjs/chart.min.js"></script>
	<script type="text/javascript">
		//	Create an MA Chart
		var maChart = new Chart($('#maChart'), {
		    type: 'doughnut',
		    data: {
				labels: ['Missed', 'Answered'],
				datasets: [{
					label: 'Missed/Answered',
					backgroundColor: [
						'rgb(255, 99, 132)',
						'rgb(75, 192, 192)',
					],
					data: maData
				}]
	
			},
		    options: {
				responsive: true,
				legend: {
					position: 'bottom',
				},
				title: {
					display: true,
					text: 'Missed/Answered'
				},
				animation: {
					animateScale: true,
					animateRotate: true
				}
			}
		});
		
		//	Create an TT Chart
		var ttChart = new Chart($('#ttChart'), {
		    type: 'doughnut',
		    data: {
				labels: ['Total Ring Time(m)', 'Total Talk Time(m)'],
				datasets: [{
					label: 'Total Ring/Talk Time',
					backgroundColor: [
						'rgb(255, 205, 86)',
						'rgb(75, 192, 192)',
					],
					data: rtData
				}]
	
			},
		    options: {
				responsive: true,
				legend: {
					position: 'bottom',
				},
				title: {
					display: true,
					text: 'Total Ring/Talk Time'
				},
				animation: {
					animateScale: true,
					animateRotate: true
				}
			}
		});
		
		//	Create an AT Chart
		var atChart = new Chart($('#atChart'), {
		    type: 'doughnut',
		    data: {
				labels: ['Average Ring Time(s)', 'Average Talk Time(s)'],
				datasets: [{
					label: 'Average Ring/Talk Time',
					backgroundColor: [
						'rgb(54, 162, 235)',
						'rgb(75, 192, 192)',
					],
					data: artData
				}]
	
			},
		    options: {
				responsive: true,
				legend: {
					position: 'bottom',
				},
				title: {
					display: true,
					text: 'Average Ring/Talk Time'
				},
				animation: {
					animateScale: true,
					animateRotate: true
				}
			}
		});
		
	</script>
<!--	End Graph JS	--->


<?php
//show the footer
	require_once "resources/footer.php";
?>