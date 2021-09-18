<?php

//application details
	$apps[$x]['name'] = 'Call Center Report Manager';
	$apps[$x]['uuid'] = '4b88ccfb-cb98-40e1-a5e5-33389e1bbff3';
	$apps[$x]['category'] = '';
	$apps[$x]['subcategory'] = '';
	$apps[$x]['version'] = '';
	$apps[$x]['license'] = 'Mozilla Public License 1.1';
	$apps[$x]['url'] = 'http://www.cloudpractice.com';
	$apps[$x]['description']['en-us'] = 'Call Center Report Manager module developed by Cloud Practice Nigeria.';

//permission details
	$y = 0;
	$apps[$x]['permissions'][$y]['name'] = 'cc_report_view';
	//$apps[$x]['permissions'][$y]['menu']['uuid'] = '2e3d8f49-5beb-44a8-9617-0bffc5b45cf3';
	$apps[$x]['permissions'][$y]['groups'][] = 'cc-manager';
	$apps[$x]['permissions'][$y]['groups'][] = 'superadmin';
	$y++;
	
//schema details

?>