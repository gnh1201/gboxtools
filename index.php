<?php
define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/index.php');
    return;
}

include_once(G5_THEME_PATH.'/head.php');
?>


    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        통합현황
        <small>Control panel</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Startpage</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
				<h4>환영합니다.</h4>
				<p>좌측 메뉴에서 원하는 작업을 선택하여주세요.</p>
            </div>
            <!-- /.box-body -->
          <!-- /.box -->
        </section>
        <!-- right col -->
      </div>
      <!-- /.row (main row) -->

    </section>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>