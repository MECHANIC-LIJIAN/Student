<?php

use think\facade\Route;

//后台路由
Route::group(
    'admin/cov',
    function () {
        Route::get('index', 'index');
        Route::rule('single', 'single', 'GET|POST');
        Route::post('cov_ajax_upload', 'ajaxUpload');
    }
)->prefix('admin/cov/');
