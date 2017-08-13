<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

// is_link flag
$is_attach_link = false;
foreach($view['link'] as $link) {
	$is_attach_link = is_null($link) ? false : true;
}
?>

<article>
  <div class="box box-solid">
	<div class="box-header with-border">
	  <i class="fa fa-text-width"></i>

	  <h3 class="box-title">암호키 생성정보</h3>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
	  <dl class="dl-horizontal">
		<dt>제목</dt>
		<dd><?php echo $view['subject']; ?></dd>
	  
<?php if ($category_name) { ?>
		<dt>분류</dt>
		<dd><?php echo $view['ca_name']; ?></dd>
<?php } ?>

		<dt>작성자</dt>
		<dd><?php echo $view['name'] ?><?php if ($is_ip_view) { echo "&nbsp;($ip)"; } ?></dd>
		
		<dt>작성일</dt>
		<dd><?php echo date("y-m-d H:i", strtotime($view['wr_datetime'])) ?></dd>
		
		<dt>조회</dt>
		<dd><?php echo number_format($view['wr_hit']) ?>회</dd>
		
		<dt>댓글</dt>
		<dd><?php echo number_format($view['wr_comment']) ?>건</dd>

<?php
		if ($view['file']['count']) {
			$cnt = 0;
			for ($i=0; $i<count($view['file']); $i++) {
				if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view'])
					$cnt++;
			}
		}

		if($cnt) {
?>

		<dt>첨부파일</dt>
		<dd>
			<ul>
<?php
				// 가변 파일
				for ($i=0; $i<count($view['file']); $i++) {
					if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
 ?>
				<li>
					<a href="<?php echo $view['file'][$i]['href'];  ?>" class="view_file_download">
						<img src="<?php echo $board_skin_url ?>/img/icon_file.gif" alt="첨부">
						<strong><?php echo $view['file'][$i]['source'] ?></strong>
						<?php echo $view['file'][$i]['content'] ?> (<?php echo $view['file'][$i]['size'] ?>)
					</a>
					<span class="bo_v_file_cnt"><?php echo $view['file'][$i]['download'] ?>회 다운로드</span>
					<span>DATE : <?php echo $view['file'][$i]['datetime'] ?></span>
				</li>
			</ul>
		</dd>
<?php
				}
			}
		} else {
?>
		<dt>첨부파일</dt>
		<dd>첨부된 파일이 없습니다.</dd>
<?php
			}
?>

<?php
		if($is_link) {
?>
		<dt>관련링크</dt>
		<dd>
			<ul>
<?php
			// 링크
			$cnt = 0;
			for ($i=1; $i<=count($view['link']); $i++) {
				if ($view['link'][$i]) {
					$cnt++;
					$link = cut_str($view['link'][$i], 70);
?>
				<li>
					<a href="<?php echo $view['link_href'][$i]; ?>" target="_blank">
						<img src="<?php echo $board_skin_url; ?>/img/icon_link.gif" alt="관련링크"/>
						<strong><?php echo $link; ?></strong>
					</a>
					<span class="bo_v_link_cnt"><?php echo $view['link_hit'][$i]; ?>회 연결</span>
				</li>
<?php
				}
			}
?>
			</ul>
		</dd>
<?php
		} else {
?>
		<dt>관련링크</dt>
		<dd>관련 링크가 없습니다.</dd>
<?php
		}
?>
        </ul>
		</dd>
		
		<dt>내용</dt>
		<dd><?php echo $view['content']; ?></dd>

<?php
		// 공개키 / 개인키 구조
		$privkey_name = $view['wr_4'];
		$pubkey_name = $view['wr_5'];
		$enckey_name = $view['wr_6'];
		$pubkey_content = $view['wr_3'];
?>

		<dt>개인키</dt>
		<dd><?php echo $privkey_name; ?></dd>
		
		<dt>공개키</dt>
		<dd>
			<p><?php echo $pubkey_name; ?></p>
		</dd>
		<dd>
			<pre><?php echo $pubkey_content; ?></pre>
		</dd>

		<dt>암호키</dt>
		<dd>
			<p><?php echo $enckey_name; ?></p>
		</dd>
	  </dl>
	</div>
	<!-- /.box-body -->
	<div class="box-footer clearfix">
        <div class="pull-left">
<?php if ($prev_href || $next_href) { ?>
            <?php if ($prev_href) { ?><a href="<?php echo $prev_href ?>" class="btn btn-default">이전글</a><?php } ?>
            <?php if ($next_href) { ?><a href="<?php echo $next_href ?>" class="btn btn-default">다음글</a><?php } ?>
<?php } ?>
			<a class="btn btn-default" href="<?php echo G5_PLUGIN_URL; ?>/boxtools/secure/enckey_down.php?bo_table=<?php echo $bo_table; ?>&wr_id=<?php echo $wr_id; ?>">암호키 내려받기</a>
        </div>
		
        <div class="pull-right">
            <?php if ($update_href) { ?><a href="<?php echo $update_href; ?>" class="btn btn-default">수정</a><?php } ?>
            <?php if ($delete_href) { ?><a href="<?php echo $delete_href; ?>" class="btn btn-default" onclick="del(this.href); return false;">삭제</a><?php } ?>
            <?php if ($copy_href) { ?><a href="<?php echo $copy_href; ?>" class="btn btn-default" onclick="board_move(this.href); return false;">복사</a><?php } ?>
            <?php if ($move_href) { ?><a href="<?php echo $move_href; ?>" class="btn btn-default" onclick="board_move(this.href); return false;">이동</a><?php } ?>
            <?php if ($search_href) { ?><a href="<?php echo $search_href; ?>" class="btn btn-default">검색</a><?php } ?>
            <a href="<?php echo $list_href; ?>" class="btn btn-default">목록</a>
            <?php if ($reply_href) { ?><a href="<?php echo $reply_href; ?>" class="btn btn-default">답변</a><?php } ?>
            <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="btn btn-default">글쓰기</a><?php } ?>
        </div>
	</div>
  </div>
  <!-- /.box -->
</article>