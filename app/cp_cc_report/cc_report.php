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
	
	function format_phone_number($num){
		if(preg_match( '/^(\+?[\d]{2,3})?([\d]{2,3})(\d{4})(\d{4})$/', $num,  $matches))
		{
			if($matches[1] != ''){
				$result = $matches[1] . '-' . sprintf("%03d", $matches[2]) . '-' . $matches[3] . '-' . $matches[4];
			}else{
				$result = sprintf("%03d", $matches[2]) . '-' . $matches[3] . '-' . $matches[4];
			}
			return $result;
		}else{
			return $num;
		}
	}
	
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
echo "<div class='action_bar' id='action_bar'>\n";# Start: Action Bar

echo "	<div class='heading'>";# Start: Heading
echo "		<b>".$text['title']."</b>";
echo "	</div>\n";# End: Heading

echo "	<div class='actions'>\n";# Start: Actions
echo "		<form id='frm_export' class='inline' method='post' action='cc_report_export.php'>\n";# Start: Form
echo "			<input type='hidden' id='agent' name='agent' value='".$agent."'>\n";
echo "			<input type='hidden' name='start_stamp_begin' value='".$start_stamp_begin."'>\n";
echo "			<input type='hidden' name='start_stamp_end' value='".$start_stamp_end."'>\n";
echo "			<input type='hidden' id='caller_id' name='caller_id' value='".$caller_id."'>\n";
echo "			<input type='hidden' id='status' name='status' value='".$status."'>\n";
echo button::create(['type'=>'button','label'=>$text['button-compare'],'icon'=>'balance-scale-right','link'=>'cc_report_compare.php']);
echo button::create(['type'=>'button','label'=>$text['button-export'],'icon'=>$_SESSION['theme']['button_icon_export'],'onclick'=>"toggle_select('export_format'); this.blur();"]);
echo button::create(['type'=>'button','label'=>$text['button-refresh'],'icon'=>'sync','link'=>'cc_report.php']);
echo "			<select class='formfld' style='display: none; width: auto; margin-left: 3px;' name='export_format' id='export_format' onchange=\"process_export();\">\n";
echo "				<option value=''>...</option>\n";
echo "				<option value='excel'>EXCEL</option>\n";
echo "				<option value='pdf'>PDF</option>\n";
echo "				<option value='email'>EMAIL</option>\n";
echo "			</select>\n";
if ($paging_controls_mini != '') {
	echo 	"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>";
}
echo "		</form>\n";# End: Form
echo "	</div>\n";# End: Actions 
echo "	<div style='clear: both;'></div>\n";
echo "</div>\n";# End: Action Bar 
	
//basic search of Call Center call records
	echo "<form name='frm' id='frm' method='get' action=''>\n";# Start: Form
	echo "<div class='form_grid'>\n";


	echo "	<div class='form_set'>\n";
	echo "		<div class='label'>\n";
	echo "			".$text['label-date']."\n";
	echo "		</div>\n";
	echo "		<div class='field no-wrap'>\n";
	echo "			<input type='text' class='formfld datetimepicker' data-toggle='datetimepicker' data-target='#start_stamp_begin' onblur=\"$(this).datetimepicker('hide');\" style='min-width: 115px; width: 115px;' name='start_stamp_begin' id='start_stamp_begin' placeholder='".$text['label-from']."' value='".escape($start_stamp_begin)."' autocomplete='off'>\n";
	echo "			<input type='text' class='formfld datetimepicker' data-toggle='datetimepicker' data-target='#start_stamp_end' onblur=\"$(this).datetimepicker('hide');\" style='min-width: 115px; width: 115px;' name='start_stamp_end' id='start_stamp_end' placeholder='".$text['label-to']."' value='".escape($start_stamp_end)."' autocomplete='off'>\n";
	echo "		</div>\n";
	echo "	</div>\n";
	
		
	echo "	<div class='form_set'>\n";
	echo "		<div class='label'>\n";
	echo "			".$text['label-agent']."\n";
	echo "		</div>\n";
	echo "		<div class='field'>\n";
	echo "			<select name='agent' class='formfld'>\n";
	echo "				<option value='all'" . (($agent == 'all') ? " selected='selected'" : null) . ">".$text['label-all']."</option>\n";
					// Load all Agents from the DB
						$sql = "select agent_name,call_center_agent_uuid from v_call_center_agents ";
						$sql .= "where domain_uuid = '" . $domain_uuid . "'";
						$prep_statement = $db->prepare(check_sql($sql));
						if ($prep_statement) {
							$prep_statement->execute();
							$agents = $prep_statement->fetchAll(PDO::FETCH_ASSOC);
							if (is_array($agents)) {
								foreach($agents as $row) {
									echo "				<option value='" . $row['call_center_agent_uuid'] . "'" . (($agent == $row['call_center_agent_uuid']) ? " selected='selected'" : null) . ">" . $row['agent_name']. "</option>\n";
								}
							}
							unset($prep_statement, $agents, $sql);
						}
	echo "			</select>\n";
	echo "		</div>\n";
	echo "	</div>\n";
	
	echo "	<div class='form_set'>\n";
	echo "		<div class='label'>\n";
	echo "			".$text['label-status']."\n";
	echo "		</div>\n";
	echo "		<div class='field'>\n";
	echo "			<select name='status' class='formfld'>\n";
	echo "				<option value='all'" . (($status == 'all') ? " selected='selected'" : null) . ">".$text['label-all']."</option>\n";
	echo "				<option value='answered'" . (($status == 'answered') ? " selected='selected'" : null) . ">" . $text['label-answered']."</option>\n";
	echo "				<option value='missed'" . (($status == 'missed') ? " selected='selected'" : null) . ">" . $text['label-missed']."</option>\n";
	echo "			</select>\n";
	echo "		</div>\n";
	echo "	</div>\n";	
	
	echo "	<div class='form_set'>\n";
	echo "		<div class='label'>\n";
	echo "			".$text['label-number']."\n";
	echo "		</div>\n";
	echo "		<div class='field'>\n";
	echo "			<input type='text' class='formfld' name='caller_id' placeholder='".$text['label-cid']."' value='" . $caller_id . "'>\n";
	echo "		</div>\n";
	echo "	</div>\n";
	echo "</div>\n";

	echo "<div style='float: right; padding-top: 15px; margin-left: 20px; white-space: nowrap;'>";
	echo button::create(['label'=>$text['button-reset'],'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','link'=>'cc_report.php']);
	echo button::create(['label'=>$text['button-search'],'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_save','name'=>'submit','value' => $text['button-search']]);
	echo "</div>\n";
	echo "<div style='font-size: 85%; padding-top: 12px; margin-bottom: 40px;'>".$text['description_search']."</div>\n";

	//echo "<br />\n";
	echo "</form>\n";# End: Form

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
	echo th_order_by('recording',$text['label-recording'], $order_by, $order, null, null, $param);
	echo th_order_by('mos_score', $text['label-mos_score'], $order_by, $order, null, null, $param);
	echo th_order_by('call_status', $text['label-finalize'], $order_by, $order, null, null, $param);
	echo th_order_by('hangup_cause', $text['label-hangup_cause'], $order_by, $order, null, null, $param);
	echo "</tr>\n";
	//***************************	Table Header End	**************************************//

	//alternate the row style
	$x = 0;

	//***************************	Table Content Start	**************************************//
	if ($result_count > 0) {

		//list($paging_controls, $rows_per_page, $var_3) = paging($num_rows, $param, $rows_per_page); //bottom
			
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
			//determine recording properties
			$record_path = $call_record['record_path'];
			$record_name = $call_record['record_name'];
			$record_extension = pathinfo($record_name, PATHINFO_EXTENSION);
			switch ($record_extension) {
				case "wav" : $record_type = "audio/wav"; break;
				case "mp3" : $record_type = "audio/mpeg"; break;
				case "ogg" : $record_type = "audio/ogg"; break;
			}
			
			//set an empty content variable
			$content = '';
			
			$content .= "<tr class='list-row' id='recording_progress_bar_".$call_record['xml_cdr_uuid']."' style='display: none;'><td class='playback_progress_bar_background' style='padding: 0; border-bottom: none; overflow: hidden;' colspan='11'><span class='playback_progress_bar' id='recording_progress_".$call_record['xml_cdr_uuid']."'></span></td></tr>\n";
			$content .= "<tr class='list-row' style='display: none;'><td></td></tr>\n"; // dummy row to maintain alternating background color
					
			$list_row_url = "xml_cdr_details.php?id=" . urlencode($row['xml_cdr_uuid']);
			$content .= "<tr class='list-row' href='" . $list_row_url . "'>\n";
			
			$content .=  "<td class='middle'>\n";
			if ($theme_cdr_images_exist) {
				$content .=  "<img src='".PROJECT_PATH."/themes/".$_SESSION['domain']['template']['name']."/images/icon_cdr_inbound"."_".$call_record['call_result'].".png' width='16' style='border: none; cursor: help;' title='".$text['label-'.$call_record['call_result']]."'>\n";
			}else{
				$content .=  "&nbsp;";
			}
			
			$content .=  "</td>\n";
			
			if($call_record['call_result'] == 'answered'){
				$maData['answered'] += 1;
			}else{
				$maData['missed'] += 1;
			}
			
			$ttData['ring'] += $call_record['hold_time'];
			$ttData['talk'] += $call_record['duration'];
			
			//caller id name
			$content .=  "	<td class='middle'>".format_phone_number($call_record['caller_id'])."</td>\n";
			
			//caller agent name
			$content .=  "	<td class='middle'>". $call_record['agent_name'] . "&nbsp;</td>\n";
			
			//Ring Time
			$content .=  "	<td class='middle' nowrap='nowrap'>" . gmdate("G:i:s", $call_record['hold_time']) . "</td>\n";
			
			//Pickup
			//echo date('Y-m-d H:i:s',$call_record['start']);
			$start_epoch = ($_SESSION['domain']['time_format']['text'] == '12h') ? date("j M Y g:i:sa", $call_record['start']) : date("j M Y H:i:s", $call_record['start']);
			$content .=  "	<td class='middle' nowrap='nowrap'>" . $start_epoch . "</td>\n";	
			
			//Call End
			$end_epoch = ($_SESSION['domain']['time_format']['text'] == '12h') ? date("j M Y g:i:sa", $call_record['end']) : date("j M Y H:i:s", $call_record['end']);
			$content .=  "	<td class='middle' nowrap='nowrap'>" . $end_epoch . "</td>\n";
			
			//Duration
			$content .=  "	<td class='middle' style='text-align: left;'>" . gmdate("G:i:s", $call_record['duration']) . "</td>\n";
			
			//recording
			if ($record_path != '') {
				$content .=  "	<td class='middle button center " . $row_style[$c] .  " no-link no-wrap'>";
				if (permission_exists('xml_cdr_recording_play')) {
					$content .=  "<audio id='recording_audio_" . escape($call_record['xml_cdr_uuid']) . "' style='display: none;' preload='none' ontimeupdate=\"update_progress('" . escape($call_record['xml_cdr_uuid']) . "')\" onended=\"recording_reset('".escape($call_record['xml_cdr_uuid'])."');\" src=\"../xml_cdr/download.php?id=".escape($call_record['xml_cdr_uuid'])."&t=record\" type='".escape($record_type)."'></audio>";
					$content .=  button::create(['type'=>'button','title'=>$text['label-play'].' / '.$text['label-pause'],'icon'=>$_SESSION['theme']['button_icon_play'],'id'=>'recording_button_'.escape($call_record['xml_cdr_uuid']),'onclick'=>"recording_play('".escape($call_record['xml_cdr_uuid'])."')"]);
				}
				$content .=  "</td>\n";
			}
			else {
				$content .= "	<td>&nbsp;</td>\n";
			}
					
			//MOS Score(Mean opinion score)
			$content .=  "	<td valign='top' class='middle' ".((strlen($call_record['rtp_audio_in_mos']) > 0) ? "title='".($call_record['mos'] / 5 * 100)."%'" : null).">".((strlen($call_record['mos']) > 0) ? $call_record['mos'] : "&nbsp;")."</td>\n";
			
			//Termination Status
			$content .=  "	<td class='middle'>" . $call_record['hangup_source'] . "</td>\n";	
			
			//Hangup Cause
			$content .=  "	<td class='middle' nowrap='nowrap'>".$call_record['hangup_cause']."</td>\n";
			
			$content .=  "</tr>\n";
			
			echo $content;
			$x++;
			
		}//end foreach
		//***************************	Table Content End	**************************************//
	} //end if result_count
	echo "</table>\n";
	echo "<br><br>";
	echo "<div align='center'>".$paging_controls."</div>\n";

/*******************************  GRAPH DATA	**********************************/

	echo "<script language='javascript' type='text/javascript'>\n";
	echo "	var maData = [". $maData['missed'] . "," . $maData['answered'] . "];\n";
	echo "	var rtData = [". $ttData['ring']/60 . "," . $ttData['talk']/60 . "];\n";
	//echo "	var rtData = [". $ttData['ring'] . "," . $ttData['talk'] . "];\n";
	echo "	var artData = [". (($ttData['ring'])?($ttData['ring']/$result_count) : 0) . "," . (($maData['answered']) ? ($ttData['talk']/$maData['answered']) : $maData['answered']) . "];\n";
	echo "</script>\n";
	
/*******************************  END GRAPH DATA	**********************************/
	
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