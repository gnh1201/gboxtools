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
$is_rpc = false;
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

// 복호화 기본정보
$decrypted_table = "decrypted";
$data_dec_base_path = G5_DATA_PATH . "/file/" . $decrypted_table;

foreach($file_info as $file) {
	if(!empty($file['file'])) {
		$file_list[] = array(
			"source" => $file['source'],
			"path"   => $data_base_path . "/" . $file['file']
		);
	}
}

// 암호키 취득
$rsa = new CryptRSA();
$rsa->loadKey(read_file_stream($pubkey_path)); // load public key
$ci_enc_key = read_file_stream($enckey_path); // load encrypt key
$ci_key = $rsa->decrypt($ci_enc_key);

// 복호화 시작
$writable_flag = true;
$decrypt_base_path = $data_dec_base_path . "/decrypted";
if(!is_dir($decrypt_base_path)) {
	if(!mkdir($decrypt_base_path, 0777)) {
		$writable_flag = false;
	}
}

if($writable_flag == false) {
	rpc_alert("데이터 영역이 쓰기방지가 되어있어 암호화가 불가능합니다.", "", $is_rpc, false);
}

$dst_list = array();
foreach($file_list as $fitem) {
	$fpath = $fitem['path'];

	$src_file = $fpath;
	$dst_file = $fpath . ".decrypted";

	$factory = new RandomFactory();
	$generator = $factory->getGenerator(new SecurityStrength(SecurityLib\Strength::MEDIUM));
	$gen_source = "0123456789abcdefghijklmnopqrstuvwxyz";
	$gen_name = $generator->generateString(16, $gen_source);

	$dst_name = $gen_name . ".decrypted";
	$dst_file = $decrypt_base_path . "/" . $dst_name;
	$encrypt_ci_cmd = "openssl enc -aes-256-cbc -d -in %s -out %s -k %s";
	$encrypt_ci_cmd = sprintf($encrypt_ci_cmd, $src_file, $dst_file, $ci_key);

	$encrypt_ci_result = shell_exec($encrypt_ci_cmd);

	// add destination list
	$dst_list[] = array(
		"dst_num" => 0,
		"src_name" => $fitem['source'],
		"dst_name" => "decrypted/" . $dst_name,
		"dst_file" => $dst_file
	);
}

// 복호화 파일 등록
$decrypt_write_table = $g5['write_prefix'] . "decrypted";

// 복호화 정보 등록
$decrypted_table = "decrypted";
$decrypted_write_table = $g5['write_prefix'] . "decrypted";
for($i = 0; $i < count($dst_list); $i++) {
	// alter to foreach
	$item = $dst_list[$i];
	
	$decrypted_file_name = $item['src_name'];
	$wr_subject = addslashes($decrypted_file_name . " 파일의 " . G5_TIME_YMDHIS . " 복호화 요청");
	$wr_content = addslashes($decrypted_file_name . " 파일의 " . G5_TIME_YMDHIS . " 복호화 요청");
	$ca_name = "";

	if($is_member) {
		$wr_name = $member['mb_name'];
		$wr_email = $member['mb_email'];
		$wr_homepage = $member['mb_homepage'];
	} else {
		$wr_name = "Agent";
		$wr_email = "agent@localhost";
		$wr_homepage = "http://127.0.0.1/";
	}

	$gen = $factory->getGenerator(new SecurityStrength(SecurityLib\Strength::MEDIUM));
	$gen_src = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-+|";
	$gen_rst = $gen->generateString(16, $gen_src);
	$wr_password = sql_password($gen_rst);
	
	$decrypted_file_store_name = $item['dst_name'];
	$decrypted_real_path = $data_dec_base_path . "/" . $decrypted_file_store_name;
	$decrypted_md5 = md5_file($decrypted_real_path);
	$decrypted_sha1 = sha1_file($decrypted_real_path);

	$wr_num = get_next_num($decrypted_write_table);
	$wr_reply = "";
	$sql = " insert into $decrypted_write_table
				set wr_num = '$wr_num',
					 wr_reply = '$wr_reply',
					 wr_comment = 0,
					 ca_name = '$ca_name',
					 wr_option = '',
					 wr_subject = '$wr_subject',
					 wr_content = '$wr_content',
					 mb_id = 'admin',
					 wr_password = '$wr_password',
					 wr_name = '$wr_name',
					 wr_email = '$wr_email',
					 wr_homepage = '$wr_homepage',
					 wr_datetime = '".G5_TIME_YMDHIS."',
					 wr_last = '".G5_TIME_YMDHIS."',
					 wr_ip = '{$_SERVER['REMOTE_ADDR']}',
					 wr_file = '1',
					 wr_3 = '$decrypted_md5', 
					 wr_4 = '$decrypted_sha1' ";
	sql_query($sql);
	
	// 파일정보 등록
	$decrypted_file_imageinfo = @getimagesize($item['dst_file']);
	$decrypted_file_size = filesize($item['dst_file']);
	$decrypted_id = sql_insert_id();
	$sql = " insert into {$g5['board_file_table']}
			set bo_table = '{$decrypted_table}',
				 wr_id = '{$decrypted_id}',
				 bf_no = '0',
				 bf_source = '{$decrypted_file_name}',
				 bf_content = 'md5:{$decrypted_md5}; sha1:{$decrypted_sha1}',
				 bf_file = '{$decrypted_file_store_name}',
				 bf_download = 0,
				 bf_filesize = '{$decrypted_file_size}',
				 bf_width = '{$decrypted_file_imageinfo['0']}',
				 bf_height = '{$decrypted_file_imageinfo['1']}',
				 bf_type = '{$decrypted_file_imageinfo['2']}',
				 bf_datetime = '".G5_TIME_YMDHIS."' ";
	sql_query($sql);
	
	// change number
	$dst_list[$i]['dst_num'] = $decrypted_id;
}

// 결과 출력
$gt_link = sprintf(G5_BBS_URL . "/board.php?bo_table=%s&wr_id=%s", $bo_table, $wr_id);
if($is_rpc == true) {
	$gt_msg = addslashes(json_encode($dst_list));
} else {
	$gt_msg = "복호화를 완료하였습니다.";
}

rpc_alert($gt_msg, $gt_link, $is_rpc, true);
