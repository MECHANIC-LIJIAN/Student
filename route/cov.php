<?php

use think\facade\Route;

//后台路由
Route::group(
    'admin/cov',
    function () {
        Route::get('index', 'admin/cov/index');
        Route::post('newreport', 'admin/cov/newreport');
        Route::rule('per_day_reports/:date', 'admin/cov/perDayReports','GET|POST');
        Route::rule('down_per_day_reports', 'admin/cov/downPerDayReports','GET|POST');

        Route::rule('reporter/[:date]', 'admin/cov/reporter','GET|POST');
        Route::post('cov_ajax_upload', 'admin/cov/ajaxUpload');
    }
);
