<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<section>
  <div class="box">
	<div class="box-header with-border">
	  <h3 class="box-title">
		<span class="title_icon" style="margin-right: 5px;"><img src="<?php echo $board_skin_url; ?>/img/title/lock_16x16.png" alt=""/></span>
		모듈설정 관리
	  </h3>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
	  <table class="table table-bordered">
		<tr>
		  <th style="width: 45px;">#</th>
		  <th>설정항목</th>
		  <th style="width: 200px;">자동암호화</th>
		  <th style="width: 200px;">등록자</th>
		  <th style="width: 200px;">최근변경</th>
		  <th style="width: 120px;">식별코드</th>
		</tr>
<?php for ($i=0; $i<count($list); $i++) { ?>
		<tr>
		  <td>
			<?php
			if ($list[$i]['is_notice']) // 공지사항
				echo '<strong>공지</strong>';
			else if ($wr_id == $list[$i]['wr_id'])
				echo "<span class=\"bo_current\">열람중</span>";
			else
				echo $list[$i]['num'];
			?>
		  </td>
		  <td><a href="<?php echo $list[$i]['href']; ?>"><?php echo $list[$i]['subject'] ?></a></td>
		  <td><?php echo empty($list[$i]['wr_3']) ? "사용안함" : "사용함"; ?></td>
		  <td><?php echo $list[$i]['name'] ?></td>
		  <td><?php echo $list[$i]['wr_last']; ?></td>
		  <td><?php echo $list[$i]['wr_2'] ?></td>
		</tr>
<?php } ?>
	  </table>
	</div>
	
	<!-- /.box-body -->
	<div class="box-footer clearfix">
	  <?php if ($write_href) { ?><a href="<?php echo $write_href ?>" class="btn btn-default">신규등록</a><?php } ?>
	  <?php if ($list_href) { ?><a href="<?php echo $list_href ?>" class="btn btn-default">목록가기</a><?php } ?>
	  <a href="./board.php?bo_table=<?php echo $bo_table ?>" class="btn btn-default">선택삭제</a>

	  <ul class="pagination pagination-sm no-margin pull-right">
		<li><a href="#">&laquo;</a></li>
		<li><a href="#">1</a></li>
		<li><a href="#">2</a></li>
		<li><a href="#">3</a></li>
		<li><a href="#">&raquo;</a></li>
	  </ul>

	  </div>
	</div>
  <!-- /.box -->
</section>

<?php if ($is_checkbox) { ?>
<script type="text/javascript">
//<!--<![CDATA[
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = "./board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == "copy")
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./move.php";
    f.submit();
}
//]]>-->
</script>
<?php } ?>
<!-- } 게시판 목록 끝 -->
