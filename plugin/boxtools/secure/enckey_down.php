<?php
/** pubkey_down.php **/
/** public key download provider **/

require_once("./_common.php");

if (!$bo_table) {
	alert("게시판이 선택되지 않았습니다.");
}

if (!$wr_id) {
	alert("게시물이 선택되지 않았습니다.");
}

// Autoload provider
$autoload_base_path = "..";
require_once($autoload_base_path . "/vendor/autoload.php");

// 공개키 정보 불러오기
$cert_sql_stmt = "select * from $write_table where wr_id = '$wr_id' ";
$cert_sql_result = sql_query($cert_sql_stmt);
$cert_info = array();
while($row = sql_fetch_array($cert_sql_result)) {
	$cert_info = $row;
}
$enckey_name = $cert_info['wr_6'];
$enckey_path = G5_PLUGIN_PATH . "/boxtools/secure/keystore/" . $enckey_name;

$quoted = sprintf('"%s"', addcslashes(basename($enckey_path), '"\\'));
$size   = filesize($pubkey_path);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $quoted); 
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $size);

if ($handle = fopen($pubkey_path, "r")) {
	$contents = fread($handle, $size);
	echo $contents;
	fclose($handle);
}
