<?php
/** noderef_enc_down.php **/
/** download encrypted file by NodeRef **/

require_once("./_common.php");

$noderef = "";
if(array_key_exists("noderef", $_REQUEST)) {
	$noderef =  addslashes($_REQUEST["noderef"]);
} else {
	alert("노드 정보가 없습니다.");
}

// read file stream
function read_file_stream($path, $size=0) {
	$stream = "";
	
	if($size <= 0) {
		$size = filesize($file_path);
	}

	if ($handle = fopen($path, "r")) {
		$contents = fread($handle, $size);
		$stream = $contents;
		fclose($handle);
	}
	
	return $stream;
}

// Autoload provider
$autoload_base_path = "..";
require_once($autoload_base_path . "/vendor/autoload.php");

// 암호화 파일 관리정보 불러오기
$encfile_not_found = false;
$flock_table_name = "filelock";
$flock_write_table = $g5['write_prefix'] . $flock_table_name;
$node_info = sql_fetch("select * from {$flock_write_table} where wr_10 = '{$noderef}'");
$data_base_path = G5_DATA_PATH . "/file/" . $flock_table_name;
$file_name = "";
$file_path = "";
if(array_key_exists("wr_id", $node_info)) {
	// 실제 파일 정보 불러오기
	$fileinfo = get_file($flock_table_name, $node_info["wr_id"]);
	
	foreach($fileinfo as $file) {
		if(array_key_exists("file", $file) && !empty($file['file'])) {
			$file_name = $file['source'];
			$file_path = $data_base_path . "/" . $file['file'];
		}
	}
} else {
	$encfile_not_found = true;
}

if(!file_exists($file_path)) {
	$encfile_not_found = true;
}

if($encfile_not_found) {
	alert("암호화된 파일을 찾을 수 없습니다.");
}

// 파일 스트림 불러오기
$quoted = $file_name;
$size   = filesize($file_path);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $quoted . '"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $size);

// 최종 파일출력
echo read_file_stream($file_path, $size);
