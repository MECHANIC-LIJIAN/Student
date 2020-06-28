<?php

use think\facade\Route;

//后台路由
Route::group(
    'admin/templates',
    function () {
        Route::rule('list', 'admin/Templates/list', 'GET|POST');
        Route::rule('control', 'admin/Templates/control', 'POST');
        Route::rule('add', 'admin/Templates/add', 'GET|POST');
        Route::rule('del', 'admin/Templates/del', 'POST');

        #文件创建模板
        Route::group('createByFile', function () {
            Route::rule('First', 'admin/Import/First', 'GET|POST');
            Route::rule('Second', 'admin/Import/Second', 'GET|POST');
            // Route::rule('Third', 'admin/Import/Third', 'GET|POST');
        });
        #手动创建模板
        Route::group('createByHand', function () {
            Route::rule('index', 'admin/Hand/index', 'GET|POST');
            Route::rule('add', 'admin/Hand/add', 'POST')->ext('do');
        });

         #创建word模板
         Route::group('createWord', function () {
            Route::rule('index', 'admin/hand/word', 'GET|POST');
            Route::rule('add', 'admin/Hand/add', 'POST')->ext('do');
        });
    }
);
