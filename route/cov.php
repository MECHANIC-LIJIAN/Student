<?php

use think\facade\Route;

//后台路由
Route::group(
    'admin/cov',
    function () {
        Route::get('index', 'admin/cov/index');
        Route::rule('reporter/[:date]', 'admin/cov/reporter','GET|POST');
        Route::rule('newreport', 'admin/cov/newreport','GET|POST');
        Route::rule('per_day_reports/:date', 'admin/cov/perDayReports','GET|POST');
        Route::post('cov_ajax_upload', 'admin/cov/ajaxUpload');
    }
);
