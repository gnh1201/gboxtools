<?php
$inc_modules = array();

// load common module
$inc_modules[] = array(
	"path" => "./_common.php",
	"type" => "required"
);

// load rpc_alert module
$inc_modules[] = array(
	"path" => "./rpc_alert.func.inc.php",
	"type" => "include"
);

// load all modules
foreach($inc_modules as $inc) {
	if(file_exists($inc['path'])) {
		if($inc['type'] == "required") {
			require_once($inc['path']);
		} else {
			include_once($inc['path']);
		}
	} else {
		exit("필요한 구성요소가 없어 중단합니다.");
	}
}

// Check RPC
if(array_key_exists("is_rpc", $_REQUEST)) {
	$is_rpc = !empty($_REQUEST['is_rpc']) ? true : false;
}

$cert_assign = 0;
if(array_key_exists("cert_assign", $_POST)) {
	$cert_assign = intval($_POST['cert_assign']);
}

if(!$bo_table) {
	rpc_alert("파일 저장소를 선택하여 주십시오");
}

if(!$wr_id) {
	rpc_alert("파일 번호를 선택하여 주십시오!");
}

if($cert_assign <= 0) {
	rpc_alert("키가 선택되지 않았습니다.");
}

// 키 파일 불러오기
function read_file_stream($key_path) {
	if($myfile = fopen($key_path, "r")) {
		$myread = fread($myfile, filesize($key_path));

		fclose($myfile);
	}
	
	return $myread;
}


// 정보 불러오기
$keyman_table_name = "keyman";
$keyman_write_table = $g5['write_prefix'] . $keyman_table_name;
$keyman_sql = "select * from {$keyman_write_table} where wr_id = '{$cert_assign}'";
$keyman_result = sql_query($keyman_sql);
$keyman_info = array(
	"privkey_name" => "",
	"pubkey_name" => "",
	"enckey_name" => ""
);
while($row = sql_fetch_array($keyman_result)) {
	$keyman_info['privkey_name'] = $row['wr_4'];
	$keyman_info['pubkey_name'] = $row['wr_5'];
	$keyman_info['enckey_name'] = $row['wr_6'];
}

// Autoload 시작
require_once("../vendor/autoload.php");

use RandomLib\Factory as RandomFactory;
use SecurityLib\Strength as SecurityStrength;
use phpseclib\Crypt\RSA as CryptRSA;

// 암호화 기본 변수
$keystore_base_path = G5_PLUGIN_PATH . "/boxtools/secure/keystore";
$privkey_name = $keyman_info['privkey_name'];
$pubkey_name = $keyman_info['pubkey_name'];
$enckey_name = $keyman_info['enckey_name'];

$privkey_path = $keystore_base_path . "/" . $privkey_name;
$pubkey_path = $keystore_base_path . "/" . $pubkey_name;
$enckey_path = $keystore_base_path . "/" . $enckey_name;

// 파일 정보 처리
$file_list = array();
$data_base_path = G5_DATA_PATH . "/file/" . $bo_table;
$file_info = get_file($bo_table, $wr_id);

foreach($file_info as $file) {
	$file_list[] = $data_base_path . "/" . $file['file'];
}

// 암호키 취득
$rsa = new CryptRSA();
$rsa->loadKey(read_file_stream($pubkey_path)); // load public key
$ci_enc_key = read_file_stream($enckey_path); // load encrypt key
$ci_key = $rsa->decrypt($ci_enc_key);

// 암호화 시작
$writable_flag = true;
$encrypt_base_path = $data_base_path . "/encrypted";
if(!is_dir($encrypt_base_path)) {
	if(!mkdir($encrypt_base_path, 0777)) {
		$writable_flag = false;
	}
}

if($writable_flag == false) {
	rpc_alert("데이터 영역이 쓰기방지가 되어있어 암호화가 불가능합니다.", "", $is_rpc, false);
}

foreach($file_list as $fpath) {
	$src_file = $fpath;
	$dst_file = $fpath . ".enc";

	$factory = new RandomFactory();
	$generator = $factory->getGenerator(new SecurityStrength(SecurityLib\Strength::MEDIUM));
	$gen_source = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$gen_name = $generator->generateString(16, $gen_source);

	$dst_file = $encrypt_base_path . "/" . $gen_name . ".enc";
	$encrypt_ci_cmd = "openssl enc -aes-256-cbc -e -in %s -out %s -k %s";
	$encrypt_ci_cmd = sprintf($encrypt_ci_cmd, $src_file, $dst_file, $ci_key);

	$encrypt_ci_result = shell_exec($encrypt_ci_cmd);
}

// 결과 출력
$gt_link = sprintf(G5_BBS_URL . "/board.php?bo_table=%s&wr_id=%s", $bo_table, $wr_id);
rpc_alert("암호화를 완료하였습니다.", $gt_link, $is_rpc, true);