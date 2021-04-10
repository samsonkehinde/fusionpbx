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
	require_once "cc_report_compare_inc.php";
	
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
	echo "			display_message('".$text['message-processing']."','default',10);\n";
	echo "			$.ajax({\n"; 
    echo "				type: 'POST',\n";
    echo "				url: 'cc_report_compare_export.php',\n";
    echo "				data: {\n";
    echo "					start_date: document.getElementById('start_date').value,\n";
    echo "					end_date: document.getElementById('end_date').value,\n";
    echo "					report_recipient: recpt,\n";
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
	echo "			<form id='frm_export' method='post' action='cc_report_compare_export.php'>\n";
	echo "				<input type='hidden' id='start_date' name='start_date' value='$start_date'\>\n";
	echo "				<input type='hidden' id='end_date' name='end_date' value='$end_date'\>\n";
	echo "				<table cellpadding='0' cellspacing='0' border='0'>\n";
	echo "					<tr>\n";
	echo "						<td style='vertical-align: top;'>\n";
	echo "							<input type='button' class='btn' value='".$text['button-export']."' onclick=\"toggle_select('export_format');\">\n";
	echo "							<input type='button' class='btn' value='".$text['button-back']."' onclick=\"document.location.href='cc_report.php';\" />\n";
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
	echo  		$text['label-cc-report-compare-description']." \n";
	echo "		<br /><br />\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";

	
	
//basic search of Call Center call records
	echo "<form method='get' action=''>\n";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
			echo "<td width='35%' style='vertical-align: top;'>\n";
				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-date']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left' style='position: relative; min-width: 250px;'>\n";
				echo "			<input type='text' class='formfld datepicker' style='min-width: 115px; width: 115px;' name='start_date' placeholder='".$text['label-from'] . "' value='$start_date'/>\n";
				echo "			<input type='text' class='formfld datepicker' style='min-width: 115px; width: 115px;' name='end_date' placeholder='".$text['label-to'] . "' value='$end_date'/>\n";
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "</table>\n";
			echo "</td>";			

	echo "	</tr>";
	echo "</table>";

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>";
	echo "		<td style='padding-top: 8px;' align='left'>";
	echo 			$text['description_compare'];
	echo "		</td>";
	echo "		<td style='padding-top: 8px;' align='right' nowrap>";
	echo "			<input type='button' class='btn' value='".$text['button-reset']."' onclick=\"document.location.href='cc_report_compare.php';\">\n";
	echo "			<input type='submit' class='btn' name='submit' value='".$text['button-submit']."'>\n";
	echo "		</td>";
	echo "	</tr>";
	echo "</table>";

	echo "</form>";
	echo "<br /><br />";

?>

<!--  Graph UI-->
	<table width='100%'>
		<tr>
			<td style="width:100%;" align='center'>
				<canvas id="compareChart"></canvas>	<!-- Compare Calls Chart -->
			</td>
		</tr>
	</table>
	<br/><br/>
<!--  End Graph UI-->


<?php
	
	//print_r($sum_data);
	/*******************************  GRAPH DATA	**********************************/

	echo "<script language='javascript' type='text/javascript'>\n";
	echo "	var graphLabels = ". json_encode(array_keys($sum_data)) . ";\n";
	echo "	var plotData = []; i=0;\n";
	echo "	for(i=0; i<7; i++)\n";
	echo "		plotData[i] = [];\n\n";
	foreach ($sum_data as $value) {
		echo "	plotData[0].push(" . $value['answer_missed']['missed'] . ");\n";
		echo "	plotData[1].push(" . $value['answer_missed']['answered'] . ");\n";
		echo "	plotData[2].push(" . $value['rt']['ring_time'] . ");\n";
		echo "	plotData[3].push(" . $value['rt']['talk_time'] . ");\n";
		echo "	plotData[4].push(" . $value['avg_rt']['ring_time'] . ");\n";
		echo "	plotData[5].push(" . $value['avg_rt']['talk_time'] . ");\n";
		echo "	plotData[6].push(" . $value['active_agents'] . ");\n";
		//unset($value);
	}
	echo "</script>\n";
	
/*******************************  END GRAPH DATA	**********************************/

//alternate the row style
	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";
/******************************* SUMMARY TABLE	*************************************/

	//	Table Header Start	//
	echo "<table class='tr_hover' width='100%' cellpadding='0' cellspacing='0' border='0'>\n";
	echo "<tr>\n";
	echo th_order_by('date', $text['label-summary_date'], $order_by, $order, null, null, $param);
	echo th_order_by('answered', $text['label-summary_answered'], $order_by, $order, null, null, $param);
	echo th_order_by('missed', $text['label-summary_missed'], $order_by, $order, null, null, $param);
	echo th_order_by('ring_time', $text['label-summary_ring_time'], $order_by, $order, null, null, $param);
	echo th_order_by('talk_time', $text['label-summary_talk_time'], $order_by, $order, null, null, $param);
	echo th_order_by('avg_ring_time', $text['label-summary_avg_ring_time'], $order_by, $order, null, null, $param);
	echo th_order_by('avg_talk_time', $text['label-summary_avg_talk_time'], $order_by, $order, null, null, $param);
	echo th_order_by('active_agents', $text['label-summary_active_agents'], $order_by, $order, null, null, $param);
	echo "</tr>\n";
	//	Table Header End	//
	foreach ($sum_data as $key => $value) {
		//print_r($value);
	//	Table Content Start	//
		echo "<tr>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' >". $key ."</td>\n";		
		echo "	<td valign='top' class='".$row_style[$c]."' >". $value['answer_missed']['answered'] ."</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' >". $value['answer_missed']['missed'] ."</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' >". gmdate("G:i:s", $value['rt']['ring_time']) ."</td>\n";	
		echo "	<td valign='top' class='".$row_style[$c]."' >". gmdate("G:i:s", $value['rt']['talk_time']) ."</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' >". gmdate("G:i:s", $value['avg_rt']['ring_time']) ."</td>\n";	
		echo "	<td valign='top' class='".$row_style[$c]."' >". gmdate("G:i:s", $value['avg_rt']['talk_time']) ."</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' >". $value['active_agents'] ."</td>\n";
		
		echo "</tr>\n";
		$c = ($c) ? 0 : 1;	
	}//end foreach
	// Table Content End	//
	echo "</table>\n";
	echo "<br><br>";
/******************************* END SUMMARY TABLE	*************************************/

?>

<!--	Graph JS	--->
	<script language="javascript" type="text/javascript" src="<?php echo PROJECT_PATH; ?>/resources/chartjs/chart.min.js"></script>
	<script type="text/javascript">
		//	Create an Comparison Chart
		var maChart = new Chart($('#compareChart'), {
		    type: 'bar',
		    data: {
				labels: graphLabels,
				datasets: [
					{
						label: 'Missed Calls',
						backgroundColor: 'rgb(255, 99, 132)',
						data: plotData[0],
						stack: 'Stack 0'
					},
					{
						label: 'Answered Calls',
						backgroundColor: 'rgb(75, 192, 192)',
						data: plotData[1],
						stack: 'Stack 0'
					},
					{
						label: 'Total Ring Time(m)',
						backgroundColor: 'rgb(54, 162, 235)',
						data: plotData[2],
						stack: 'Stack 1'
					},
					{
						label: 'Total Talk Time(m)',
						backgroundColor: 'rgb(153, 102, 255)',
						data: plotData[3],
						stack: 'Stack 1'
					},
					{
						label: 'Average Ring Time(s)',
						backgroundColor: 'rgb(255, 205, 86)',
						data: plotData[4],
						stack: 'Stack 2'
					},
					{
						label: 'Average Talk Time(s)',
						backgroundColor: 'rgb(255, 159, 64)',
						data: plotData[5],
						stack: 'Stack 2'
					},
					{
						label: 'Active Agents',
						backgroundColor: 'rgb(224, 133, 133)',
						data: plotData[6],
						stack: 'Stack 3'
					},
					/*{
						label: 'Total Agents',
						backgroundColor: ['rgb(113, 218, 113)'],
						data: [13],
						stack: 'Stack 3'
					}*/
				]
	
			},
		    options: {
				responsive: true,
				title: {
					display: true,
					text: 'Call Summary'
				},
				animation: {
					animateScale: true
				},
				scales: {
					xAxes: [{
						stacked: true,
					}],
					yAxes: [{
						stacked: true
					}]
				}
			}
		});
		
	</script>
<!--	End Graph JS	--->


<?php
	//show the footer
	require_once "resources/footer.php";
?>