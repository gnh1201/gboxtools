<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 키 생성 도구 불러오기
$inc_file = G5_PLUGIN_PATH . "/boxtools/secure/create_pkey.inc.php";
if(file_exists($inc_file)) {
	include_once($inc_file);
}

// 키 정보 확인
$privkey = $key_pair['privatekey'];
$pubkey = $key_pair['publickey'];
$privkey_name = $key_pathinfo['privkey_name'];
$pubkey_name = $key_pathinfo['pubkey_name'];
$enckey_name = $key_pathinfo['enckey_name'];

// 키 정보 등록
$sql_stmt = "update $write_table set
				wr_2 = '{$privkey}',
				wr_3 = '{$pubkey}',
				wr_4 = '{$privkey_name}',
				wr_5 = '{$pubkey_name}',
				wr_6 = '{$enckey_name}'
			where wr_id = '{$wr_id}'";
sql_query($sql_stmt);
