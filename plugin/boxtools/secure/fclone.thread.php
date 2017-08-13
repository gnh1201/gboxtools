<?php
/** fclone.thread.php **/
/** Thread for Clone Stream to File */

class cloneThread extends Thread {
	public function __construct($propinfo, $stream){
		$this->propinfo = $propinfo;
		$this->stream = $stream;
	}

	public function run(){
		clone_file_stream($this->propinfo, $this->stream);
	}

	public function clone_file_stream($propinfo, $stream) {
		global $g5, $member;

		$clone_table = "explore";
		$clone_write_table = $g5['write_prefix'] . $clone_table;
		$clone_board = sql_fetch("select * from {$clone_write_table} where bo_table = '{$clone_table}'");
		$clone_prop = json_encode($propinfo);

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

			$mb_id = '';
			$wr_name = "Agent";
			$wr_password = get_encrypt_string($gen_password);
			$wr_email = "";
			$wr_homepage = "";
		}

		$wr_subject = "ALF-52의 " . $propinfo["Name"] . " 파일 동기화";
		$wr_content = "ALF-52의 " . $propinfo["Name"] . " 파일 동기화";
		$wr_2 = "ALF-52"; // identify code
		$wr_3 = md5($stream); // md5 checksum
		$wr_4 = sha1($stream); // sha1 checksum
		$wr_5 = $clone_prop; // properties info
		$wr_6 = "";
		$wr_7 = "";
		$wr_8 = "";
		$wr_9 = "";
		$wr_10 = "";

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
	}
}
