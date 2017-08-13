<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<?php if ($is_admin == 'super') {  ?><!-- <div style='float:left; text-align:center;'>RUN TIME : <?php echo get_microtime()-$begin_time; ?><br></div> --><?php }  ?>

<!-- ie6,7에서 사이드뷰가 게시판 목록에서 아래 사이드뷰에 가려지는 현상 수정 -->
<!--[if lte IE 7]>
<script type="text/javascript">
$(function() {
    var $sv_use = $(".sv_use");
    var count = $sv_use.length;

    $sv_use.each(function() {
        $(this).css("z-index", count);
        $(this).css("position", "relative");
        count = count - 1;
    });
});
</script>
<![endif]-->

<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<script type="text/javascript">
//<!--<![CDATA[
  $.widget.bridge('uibutton', $.ui.button);
//]]>-->
</script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/bootstrap/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/morris/morris.min.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/sparkline/jquery.sparkline.min.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/knob/jquery.knob.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/daterangepicker/daterangepicker.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/plugins/fastclick/fastclick.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/dist/js/app.min.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/dist/js/pages/dashboard.js"></script>
<script src="<?php echo G5_THEME_CSS_URL; ?>/adminlte/dist/js/demo.js"></script>

</body>
</html>
<?php echo html_end(); // HTML 마지막 처리 함수 : 반드시 넣어주시기 바랍니다. ?>