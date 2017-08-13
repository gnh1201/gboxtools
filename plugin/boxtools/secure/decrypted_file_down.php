<?php
include_once("./_common.php");
include_once("./rpc_alert.inc.php");

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

// decrypted file
$decrypted_id = $_REQUEST['decrypted_id'];
if(empty($decrypted_id)) {
	alert("복호화 건이 존재하지 않습니다.");
}

// find decrypted resource
$decrypted_table_name = "decrypted";
$decrypted_write_table = $g5['write_prefix'] . $decrypted_table_name;
$sql_comm = "from {$decrypted_write_table} where wr_id = '{$decrypted_id}'";
$sql_row = " select * {$sql_comm} ";
$sql_cnt = " select count(*) as cnt {$sql_comm} ";

$write = sql_fetch($sql_row);

$file_found = false;
$file_name = "";
$file_path = "";
$data_base_path = G5_DATA_PATH . "/file/" . $decrypted_table_name;
if(array_key_exists("wr_id", $write)) {
	if(!empty($write['wr_id'])) {
		// 실제 파일 정보 불러오기
		$fileinfo = get_file($decrypted_table_name, $write['wr_id']);
		
		foreach($fileinfo as $file) {
			if(array_key_exists("file", $file) && !empty($file['file'])) {
				$file_name = $file['source'];
				$file_path = $data_base_path . "/" . $file['file'];

				break;
			}
		}

		if(file_exists($file_path)) {
			$file_found = true;
		}
	}
} else {
	alert("복호화 정보를 찾을 수 없습니다.");
}

if($file_found == false) {
	alert("복호화된 파일을 찾을 수 없습니다.");
}

// 파일 스트림 불러오기
$quoted = $file_name;
$size   = filesize($file_path);

// 전송 헤더정의
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
?>