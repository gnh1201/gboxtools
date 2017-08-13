<?php
/** create_pkey.inc.php **/
/** Component for create RSA key by OpenSSL **/

// 중간 메시지 표시 여부
$msg_printable = defined('_GNUBOARD_') ? false : true;
if($msg_printable == false) {
	$autoload_base_path = G5_PLUGIN_PATH . "/boxtools";
} else {
	$autoload_base_path = "..";
}

// 파일 불러오기
require_once("./_common.php");
require_once($autoload_base_path . "/vendor/autoload.php");

use AdamBrett\ShellWrapper\Runners\Exec;
use AdamBrett\ShellWrapper\Command\Builder as CommandBuilder;
use RandomLib\Factory as RandomFactory;
use SecurityLib\Strength as SecurityStrength;
use phpseclib\Crypt\RSA as CryptRSA;

// 키를 저장할 위치를 구함
function get_new_key_path() {
	$path_info = array(
		"privkey_name" => "",
		"pubkey_name" => "",
		"privkey_path" => "",
		"pubkey_path" => ""
	);

	$factory = new RandomFactory();
	$generator = $factory->getGenerator(new SecurityStrength(SecurityLib\Strength::MEDIUM));
	$gen_source = "0123456789abcdefghijklmnopqrstuvwxyz";
	$gen_name1 = $generator->generateString(16, $gen_source); // privkey
	$gen_name2 = $generator->generateString(16, $gen_source); // pubkey
	$gen_name3 = $generator->generateString(16, $gen_source); // enckey

	$privkey_name = $gen_name1 . ".pem";
	$pubkey_name = $gen_name2 . ".pub";
	$enckey_name = $gen_name3 . ".enc";

	if(defined("G5_PLUGIN_PATH")) {
		$key_base_path = G5_PLUGIN_PATH . "/boxtools/secure/keystore";
	} else {
		$key_base_path = "./keystore";
	}

	$privkey_path = $key_base_path . "/" . $privkey_name;
	$pubkey_path = $key_base_path . "/" . $pubkey_name;
	$enckey_path = $key_base_path . "/" . $enckey_name;

	if(file_exists($privkey_path) || file_exists($pubkey_path)) {
		$path_info = get_new_key_path(); // 실패 시 반복
	} else {
		$path_info["privkey_name"] = $privkey_name;
		$path_info["pubkey_name"] = $pubkey_name;
		$path_info["enckey_name"] = $enckey_name;
		$path_info["privkey_path"] = $privkey_path;
		$path_info["pubkey_path"] = $pubkey_path;
		$path_info["enckey_path"] = $enckey_path;
	}

	return $path_info;
}

// 키 파일을 생성함
function create_key_file($keypair, $pathinfo) {
	$work_flag = false;

	// pki path info
	$privkey_path = $pathinfo["privkey_path"];
	$pubkey_path = $pathinfo["pubkey_path"];

	// write private key
	$step1 = fwrite_with_content($privkey_path, $keypair["privatekey"]);
	$step2 = fwrite_with_content($pubkey_path, $keypair["publickey"]);
	$step3 = generate_encrypt_key($pathinfo, $keypair);

	if($step1 && $step2 && $step3) {
		$work_flag = true;		
	}

	return $work_flag;
}

// 비밀키 생성
function generate_encrypt_key($pathinfo, $keypair) {
	$gen_result = false;
	
	// pki path info
	$privkey_path = $pathinfo["privkey_path"];
	$pubkey_path = $pathinfo["pubkey_path"];
	$enckey_path  = $pathinfo["enckey_path"];
	
	// create random factory
	$factory = new RandomFactory();
	$generator = $factory->getGenerator(new SecurityStrength(SecurityLib\Strength::MEDIUM));
	$gen_source = "0123456789abcdefghijklmnopqrstuvwxyz";
	$gen_enckey = $generator->generateString(32, $gen_source); // enckey
	
	// step of generation
	$rsa = new CryptRSA();
	$rsa->loadKey($keypair["privatekey"]);
	$ciphertext = $rsa->encrypt($gen_enckey);

	$gen_step = fwrite_with_content($enckey_path, $ciphertext);
	
	if($gen_step == true) {
		$gen_result = true;
	}

	return $gen_result;
}

// 파일 내용을 쓰기.
function fwrite_with_content($filename, $content) {
	$write_flag = false;

	if($handle = fopen($filename, 'w')) {
		if (fwrite($handle, $content) !== FALSE) {
			$write_flag = true;
		}

		fclose($handle);
	}

	return $write_flag;
}

// key generation
$rsa = new CryptRSA();
$key_pathinfo = get_new_key_path();
$key_pair = $rsa->createkey();

if(create_key_file($key_pair, $key_pathinfo)) {
	$info_out = array(
		"success" => true,
		"pathinfo" => $key_pathinfo,
		"msg" => "키 생성에 성공하였습니다."
	);
} else {
	$info_out = array(
		"success" => false,
		"pathinfo" => $key_pathinfo,
		"msg" => "키 생성에 실패하였습니다."
	);
}

// 최종 결과 출력
if($msg_printable) {
	echo "{
		\"success\": \"" . ($info_out["success"] == true ? "true" : "false") . "\",
		\"privkey_name\": \"" . $info_out["pathinfo"]["privkey_name"] . "\",
		\"pubkey_name\": \"" . $info_out["pathinfo"]["pubkey_name"] . "\",
		\"enckey_name\": \"" . $info_out["pathinfo"]["enckey_name"] . "\",
		\"msg\": \"" . $info_out["msg"] . "\"
	}";
}
?>