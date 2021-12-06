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
	require_once "cc_report_compare_inc.php";

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
		  $text['label-summary_date'] =>'DD-MM-YYYY',//text
		  $text['label-summary_answered'] => 'integer',//text
		  $text['label-summary_missed'] =>'integer',
		  $text['label-summary_ring_time'] => '@',
		  $text['label-summary_talk_time'] =>'@',
		  $text['label-summary_avg_ring_time'] => '@',
		  $text['label-summary_avg_talk_time'] =>'@',
		  $text['label-summary_active_agents'] =>'integer'
		);
		
		$writer->writeSheetHeader('CallCenter Daily Summary', $header);

		$filename = "cdr_dailysummary_".date("Ymd_His").".xlsx";

		foreach($sum_data as $key => $value) {
			$writer->writeSheetRow('CallCenter Daily Summary',
				[
					$key,
					$value['answer_missed']['answered'],
					$value['answer_missed']['missed'],
					gmdate("H:i:s",$value['duration']['ring_time']),
					gmdate("H:i:s",$value['duration']['talk_time']),
					gmdate("H:i:s",$value['avg_duration']['ring_time']),
					gmdate("H:i:s",$value['avg_duration']['talk_time']),
					$value['active_agents']
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
		$data_head .= '<td width="20%"><b>'.$text['label-summary_date'].'</b></td>';
		$data_head .= '<td width="15%"><b>'.$text['label-summary_answered'].'</b></td>';
		$data_head .= '<td width="15%"><b>'.$text['label-summary_missed'].'</b></td>';
		$data_head .= '<td width="20%"><b>'.$text['label-summary_ring_time'].'</b></td>';
		$data_head .= '<td width="20%"><b>'.$text['label-summary_talk_time'].'</b></td>';
		$data_head .= '<td width="10%"><b>'.$text['label-summary_active_agents'].'</b></td>';
		$data_head .= '</tr>';
		$data_head .= '<tr><td colspan="8"><hr></td></tr>';
	
		//initialize total variables
		$total['answered'] = 0;
		$total['missed'] = 0;
		$total['ring_time'] = 0;
		$total['talk_time'] = 0;
		$total['agents'] = 0;
		
		$z = 0; // total counter
		$p = 0; // per page counter
		
		//write the row cells
		if (sizeof($sum_data) > 0) {
			foreach($sum_data as $key => $value) {
				$data_body[$p] .= '<tr>';
				$data_body[$p] .= '<td width="20%">'.$key.'</td>';
				$total['answered'] += $value['answer_missed']['answered'];
				$total['missed'] += $value['answer_missed']['missed'];
				$data_body[$p] .= '<td width="15%">'.$value['answer_missed']['answered'].'</td>';
				$data_body[$p] .= '<td width="15%">'.$value['answer_missed']['missed'].'</td>';
				$total['ring_time'] += $value['duration']['ring_time'];
				$data_body[$p] .= '<td width="20%">'.gmdate("G:i:s",$value['duration']['ring_time']).'</td>';
				$total['talk_time'] += $value['duration']['talk_time'];
				$data_body[$p] .= '<td width="20%">'.gmdate('G:i:s',$value['duration']['talk_time']).'</td>';
				$total['agents'] += $value['active_agents'];
				$data_body[$p] .= '<td width="10%">'.$value['active_agents'].'</td>';
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
		$data_footer = '<tr><td colspan="6"><hr></td></tr>';
		
		//write totals
		$data_footer .= '<tr>';
		$data_footer .= '<td><b>'.$text['label-total'].'</b></td>';
		$data_footer .= '<td>'.$total['answered'].'</td>';
		$data_footer .= '<td>'.$total['missed'].'</td>';
		$data_footer .= '<td><b>'.gmdate("G:i:s", $total['ring_time']).'</b></td>';
		$data_footer .= '<td><b>'.gmdate("G:i:s", $total['talk_time']).'</b></td>';
		$data_footer .= '<td><b>'.$total['agents'].'</b></td>';
		$data_footer .= '</tr>';
	
		//write divider
		$data_footer .= '<tr><td colspan="6"><hr></td></tr>';
	
		//write averages
		$data_footer .= '<tr>';
		$data_footer .= '<td><b>'.$text['label-average'].'</b></td>';
		$data_footer .= '<td><b>'.round($total['answered']/$z,1).'</b></td>';
		$data_footer .= '<td><b>'.round($total['missed']/$z,1).'</b></td>';
		$data_footer .= '<td><b>'.gmdate("G:i:s", round($total['ring_time']/$z)).'</b></td>';
		$data_footer .= '<td><b>'.gmdate("G:i:s", round($total['talk_time']/$z)).'</b></td>';
		$data_footer .= '<td><b>'.round($total['agents']/$z,1).'</b></td>';
		$data_footer .= '</tr>';
		
		//write divider
		$data_footer .= '<tr><td colspan="6"><hr></td></tr>';
	
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
		$pdf_filename = "cdr_dailysummary_".$_SESSION['domain_name']."_".date("Ymd_His").".pdf";
	
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
			    $mail->Subject = $text['label-summary_report_subject'];
			    $mail->Body    = $text['label-summary_report_body'];
			
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