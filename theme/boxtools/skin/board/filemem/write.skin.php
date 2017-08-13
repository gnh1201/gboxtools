<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

// 새 글일 때
$w_flags = array('u', 'r');
if(!in_array($w, $w_flags)) {
	if(isset($member) && array_key_exists("mb_nick", $member)) {
		$name = $member['mb_nick'];
	}
	
	$subject = $name . "님의 새로운 업로드	" . G5_TIME_YMDHIS;
	$content = $subject;
}
?>

<section>
  <!-- Horizontal Form -->
  <div class="box box-info">
	<div class="box-header with-border">
	  <h3 class="box-title">
	    <span class="title_icon" style="margin-right: 5px;"><img src="<?php echo $board_skin_url; ?>/img/title/new_16x16.png" alt=""/></span>
		<?php echo $g5['title'] ?>
	  </h3>
	</div>
	<!-- /.box-header -->
	<!-- form start -->
	<form class="form-horizontal" name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
      <div>
        <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>"/>
        <input type="hidden" name="w" value="<?php echo $w ?>"/>
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>"/>
        <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>"/>
        <input type="hidden" name="sca" value="<?php echo $sca ?>"/>
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>"/>
        <input type="hidden" name="stx" value="<?php echo $stx ?>"/>
        <input type="hidden" name="spt" value="<?php echo $spt ?>"/>
        <input type="hidden" name="sst" value="<?php echo $sst ?>"/>
        <input type="hidden" name="sod" value="<?php echo $sod ?>"/>
        <input type="hidden" name="page" value="<?php echo $page ?>"/>
		<?php
		$option = '';
		$option_hidden = '';
		if ($is_notice || $is_html || $is_secret || $is_mail) {
			$option = '';
			if ($is_notice) {
				$option .= "\n".'<label for="notice"><input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'>'."\n".'공지</label>';
			}

			if ($is_html) {
				if ($is_dhtml_editor) {
					$option_hidden .= '<input type="hidden" value="html1" name="html">';
				} else {
					$option .= "\n".'<label for="html"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'html</label>';
				}
			}

			if ($is_secret) {
				if ($is_admin || $is_secret==1) {
					$option .= "\n".'<label for="secret"><input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'>'."\n".'비밀글</label>';
				} else {
					$option_hidden .= '<input type="hidden" name="secret" value="secret">';
				}
			}

			if ($is_mail) {
				$option .= "\n".'<label for="mail"><input type="checkbox" id="mail" name="mail" value="mail" '.$recv_email_checked.'>'."\n".'답변메일받기</label>';
			}
		}

		echo $option_hidden;
		?>
	  </div>
	
	  <div class="box-body">
		<?php if ($is_name) { ?>
		<div class="form-group">
		  <label for="wr_name" class="col-sm-2 control-label">이름</label>

		  <div class="col-sm-10">
			<input class="form-control" type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required="required" class="frm_input required" size="10" maxlength="20" placeholder="이름" />
		  </div>
		</div>
		<?php } ?>
		
		<?php if ($is_password) { ?>
		<div class="form-group">
		  <label for="wr_password" class="col-sm-2 control-label">비밀번호</label>

		  <div class="col-sm-10">
			<input class="form-control" type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="frm_input <?php echo $password_required ?>" maxlength="20" placeholder="비밀번호"/>
		  </div>
		</div>
		<?php } ?>
		
        <?php if ($is_email) { ?>
		<div class="form-group">
		  <label for="wr_email" class="col-sm-2 control-label">이메일</label>

		  <div class="col-sm-10">
            <input class="form-control" type="text" name="wr_email" value="<?php echo $email ?>" id="wr_email" class="frm_input email" size="50" maxlength="100" placeholder="이메일"/>
		  </div>
		</div>
        <?php } ?>

        <?php if ($is_homepage) { ?>
		<div class="form-group">
		  <label for="wr_homepage" class="col-sm-2 control-label">홈페이지</label>

		  <div class="col-sm-10">
            <input class="form-control" type="text" name="wr_homepage" value="<?php echo $homepage ?>" id="wr_homepage" class="frm_input" size="50" placeholder="홈페이지"/>
		  </div>
		</div>
		<?php } ?>

		<?php if ($option) { ?>
		<div class="form-group">
		  <label for="inputEmail3" class="col-sm-2 control-label">옵션</label>

		  <div class="col-sm-10">
			<div class="checkbox">
			  <?php echo $option; ?>
			</div>
		  </div>
		</div>
		<?php } ?>

		<div class="form-group">
		  <label for="wr_subject" class="col-sm-2 control-label">제목</label>

		  <div class="col-sm-10">
			<input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required="required" class="form-control required" size="50" maxlength="255" placeholder="제목"/>
		  </div>
		</div>

		<div class="form-group">
		  <label for="wr_content" class="col-sm-2 control-label">내용</label>

		  <div class="col-sm-10">
			<textarea class="form-control" name="wr_content" id="wr_content" rows="3" placeholder="내용"><?php echo $content; ?></textarea>
		  </div>
		</div>

        <?php for ($i=1; $is_link && $i<=G5_LINK_COUNT; $i++) { ?>
		<div class="form-group">
		  <label for="wr_link<?php echo $i ?>" class="col-sm-2 control-label">링크 #<?php echo $i ?></label>

		  <div class="col-sm-10">
			<input type="text" class="form-control" id="wr_link<?php echo $i ?>" value="<?php if($w=="u"){echo$write['wr_link'.$i];} ?>" id="wr_link<?php echo $i ?>" placeholder="링크 #<?php echo $i ?>"/>
		  </div>
		</div>
        <?php } ?>

        <?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
		<div class="form-group">
		  <label class="col-sm-2 control-label">파일 #<?php echo $i+1 ?></label>

		  <div class="col-sm-10">
			<input type="file" name="bf_file[]" title="파일첨부 <?php echo $i+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input">
			<?php if ($is_file_content) { ?>
			<input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="frm_file frm_input" size="50">
			<?php } ?>
			<?php if($w == 'u' && $file[$i]['file']) { ?>
			<input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i;  ?>]" value="1"> <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source'].'('.$file[$i]['size'].')';  ?> 파일 삭제</label>
			<?php } ?>
		  </div>
		</div>
        <?php } ?>
		
        <?php if ($is_guest) { //자동등록방지  ?>
		<div class="form-group">
		  <label for="wr_key" class="col-sm-2 control-label">자동등록방지</label>

		  <div class="col-sm-10">
			<?php echo $captcha_html; ?>
          </div>
		</div>
        <?php } ?>

	  </div>
	  <!-- /.box-body -->
	  <div class="box-footer">
		<a href="./board.php?bo_table=<?php echo $bo_table ?>" class="btn btn-default">취소</a>
		<input type="submit" value="등록완료" id="btn_submit" accesskey="s" class="btn btn-info pull-right">
	  </div>
	  <!-- /.box-footer -->
	</form>
  </div>
</section>

<script type="text/javascript">
<?php if($write_min || $write_max) { ?>
    // 글자수 제한
    var char_min = parseInt(<?php echo $write_min; ?>); // 최소
    var char_max = parseInt(<?php echo $write_max; ?>); // 최대
    check_byte("wr_content", "char_count");

    $(function() {
        $("#wr_content").on("keyup", function() {
            check_byte("wr_content", "char_count");
        });
    });

<?php } ?>
    function html_auto_br(obj)
    {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "html2";
            else
                obj.value = "html1";
        }
        else
            obj.value = "";
    }

    function fwrite_submit(f)
    {
        <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
            type: "POST",
            data: {
                "subject": f.wr_subject.value,
                "content": f.wr_content.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                subject = data.subject;
                content = data.content;
            }
        });

        if (subject) {
            alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
            f.wr_subject.focus();
            return false;
        }

        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            if (typeof(ed_wr_content) != "undefined")
                ed_wr_content.returnFalse();
            else
                f.wr_content.focus();
            return false;
        }

        if (document.getElementById("char_count")) {
            if (char_min > 0 || char_max > 0) {
                var cnt = parseInt(check_byte("wr_content", "char_count"));
                if (char_min > 0 && char_min > cnt) {
                    alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
                    return false;
                }
                else if (char_max > 0 && char_max < cnt) {
                    alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
                    return false;
                }
            }
        }

        <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
</script>
<!-- } 게시물 작성/수정 끝 -->