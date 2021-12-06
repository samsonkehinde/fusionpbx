<?php

//Check Permission
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


// Save our previous search term.
	if (count($_REQUEST) > 0) {
		$agent = check_str($_REQUEST["agent"]);
		$start_stamp_begin = check_str($_REQUEST["start_stamp_begin"]);
		$start_stamp_end = check_str($_REQUEST["start_stamp_end"]);
		$caller_id = check_str($_REQUEST["caller_id"]);
		$status = check_str($_REQUEST["status"]);
	}
	
	//set the param variable which is used with paging
	$param .= "&caller_id=".$caller_id;
	$param .= "&start_stamp_begin=".$start_stamp_begin;
	$param .= "&start_stamp_end=".$start_stamp_end;
	$param .= "&status=".$status;
	$param .= "&agent=".$agent;
	
	/******************** Prepare SQL Query ******************/
	
	// Agent Filter
	if (strlen($agent) > 0 && $agent != "all") {
		$sql_where_ands[] = "cc_agent like '" . "%".$agent."%" ."'";
	}
	
	// Status Filter
	if (strlen($status) > 0) {
		switch ($status) {
			case 'answered':
				//$sql_where_ands[] = "(answer_stamp is not null and bridge_uuid is not null)";
				$sql_where_ands[] = "(cc_agent_bridged = 'true' and cc_cause='answered' and cc_agent is not null)";
				break;
			case 'missed':
				//$sql_where_ands[] = "(answer_stamp is not null and bridge_uuid is null)";
				$sql_where_ands[] = "(cc_agent_bridged is null and cc_cause='cancel' and cc_agent is null)";
				break;
			default: // all
		}
	}	
	
	// Caller ID Filter
	if (strlen($caller_id) > 0) {
		$mod_caller_id = str_replace("*", "%", $caller_id);
		$sql_where_ands[] = "caller_id_number like '".$mod_caller_id."'";
	}

	// Date Range Filter
	if (strlen($start_stamp_begin) > 0 && strlen($start_stamp_end) > 0) {
		$sql_where_ands[] = "start_stamp BETWEEN '" . $start_stamp_begin . ":00.000' AND '" . $start_stamp_end.":59.999'";
	}
	else {
		if (strlen($start_stamp_begin) > 0) {
			$sql_where_ands[] = "start_stamp >= '".$start_stamp_begin.":00.000'";
		}
		if (strlen($start_stamp_end) > 0) {
			$sql_where_ands[] = "start_stamp <= '".$start_stamp_end.":59.999'";
		}
	}
	
	// concatenate the 'ands's array, add to where clause
	if (sizeof($sql_where_ands) > 0) {
		$sql_where = " and ".implode(" and ", $sql_where_ands);
	}
	
	$sql_where_ands[] = "cc_side = 'member'";
	$sql_where_ands[] = "direction = 'inbound'";
	#$sql_where_ands[] = "last_app = 'callcenter'";
	
	// concatenate the 'ands's array, add to where clause
	if (sizeof($sql_where_ands) > 0) {
		$sql_where = " and ".implode(" and ", $sql_where_ands);
	}
	
	$sql[] = "select direction,(xml IS NOT NULL OR json IS NOT NULL) AS raw_data_exists,cc_cause,start_epoch,answer_stamp,end_epoch,hangup_cause,duration,billmsec,waitsec,bridge_uuid,billsec,cc_agent_bridged,caller_id_number,destination_number,sip_hangup_disposition,rtp_audio_in_mos, record_path, xml_cdr_uuid from v_xml_cdr where domain_uuid = :domain_uuid ";

	$sql[] = "select count(*) as num_rows from v_xml_cdr where domain_uuid = :domain_uuid ";

	$sql[0] .= $sql_where;
	$sql[1] .= $sql_where;
	
	if (strlen($order_by) > 0) { $sql[0] .= " order by " . $order_by." " . $order . " "; }
	
	//Disable the paging for exports
	if ($_REQUEST['export_format'] != "") { $rows_per_page = 0; }
	
	//prepare paging controls
	$page = $_GET['page'];
	if (strlen($page) == 0) 
	{ 
		$page = 0; $_GET['page'] = 0;
	}
	
	$offset = $rows_per_page * $page;
	
	if ($_REQUEST['export_format'] == "") {
		if ($rows_per_page == 0) {
			$sql[0] .= " limit " . $_SESSION['cdr']['limit']['numeric'] . " offset 0 ";
		}
		else {
			$sql[0] .= " limit " . $rows_per_page . " offset " . $offset . " ";
		}
	}
	
	$sql[0] = str_replace("  ", " ", $sql[0]);
	$sql[1] = str_replace("  ", " ", $sql[1]);
	$sql[0] = str_replace("where and", "where", $sql[0]);
	$sql[1] = str_replace("where and", "where", $sql[1]);
	//echo $domain_uuid;
	// Fetch total number of records
	$database = new database;
	$parameters['domain_uuid'] = $domain_uuid;
	$num_rows = $database->select($sql[1], $parameters, 'column');
	
	// Fetch Call Records
	//echo $sql[0];
	$prep_statement = $db->prepare(check_sql($sql[0]));
	$prep_statement->execute($parameters);
	$result = $prep_statement->fetchAll(PDO::FETCH_ASSOC);
	$result_count = $prep_statement->rowCount();
	
	// Process results
	foreach($result as $row) {
		$hangup_cause = $row['hangup_cause'];
		$hangup_cause = str_replace("_", " ", $hangup_cause);
		$hangup_cause = strtolower($hangup_cause);
		$hangup_cause = ucwords($hangup_cause);
	
		if($row['sip_hangup_disposition'] == 'send_bye'){$hangup_source = 'Agent Hungup';}
		if($row['sip_hangup_disposition'] == 'recv_bye'){
			$hangup_source = 'Caller Hungup';
		}
		else {
			$tr_link = null;
		}
		
			if ($row['direction'] == 'inbound') {
				if ($row['cc_agent_bridged'] == true && $row['cc_cause'] == 'answered') { 
					$call_result = 'answered';
				}
				else if ($row['cc_agent_bridged'] == '' && $row['cc_cause'] == 'cancel') {
					$call_result = 'voicemail';
				}
				else {
					$call_result = 'failed';
				}
			}

			$icon = "<img src='".PROJECT_PATH."/themes/".$_SESSION['domain']['template']['name']."/images/icon_cdr_".$row['direction']."_".$call_result.".png' width='16' style='border: none; cursor: help;' title='".$text['label-'.$call_result]."'>\n";
			
			// Set the Talk/Ring Time
			if($call_result == 'answered'){
				$waittime = $row['waitsec'];
				$talktime = $row['billsec'] - $waittime;
			}else{
				$waittime = $row['duration'];
				$talktime = 0;
			}
			
			$summary_data[] = [
				'call_result' => $call_result, 
				'caller_id' => $row['caller_id_number'],
				'agent_name' => ($row['bridge_uuid'] != '') ? $row['destination_number'] : '',
				'hold_time' => $waittime,
				'start' => $row['start_epoch'],
				'end' => $row['end_epoch'],
				'duration' => $talktime,
				'mos' => $row['rtp_audio_in_mos'],
				'hangup_source' => $hangup_source,
				'hangup_cause' => $hangup_cause,
				'record_path' => $row['record_path'],
				'xml_cdr_uuid' => $row['xml_cdr_uuid']
			];
		
	}//end foreach
	
	unset ($prep_statement, $result, $sql);
	
	//return the paging
	if ($_REQUEST['export_format'] !== "excel" && $_REQUEST['export_format'] !== "pdf") {
		list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true, $result_count); //top
		list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page, false, $result_count); //bottom
	}
?>