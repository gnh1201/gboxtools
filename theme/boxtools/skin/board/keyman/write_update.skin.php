<?php
if (!defined('_GNUBOARD_')) exit; // ���� ������ ���� �Ұ�

// Ű ���� ���� �ҷ�����
$inc_file = G5_PLUGIN_PATH . "/boxtools/secure/create_pkey.inc.php";
if(file_exists($inc_file)) {
	include_once($inc_file);
}

// Ű ���� Ȯ��
$privkey = $key_pair['privatekey'];
$pubkey = $key_pair['publickey'];
$privkey_name = $key_pathinfo['privkey_name'];
$pubkey_name = $key_pathinfo['pubkey_name'];
$enckey_name = $key_pathinfo['enckey_name'];

// Ű ���� ���
$sql_stmt = "update $write_table set
				wr_2 = '{$privkey}',
				wr_3 = '{$pubkey}',
				wr_4 = '{$privkey_name}',
				wr_5 = '{$pubkey_name}',
				wr_6 = '{$enckey_name}'
			where wr_id = '{$wr_id}'";
sql_query($sql_stmt);
