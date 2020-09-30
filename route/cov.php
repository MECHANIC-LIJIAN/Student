<?php

use think\facade\Route;

//后台路由
Route::group(
    'admin/cov',
    function () {
        Route::get('index', 'admin/cov/index');
        Route::post('newreport', 'admin/cov/newReport');
        Route::rule('per_day_reports/:date', 'admin/cov/perDayReports', 'GET|POST');

        Route::post('del_one_report', 'admin/cov/delOneReport');
        Route::rule('down_per_day_reports', 'admin/cov/downPerDayReports', 'GET|POST');
        Route::rule('add_members', 'admin/cov/addMembers', 'GET|POST');

        Route::get('index_b', 'admin/cov/indexB');
        Route::rule('reporter/[:date]', 'admin/cov/reporter', 'GET|POST');
        Route::post('cov_ajax_upload', 'admin/cov/ajaxUpload');
        Route::group(
            'leave',
            function () {
                Route::get('index', 'admin/leave/index');
                Route::post('newreport', 'admin/leave/newReport');
                Route::rule('per_day_reports/:date', 'admin/leave/perDayReports', 'GET|POST');
                Route::rule('down_per_day_reports', 'admin/leave/downPerDayReports', 'GET|POST');
                Route::post('del_one_report', 'admin/leave/delOneReport');

                Route::get('index_b', 'admin/leave/indexB');
                Route::rule('reporter/[:date]', 'admin/leave/reporter', 'GET|POST');
                Route::post('cov_leave_ajax_upload', 'admin/leave/ajaxUpload');
            });

    }
);
