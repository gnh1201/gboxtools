<?php
/** encrypt set auto **/
/** set auto encryption for repository */

require_once("./_common.php");

$cert_assign = 0;
if(array_key_exists("cert_assign", $_POST)) {
	$cert_assign = intval($_POST['cert_assign']);
}

if(!$bo_table) {
	alert("모듈 관리자를 선택하여 주십시오");
}

if(!$wr_id) {
	alert("모듈 관리번호를 선택하여 주십시오!");
}

if($cert_assign <= 0) {
	alert("키가 선택되지 않았습니다.");
}

// 암호키 등록
$sql = "update {$write_table} set wr_3 = '{$cert_assign}' where wr_id = '{$wr_id}'";
sql_query($sql);

alert(
	"자동 암호화 키가 등록되었습니다.",
	G5_BBS_URL . sprintf("/board.php?bo_table=%s&wr_id=%s", $bo_table, $wr_id)
);