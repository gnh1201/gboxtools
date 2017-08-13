<?php
/** retrieve_cmis.inc.php **/
/** Component for retrieve cmis protocol **/

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

use \GuzzleHttp\Client as GuzzleHttpClient;
use \Dkd\PhpCmis\SessionParameter as CmisSessionParameter;
use \Dkd\PhpCmis\Enum\BindingType as CmisBindingType;
use \Dkd\PhpCmis\Data\FolderInterface as CmisFolderInterface;
use \Dkd\PhpCmis\Data\DocumentInterface as CmisDocumentInterface;
use \Dkd\PhpCmis\SessionFactory as CmisSessionFactory;
use \Dkd\PhpCmis\Enum\PropertyType as CmisEnumPropertyType;
use RandomLib\Factory as RandomFactory;
use SecurityLib\Strength as SecurityStrength;

if (!is_file(__DIR__ . '/conf/Configuration.php')) {
    die("CMIS 서버에 접속하기 위한 인증 정보를 찾을 수 없습니다.\"" . __DIR__ . "/conf/Configuration.php\".\n");
} else {
    require_once(__DIR__ . '/conf/Configuration.php');
}

$httpInvoker = new GuzzleHttpClient(
    array(
        'defaults' => array(
            'auth' => array(
                CMIS_BROWSER_USER,
                CMIS_BROWSER_PASSWORD
            )
        )
    )
);

$parameters = array(
    CmisSessionParameter::BINDING_TYPE => CmisBindingType::BROWSER,
    CmisSessionParameter::BROWSER_URL => CMIS_BROWSER_URL,
    CmisSessionParameter::BROWSER_SUCCINCT => false,
    CmisSessionParameter::HTTP_INVOKER_OBJECT => $httpInvoker,
);

$sessionFactory = new CmisSessionFactory();

// If no repository id is defined use the first repository
if (CMIS_REPOSITORY_ID === null) {
    $repositories = $sessionFactory->getRepositories($parameters);
    $parameters[CmisSessionParameter::REPOSITORY_ID] = $repositories[0]->getId();
} else {
    $parameters[CmisSessionParameter::REPOSITORY_ID] = CMIS_REPOSITORY_ID;
}

$session = $sessionFactory->createSession($parameters);

// Get the root folder of the repository
$rootFolder = $session->getRootFolder();

echo "<pre>";

echo '+ [ROOT FOLDER]: ' . $rootFolder->getName() . "\n";

printFolderContent($rootFolder);

function printFolderContent(CmisFolderInterface $folder, $levelIndention = '  ')
{
    $i = 0;
    foreach ($folder->getChildren() as $children) {
        echo $levelIndention;
        $i++;
        if ($i > 10) {
            echo "| ...\n";
            break;
        }

        if ($children instanceof CmisFolderInterface) {
            echo '+ [FOLDER]: ' . $children->getName() . "\n";
            printFolderContent($children, $levelIndention . '  ');
        } elseif ($children instanceof CmisDocumentInterface) {
            echo '- [DOCUMENT]: ' . $children->getName() . "\n";
			
			$propinfo = array();
			foreach ($children->getProperties() as $property) {
				$propinfo_key = str_replace(' ', '', $property->getDisplayName());
				$propinfo_value = $property->getFirstValue();

				$propinfo[$propinfo_key] = $propinfo_value;
			}

			echo "- [CLONE]: 등록을 시작합니다.\n";
			// multi processing
			//$pid = pcntl_fork();
			//if (!$pid) {
				// clone file
				clone_file_stream($propinfo, $children->getContentStream());
			//}
        } else {
            echo '- [ITEM]: ' . $children->getName() . "\n";
        }
    }
}

echo "</pre>";

// clone file stream
function clone_file_stream($propinfo, $stream) {
	global $g5, $member;
	
	
	$pid = pcntl_fork();

    if ($pid == -1) {
        alert("[ERROR] could not fork\n");
    }

	$clone_result = false;
	
	$clone_table = "filemem";
	$clone_write_table = $g5['write_prefix'] . $clone_table;
	$clone_board = sql_fetch("select * from {$clone_write_table} where bo_table = '{$clone_table}'");
	$clone_prop = json_encode($propinfo);
	$clone_md5 = md5($stream);
	$clone_sha1 = sha1($stream);

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

	$clone_file_name = $propinfo["Name"];
	$wr_subject = $clone_file_name;
	$wr_content = "ALF-52의 " . $clone_file_name . " 파일 동기화";
	$wr_2 = "ALF-52"; // identify code
	$wr_3 = $clone_md5; // md5 checksum
	$wr_4 = $clone_sha1; // sha1 checksum
	$wr_5 = $clone_prop; // properties info
	$wr_6 = "";
	$wr_7 = "";
	$wr_8 = "";
	$wr_9 = $propinfo["VersionLabel"];
	$wr_10 = $propinfo["AlfrescoNodeRef"];

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
	
	// 파일 쓰기 진행
	$factory = new RandomFactory();
	$generator = $factory->getGenerator(new SecurityStrength(SecurityLib\Strength::MEDIUM));
	$gen_source = "0123456789abcdefghijklmnopqrstuvwxyz";
	$gen_name = $generator->generateString(16, $gen_source);
	$clone_file_store_name = $gen_name . ".raw";
	$clone_file_path = G5_DATA_PATH . "/file/" . $clone_table . "/" . $clone_file_store_name;

	if(fwrite_with_content($clone_file_path, $stream)) {
		$clone_file_imageinfo = @getimagesize($clone_file_path);
		$clone_file_size = filesize($clone_file_path);

		// 파일 정보 등록
		$clone_id = sql_insert_id();
		echo "clone_id: " . $clone_id . "\n";
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
	}

	return $clone_result;
}

// 파일 쓰기
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

?>
