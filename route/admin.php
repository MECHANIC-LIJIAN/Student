<?php

use think\facade\Route;

//后台路由
Route::group(
    'admin',
    function () {
        Route::rule('/', 'admin/Home/index', 'GET|POST');
        Route::rule('index', 'admin/Home/index', 'GET|POST');
        Route::rule('help', 'admin/Home/help', 'GET|POST');
        Route::rule('logout', 'admin/Home/logout', 'POST');
        Route::rule('edit_info', 'admin/Home/edit', 'GET|POST');

        Route::rule('login', 'admin/Index/login', 'GET|POST');
        Route::rule('register', 'admin/Index/register', 'GET|POST');
        Route::rule('checkid/', 'admin/Index/checkid/', 'GET|POST');
        Route::rule('forget', 'admin/Index/forget', 'GET|POST');
        Route::rule('reset', 'admin/Index/reset', 'POST');


        #单个表单数据
        Route::group('template', function () {
            Route::rule('detail/[:id]', 'admin/Template/index', 'GET')->ext();
            Route::rule('ajax_data', 'admin/Template/dataList', 'POST')->ext('do');
            Route::rule('ajax_no_data', 'admin/Template/getNoData', 'POST')->ext('do');
            Route::rule('ajax_del', 'admin/Template/del', 'POST')->ext('do');
            Route::rule('ajax_export', 'admin/Template/export', 'POST')->ext('do');

            Route::rule('export_word','admin/Word/export_word', 'GET');
        });

        #数据集
        Route::group('mydatas', function () {
            Route::rule('index', 'admin/MyData/index', 'GET|POST');
            Route::rule('create', 'admin/MyData/create', 'GET|POST');
            Route::rule('createByFile', 'admin/MyData/createByFile', 'POST')->ext('do');
            Route::rule('createByText', 'admin/MyData/createByText', 'POST')->ext('do');
            Route::rule('append/[:id]', 'admin/MyData/append', 'GET|POST');
            Route::rule('dataList/[:id]', 'admin/MyData/read', 'GET|POST')->ext();
            Route::rule('del_data', 'admin/MyData/delete', 'POST')->ext('do');
        });

        #用户
        Route::rule('adminList', 'admin/Admin/list', 'GET');
        Route::rule('adminAdd', 'admin/Admin/add', 'GET|POST');
        Route::rule('adminStatus', 'admin/Admin/status', 'POST');
        Route::rule('adminEdit/[:id]', 'admin/Admin/edit', 'GET|POST');
        Route::rule('adminDel', 'admin/Admin/del', 'GET|POST');
        Route::rule('system', 'admin/System/index', 'GET|POST');
    }
);
