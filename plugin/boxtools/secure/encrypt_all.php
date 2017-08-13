<?php
/** encrypt_all.php **/
/** encrypt all files in specific repository **/

require_once("./_common.php");

$alert_disable = "";
if(array_key_exists("alert_disable", $_REQUEST)) {
	$alert_disable = $_REQUEST["alert_disable"];
} 

if(!$bo_table) {
	alert("파일 저장소를 선택하여 주십시오");
}

// Autoload 시작
require_once("../vendor/autoload.php");

use RandomLib\Factory as RandomFactory;
use SecurityLib\Strength as SecurityStrength;
use phpseclib\Crypt\RSA as CryptRSA;

// redefine alert
function redef_alert($msg, $link) {
	global $alert_disable;
	
	if(empty($alert_disable)) {
		alert($msg);
	} else {
		goto_url($link);
	}
}

// 키 파일 불러오기
function read_file_stream($key_path) {
	if($myfile = fopen($key_path, "r")) {
		$myread = fread($myfile, filesize($key_path));

		fclose($myfile);
	}
	
	return $myread;
}

// 암호화 파일 관리등록
$flock_table_name = "filelock";
$flock_write_table = $g5["write_table"] . $flock_table_name;
$flock_data_base_path = G5_DATA_PATH . "/file/" . $flock_table_name;

// 암호화 디렉토리 상태 확인
$writable_flag = true;
$data_base_path = G5_DATA_PATH . "/file/" . $bo_table;
$encrypt_base_path = $flock_data_base_path . "/encrypted";
if(!is_dir($encrypt_base_path)) {
	if(!mkdir($encrypt_base_path, 0777)) {
		$writable_flag = false;
	}
}

if($writable_flag == true) {
	if(!is_writable($encrypt_base_path)) {
		$writable_flag = false;
	}
}

if($writable_flag == false) {
	alert("데이터 영역이 쓰기방지가 되어있어 암호화가 불가능합니다.");
}

// 최종 탐색번호 등록 및 조회
$lastnum = 0;
$old_lastnum = 0;
$lastnum_sql = "select bo_2 from {$g5['board_table']} where bo_table = '{$bo_table}'";
$lastnum_result = sql_query($lastnum_sql);
while($row = sql_fetch_array($lastnum_result)) {
	$old_lastnum = intval($row['bo_2']);
}
$lastnum = $old_lastnum;

// 리스트 불러오기
$sql_base_stmt = "select * from {$write_table}";
$sql_part_stmt = "";
if($lastnum > 0) {
	$sql_part_stmt .= " where wr_id > {$lastnum}";
}

$sql = $sql_base_stmt . $sql_part_stmt;
$result = sql_query($sql);
while($row = sql_fetch_array($result)) {
	$list[] = get_list($row, $board, $skin_url, $subject_len=40);
}

// 암호키 정보 조회
$keyman_table_name = "keyman";
$keyman_write_table = $g5['write_prefix'] . $keyman_table_name;

// 모듈 식별정보 조회
$module_table = "modules";
$module_write_table = $g5['write_prefix'] . $module_table;

// 암호키 경로 설정
$keystore_base_path = G5_PLUGIN_PATH . "/boxtools/secure/keystore";

$encrypt_target_list = array();
foreach($list as $item) {
	$file_info = get_file($bo_table, $item['wr_id']);
	
	// 식별코드로 조회
	$manage_code = addslashes($item['wr_2']);
	$module_sql = "select * from {$module_write_table} where wr_2 = '{$manage_code}' order by wr_datetime desc ";
	$module_result = sql_query($module_sql);
	$module_id = 0;
	$keyman_id = 0;
	while($row = sql_fetch_array($module_result)) {
		$module_id = intval($row['wr_id']);
		$keyman_id = intval($row['wr_3']);

		break; // 최근 1건만
	}

	if($keyman_id > 0) {
		$keyman_sql = "select * from {$keyman_write_table} where wr_id = '{$keyman_id}' ";
		$keyman_result = sql_query($keyman_sql);
		$keyman_info = array(
			"identify" => 0,
			"privkey_name" => "",
			"pubkey_name" => "",
			"enckey_name" => ""
		);
		while($row = sql_fetch_array($keyman_result)) {
			$keyman_info['identify'] = intval($row['wr_id']);
			$keyman_info['privkey_name'] = $row['wr_4'];
			$keyman_info['pubkey_name'] = $row['wr_5'];
			$keyman_info['enckey_name'] = $row['wr_6'];

			break; // 최근 1건만
		}

		// 조회된 키가 있는지 확인
		if($keyman_info['identify'] > 0) {
			// 키 파일 위치
			$pubkey_path = $keystore_base_path . "/" . $keyman_info['pubkey_name'];
			$enckey_path = $keystore_base_path . "/" . $keyman_info['enckey_name'];

			// 암호화 파일 목록 추가
			foreach($file_info as $file) {
				if(array_key_exists("file", $file) && !empty($file['file'])) {
					$encrypt_target_info = array(
						"pubkey_path" => $pubkey_path,
						"enckey_path" => $enckey_path,
						"src_name" => $file["source"],
						"src_path" => $data_base_path . "/" . $file['file'],
						"dst_name" => "",
						"dst_path" => "",
						"workspace" => $item['wr_10']
					);

					$encrypt_target_list[] = $encrypt_target_info;
				}
			}
		}
	} else {
		redef_alert(
			"자동 암호화 설정이 되어있지 않습니다.",
			G5_BBS_URL . sprintf("/board.php?bo_table=%s&wr_id=%s", $keyman_table_name, $module_id)
		);
	}
	
	// 마지막 처리 번호 입력
	$lastnum = intval($item['wr_id']);
	if($lastnum > $old_lastnum) {
		$old_lastnum = $lastnum;
	}
}

// 파일 암호화 시작
foreach($encrypt_target_list as $enc_item) {
	// 암호키 경로
	$fpath = $enc_item["src_path"];
	$pubkey_path = $enc_item["pubkey_path"];
	$enckey_path = $enc_item["enckey_path"];

	// 암호키 취득
	$rsa = new CryptRSA();
	$rsa->loadKey(read_file_stream($pubkey_path)); // load public key
	$ci_enc_key = read_file_stream($enckey_path); // load encrypt key
	$ci_key = $rsa->decrypt($ci_enc_key);

	$src_file = $fpath;
	$dst_file = $fpath . ".enc";

	$factory = new RandomFactory();
	$generator = $factory->getGenerator(new SecurityStrength(SecurityLib\Strength::MEDIUM));
	$gen_source = "0123456789abcdefghijklmnopqrstuvwxyz";
	$gen_name = $generator->generateString(16, $gen_source);

	$dst_file_name = $gen_name . ".enc";
	$dst_file = $encrypt_base_path . "/" . $dst_file_name;
	$encrypt_ci_cmd = "openssl enc -aes-256-cbc -e -in %s -out %s -k %s";
	$encrypt_ci_cmd = sprintf($encrypt_ci_cmd, $src_file, $dst_file, $ci_key);

	$encrypt_ci_result = shell_exec($encrypt_ci_cmd);
	
	// 암호화 정보 등록
	$enc_item["dst_name"] = "encrypted/" . $dst_file_name;
	$enc_item["dst_path"] = $dst_file;

	update_encrypted_file($flock_table_name, $enc_item);
}

// 마지막 처리 번호 등록
$sql = "update {$g5['board_table']}
			set bo_2_subj = '최근 암호화 순번',
				bo_2 = '{$old_lastnum}'
		where bo_table = '{$bo_table}'";
sql_query($sql);

// 암호화 파일 등록
function update_encrypted_file($bo_table, $enc_item) {
	global $g5, $member, $manage_code;
	
	// 데이터 정보 입력
	$data_base_path = G5_DATA_PATH . "/file/" . $bo_table;
	$encrypt_base_path = $data_base_path . "/encrypted";

	// 정보 등록 시작
	$clone_result = false;
	$clone_table = $bo_table;
	$clone_write_table = $g5['write_prefix'] . $clone_table;
	$clone_board = sql_fetch("select * from {$clone_write_table} where bo_table = '{$clone_table}'");

	$clone_md5 = md5_file($enc_item["dst_path"]);
	$clone_sha1 = sha1_file($enc_item["dst_path"]);

	// 새 글 등록
    if ($member['mb_id']) {
        $mb_id = $member['mb_id'];
        $wr_name = addslashes(clean_xss_tags($board['bo_use_name'] ? $member['mb_name'] : $member['mb_nick']));
        $wr_password = $member['mb_password'];
        $wr_email = addslashes($member['mb_email']);
        $wr_homepage = addslashes(clean_xss_tags($member['mb_homepage']));
    } else {
		$factory = new RandomFactory();
		$generator = $factory->getGenerator(new SecurityStrength(SecurityLib\Strength::MEDIUM));
		$gen_source = "0123456789abcdefghijklmnopqrstuvwxyz";
		$gen_password = $generator->generateString(32, $gen_source);

        $mb_id = "agent";
        $wr_name = "Agent";
        $wr_password = get_encrypt_string($gen_password);
        $wr_email = "";
        $wr_homepage = "";
    }

	$clone_file_name = $enc_item["src_name"];
	$clone_file_store_name = $enc_item["dst_name"];
	$clone_file_path = G5_DATA_PATH . "/file/" . $clone_table . "/" . $clone_file_store_name;

	$ca_name = "";
	$wr_subject = "Encrypted: ". $clone_file_name;
	$wr_content = "Encrypted: ". $clone_file_name;
	$wr_2 = $manage_code; // identify code
	$wr_3 = $clone_md5; // md5 checksum
	$wr_4 = $clone_sha1; // sha1 checksum
	$wr_5 = "";
	$wr_6 = "";
	$wr_7 = "";
	$wr_8 = "";
	$wr_9 = "";
	$wr_10 = $enc_item['workspace'];

	$wr_num = get_next_num($clone_write_table);
	$wr_reply = '';
    $sql = " insert into $clone_write_table
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
                     wr_1 = '$wr_1',
                     wr_2 = '$wr_2',
                     wr_3 = '$wr_3',
                     wr_4 = '$wr_4',
                     wr_5 = '$wr_5',
                     wr_6 = '$wr_6',
                     wr_7 = '$wr_7',
                     wr_8 = '$wr_8',
                     wr_9 = '$wr_9',
                     wr_10 = '$wr_10' ";
    sql_query($sql);

	// 파일 정보 추출
	$clone_file_imageinfo = @getimagesize($clone_file_path);
	$clone_file_size = filesize($clone_file_path);

	// 파일 정보 등록
	$clone_id = sql_insert_id();
	$clone_sql = " insert into {$g5['board_file_table']}
				set bo_table = '{$clone_table}',
					 wr_id = '{$clone_id}',
					 bf_no = '0',
					 bf_source = '{$clone_file_name}',
					 bf_content = 'md5:{$clone_md5}',
					 bf_file = '{$clone_file_store_name}',
					 bf_download = 0,
					 bf_filesize = '{$clone_file_size}',
					 bf_width = '{$clone_file_imageinfo['0']}',
					 bf_height = '{$clone_file_imageinfo['1']}',
					 bf_type = '{$clone_file_imageinfo['2']}',
					 bf_datetime = '".G5_TIME_YMDHIS."' ";
	sql_query($clone_sql);

	// 복사 완료
	$clone_result = true;

	return $clone_result;
}

// 결과 출력
redef_alert(
	"모든 암호화를 완료하였습니다.",
	G5_BBS_URL . sprintf("/board.php?bo_table=%s&wr_id=%s", $bo_table, $wr_id)
);