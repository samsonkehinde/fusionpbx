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
		$start_date = check_str($_REQUEST["start_date"]);
		$end_date = check_str($_REQUEST["end_date"]);
	}

	/******************** Prepare SQL Query ******************/
	$end_date2 = new DateTime($end_date);
	$end_date2 = $end_date2->modify( '+1 day' );
	$period = new DatePeriod(new DateTime($start_date),new DateInterval('P1D'),$end_date2);
	$sum_data = [];
	
	/************	 Get the Call Summary for each date in the range selected	**************/
    foreach ($period as $date) {
    	// Set the defaults for each day.
    	$sum_data[$date->format("Y-m-d")] = ['active_agents' => 0,'answer_missed'=>['answered' => 0, 'missed' => 0],'duration'=>['ring_time' => 0, 'talk_time' => 0],'avg_duration'=>['ring_time' => 0, 'talk_time' => 0]];

	    /*************	Get the Call Summary Data	**************/
	    $sql = "select duration,waitsec,cc_agent_bridged,cc_cause,billsec,sip_hangup_disposition,rtp_audio_in_mos from v_xml_cdr where cc_side = 'member' and direction = 'inbound' and domain_uuid = '". $domain_uuid . "' ";
		$sql .= " and start_stamp BETWEEN '" . $date->format("Y-m-d") . " 00:00:00.000' AND '" . $date->format("Y-m-d") . " 23:59:59.999'";
		//echo $sql;
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll(PDO::FETCH_ASSOC);
	    $result_count = $prep_statement->rowCount();
	    unset ($prep_statement, $sql);
	    
	    if ($result_count > 0) {
	    	foreach($result as $row) {
	    		if ($row['cc_agent_bridged'] == true && $row['cc_cause'] == 'answered') { 
					$call_result = 'answered';
				}else {
					$call_result = 'failed';
				}
				
				// Set the Talk/Ring Time
				if($call_result == 'answered'){
					$sum_data[$date->format("Y-m-d")]['answer_missed']['answered'] += 1;
					$waittime = $row['waitsec'];
					$talktime = $row['billsec'] - $row['waitsec'];
				}else{
					$waittime = $row['duration'];
					$talktime = 0;
					$sum_data[$date->format("Y-m-d")]['answer_missed']['missed'] += 1;
				}
				$sum_data[$date->format("Y-m-d")]['duration']['ring_time'] += $waittime;
				$sum_data[$date->format("Y-m-d")]['duration']['talk_time'] += $talktime;	
	    	}
	    }
	    
	    //	Compute the Average Ringtime and Talktime.
	    $sum_data[$date->format("Y-m-d")]['avg_duration']['ring_time'] = ($result_count)?number_format($sum_data[$date->format("Y-m-d")]['duration']['ring_time'] / $result_count,2) : 0;
	    $sum_data[$date->format("Y-m-d")]['avg_duration']['talk_time'] = ($result_count)?number_format($sum_data[$date->format("Y-m-d")]['duration']['talk_time'] / $result_count,2) : 0;
	    
	    // Convert the Total ringtime/talktime to minutes.
	    $sum_data[$date->format("Y-m-d")]['duration']['ring_time'] = ($sum_data[$date->format("Y-m-d")]['duration']['ring_time'])?number_format($sum_data[$date->format("Y-m-d")]['duration']['ring_time'] / 60,2) : 0;
	    $sum_data[$date->format("Y-m-d")]['duration']['talk_time'] = ($sum_data[$date->format("Y-m-d")]['duration']['talk_time'])?number_format($sum_data[$date->format("Y-m-d")]['duration']['talk_time'] / 60,2) : 0;
	    
	    unset($result, $result_count);
	    
	    /*************	Get Action Agents	**************/
	    $sql = "select count (distinct destination_number) as active_agents from v_xml_cdr where cc_side = 'member' and direction = 'inbound' and domain_uuid = '". $domain_uuid . "' ";
		$sql .= " and start_stamp BETWEEN '" . $date->format("Y-m-d") . " 00:00:00.000' AND '" . $date->format("Y-m-d") . " 23:59:59.999'";
		//echo $sql;
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
	    $sum_data[$date->format("Y-m-d")]['active_agents'] = $row['active_agents'];
	    unset ($prep_statement, $sql);

	}
	
?>