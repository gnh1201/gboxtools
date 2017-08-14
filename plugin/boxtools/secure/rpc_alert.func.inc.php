<?php
// rpc_alert.function.php

// 비동기식 알림
function rpc_alert($str = "", $link = "", $is_rpc = false, $is_pos = false) {
	if($is_pos == true) {
		$cnt = 1;
	} else {
		$cnt = 0;
	}
	
	if($is_rpc == true) {
		$resp_predat = array(
			"cnt" => $cnt,
			"msg" => "%s"
		);

		$resp_predat_step1 = json_encode($resp_predat);
		$resp_predat_step2 = trim(preg_replace('/\s\s+/', ' ', $str));
		$resp = sprintf($resp_predat_step1, $resp_predat_step2);

		echo $resp;
	} else {
		if(function_exists("alert")) {
			if(!empty($link)) {
				alert($str, $link);
			} else {
				alert($str);
			}
		} else {
			exit($str);
		}
	}
}
?>
