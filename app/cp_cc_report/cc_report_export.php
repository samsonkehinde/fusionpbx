<?php

include "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";

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

//additional includes
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	require_once "cc_report_inc.php";

//get the format
	$export_format = check_str($_REQUEST['export_format']);



//exprot the csv
	if ($export_format == 'excel') {
		require_once 'resources/php_xlswriter/xlsxwriter.class.php';
		
		$writer = new XLSXWriter();
		$writer->setAuthor('Samson Kehinde');
		$writer->setCompany('Cloud Practice Limited');
		$writer->setTitle('Call Center Report');
		
		$header = array(
		  $text['label-call_status'] =>'string',//text
		  $text['label-caller_id'] => '@',//text
		  $text['label-agent_name'] =>'string',
		  $text['label-ring_time'] => '@',
		  $text['label-answer_time'] =>'DD-MM-YYYY HH:MM:SS',
		  $text['label-call_end'] => 'DD-MM-YYYY HH:MM:SS',
		  $text['label-duration'] =>'@',
		  $text['label-call_quality'] =>'0%',
		  $text['label-finalize'] => 'string',
		  $text['label-hangup_cause'] => 'string'
		);
		$writer->writeSheetHeader('CallCenter Data', $header);

		$filename = "cdr_".date("Ymd_His").".xlsx";

		foreach($summary_data as $call_record) {
			$writer->writeSheetRow('CallCenter Data',
				[
					($call_record['call_result'] == 'answered') ? 'Answered' : 'Missed',
					$call_record['caller_id'],
					$call_record['agent_name'],
					gmdate("H:i:s",$call_record['hold_time']),
					date('Y-m-d H:i:s',$call_record['start']),
					date('Y-m-d H:i:s',$call_record['end']),
					gmdate("H:i:s",$call_record['duration']),
					(strlen($call_record['mos']) > 0) ? ($call_record['mos'] / 5) : null,
					$call_record['hangup_source'],
					$call_record['hangup_cause']
				]
			);
		}//End ForEach
		
		header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
		header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		
		$writer->writeToStdOut();
	}else {
		//load pdf libraries
		require_once("resources/tcpdf/tcpdf.php");
		require_once("resources/fpdi/fpdi.php");
	
		//determine page size
		switch ($_SESSION['fax']['page_size']['text']) {
			case 'a4' :
				$page_width = 11.7; //in
				$page_height = 8.3; //in
				break;
			case 'legal' :
				$page_width = 14; //in
				$page_height = 8.5; //in
				break;
			case 'letter' :
			default	:
				$page_width = 11; //in
				$page_height = 8.5; //in
		}
	
		// initialize pdf
		$pdf = new FPDI('L', 'in');
		$pdf -> SetAutoPageBreak(false);
		$pdf -> setPrintHeader(false);
		$pdf -> setPrintFooter(false);
		$pdf -> SetMargins(0.5, 0.5, 0.5, true);
	
		//set default font
		$pdf -> SetFont('helvetica', '', 7);
		//add new page
		$pdf -> AddPage('L', array($page_width, $page_height));
	
		//write the table column headers
		$data_start = '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
		$data_end = '</table>';
	
		$data_head = '<tr>';
		$data_head .= '<td width="9%"><b>'.$text['label-call_status'].'</b></td>';
		$data_head .= '<td width="11%"><b>'.$text['label-caller_id'].'</b></td>';
		$data_head .= '<td width="8%"><b>'.$text['label-agent_name'].'</b></td>';
		$data_head .= '<td width="8%"><b>'.$text['label-ring_time'].'</b></td>';
		$data_head .= '<td width="15%"><b>'.$text['label-answer_time'].'</b></td>';
		$data_head .= '<td width="15%"><b>'.$text['label-call_end'].'</b></td>';
		$data_head .= '<td width="8%"><b>'.$text['label-duration'].'</b></td>';
		$data_head .= '<td width="6%"><b>'.$text['label-call_quality'].'</b></td>';
		$data_head .= '<td width="10%"><b>'.$text['label-finalize'].'</b></td>';
		$data_head .= '<td width="10%"><b>'.$text['label-hangup_cause'].'</b></td>';
		$data_head .= '</tr>';
		$data_head .= '<tr><td colspan="10"><hr></td></tr>';
	
		//initialize total variables
		$total['ring_time'] = 0;
		$total['talk_time'] = 0;
		$total['mos'] = 0;
		
		$z = 0; // total counter
		$p = 0; // per page counter
		
		//write the row cells
		if (sizeof($summary_data) > 0) {
			foreach($summary_data as $call_record) {
				$data_body[$p] .= '<tr>';
				
				$data_body[$p] .= '<td width="9%">'.(($call_record['call_result'] == 'answered') ? 'Answered' : 'Missed').'</td>';
				$data_body[$p] .= '<td width="11%">'.$call_record['caller_id'].'</td>';
				$data_body[$p] .= '<td width="8%">'.$call_record['agent_name'].'</td>';
				$total['ring_time'] += $call_record['hold_time'];
				$data_body[$p] .= '<td width="8%">'.gmdate("G:i:s",$call_record['hold_time']).'</td>';
				$data_body[$p] .= '<td width="15%">'.date('Y-m-d H:i:s',$call_record['start']).'</td>';
				$data_body[$p] .= '<td width="15%">'.date('Y-m-d H:i:s',$call_record['end']).'</td>';
				$total['talk_time'] += $call_record['duration'];
				$data_body[$p] .= '<td width="8%">'.gmdate("G:i:s",$call_record['duration']).'</td>';
				$total['mos'] += (strlen($call_record['mos']) > 0) ? ($call_record['mos'] / 5 * 100) : 0;
				$data_body[$p] .= '<td width="6%">'.((strlen($call_record['mos']) > 0) ? (($call_record['mos'] / 5 * 100) . '%') : '--').'</td>';
				$data_body[$p] .= '<td width="10%">'.$call_record['hangup_source'].'</td>';
				$data_body[$p] .= '<td width="10%">'.$call_record['hangup_cause'].'</td>';
				
				$data_body[$p] .= '</tr>';
				
				$z++;
				$p++;
				
				if ($p == 60) {
					//output data
					$data_body_chunk = $data_start.$data_head;
					foreach ($data_body as $data_body_row) {
						$data_body_chunk .= $data_body_row;
					}
					$data_body_chunk .= $data_end;
					$pdf -> writeHTML($data_body_chunk, true, false, false, false, '');
					unset($data_body_chunk, $data_body);
					$p = 0;
	
					//add new page
					$pdf -> AddPage('L', array($page_width, $page_height));
				}

			}
		}
		
		//write divider
		$data_footer = '<tr><td colspan="10"></td></tr>';
		
		//write totals
		$data_footer .= '<tr>';
		$data_footer .= '<td><b>'.$text['label-total'].'</b></td>';
		$data_footer .= '<td>'.$z.'</td>';
		$data_footer .= '<td colspan="1"></td>';
		$data_footer .= '<td><b>'.gmdate("G:i:s", $total['ring_time']).'</b></td>';
		$data_footer .= '<td colspan="2"></td>';
		$data_footer .= '<td><b>'.gmdate("G:i:s", $total['talk_time']).'</b></td>';
		$data_footer .= '<td colspan="2"></td>';
		$data_footer .= '</tr>';
	
		//write divider
		$data_footer .= '<tr><td colspan="10"><hr></td></tr>';
	
		//write averages
		$data_footer .= '<tr>';
		$data_footer .= '<td><b>'.$text['label-average'].'</b></td>';
		$data_footer .= '<td colspan="2"></td>';
		$data_footer .= '<td><b>'.gmdate("G:i:s", $total['ring_time']/$z).'</b></td>';
		$data_footer .= '<td colspan="2"></td>';
		$data_footer .= '<td><b>'.gmdate("G:i:s", $total['talk_time']/$z).'</b></td>';
		$data_footer .= '<td><b>'.number_format($total['mos']/$z, 2) ."%".'</b></td>';
		$data_footer .= '<td colspan="1"></td>';
		$data_footer .= '</tr>';
		
		//write divider
		$data_footer .= '<tr><td colspan="10"><hr></td></tr>';
	
		//add last page
		if ($p >= 55) {
			$pdf -> AddPage('L', array($page_width, $page_height));
		}
		//output remaining data
		$data_body_chunk = $data_start.$data_head;
		foreach ($data_body as $data_body_row) {
			$data_body_chunk .= $data_body_row;
		}
		$data_body_chunk .= $data_footer.$data_end;
		$pdf -> writeHTML($data_body_chunk, true, false, false, false, '');
		unset($data_body_chunk);
	
		//define file name
		$pdf_filename = "cdr_".$_SESSION['domain_name']."_".date("Ymd_His").".pdf";
	
		// Create a PDF file.
		if($export_format == 'email'){// Create an Email and attach the exported PDF to the email and send.
			include "resources/phpmailer/class.phpmailer.php";
			include "resources/phpmailer/class.smtp.php";
			$mail = new PHPMailer(true);
			
			try {
				
				// load default smtp settings
				$mail->Host = (strlen($_SESSION['email']['smtp_host']['var'])?$_SESSION['email']['smtp_host']['var']:'127.0.0.1');
				
				if (isset($_SESSION['email']['smtp_port'])) {
					$mail->Port = (int)$_SESSION['email']['smtp_port']['numeric'];
				} else {
					$mail->Port = 587;
				}
				
				if (isset($_SESSION['email']['method'])) {
					switch($_SESSION['email']['method']['text']) {
						case 'sendmail': $mail->IsSendmail(); break;
						case 'qmail': $mail->IsQmail(); break;
						case 'mail': $mail->IsMail(); break;
						default: $mail->IsSMTP(); break;
					}
					
				} else{
					$mail->IsSMTP();
				}
	
				$mail->SMTPAuth = $_SESSION['email']['smtp_auth']['var'];
				$mail->Username = $_SESSION['email']['smtp_username']['var'];
				$mail->Password = $_SESSION['email']['smtp_password']['var'];
				$smtp_from 	= (strlen($_SESSION['email']['smtp_from']['var'])?$_SESSION['email']['smtp_from']['var']:'fusionpbx@example.com');
				$smtp_from_name = (strlen($_SESSION['email']['smtp_from_name']['var'])?$_SESSION['email']['smtp_from_name']['var']:'FusionPBX Voicemail');
			    $mail->setFrom($smtp_from, $smtp_from_name);
				$mail->SMTPDebug  = 0;
				
				if ($_SESSION['email']['smtp_secure']['var'] != '') {
					$mail -> SMTPSecure = $_SESSION['email']['smtp_secure']['var'];
				}
				
			    $mail->addAddress(check_str($_REQUEST["report_recipient"]));               		//Name is optional
			
			    //Attachments
			    $mail->AddStringAttachment($pdf -> Output($pdf_filename, 'S'), $pdf_filename,'base64','application/pdf');    //Optional name
			
			    //Content
			    $mail->isHTML(true);                                  //Set email format to HTML
			    $mail->Subject = $text['label-report_subject'];
			    $mail->Body    = $text['label-report_body'];
			
			    $mail->send();
			    echo json_encode(['message' => $text['label-report_send_failed']]);
			} catch (Exception $e) {
				// Log Error to the database.
				http_response_code(500);
				echo json_encode(['message' => $mail->ErrorInfo]);
			}
			
		}else{
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Description: File Transfer");
			header('Content-Disposition: attachment; filename="'.$pdf_filename.'"');
			header("Content-Type: application/pdf");
			header('Accept-Ranges: bytes');
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // date in the past
		
			// push pdf download
			$pdf -> Output($pdf_filename, 'D');	// Display [I]nline, Save to [F]ile, [D]ownload
		}

	}

?>