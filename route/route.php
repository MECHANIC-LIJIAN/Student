<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;

Route::rule('fill/:id', 'index/Template/readTemplate', 'GET|POST');
Route::rule('/', 'index/index/index', 'GET|POST');
Route::rule('collect', 'index/Template/collect', 'GET|POST');
Route::group(
    'checker',
    function () {
        Route::rule('checkSno', 'index/Checker/checkSno', 'POST')->ext('do');
    }
);
//后台路由
Route::group(
    'admin',
    function () {
        Route::rule('/', 'admin/Home/index', 'GET|POST');
        Route::rule('login', 'admin/Index/login', 'GET|POST');
        Route::rule('logout', 'admin/Home/logout', 'POST');
        Route::rule('register', 'admin/Index/register', 'GET|POST');
        Route::rule('checkid/', 'admin/Index/checkid/', 'GET|POST');
        Route::rule('forget', 'admin/Index/forget', 'GET|POST');
        Route::rule('reset', 'admin/Index/reset', 'POST');

        Route::group('templates', function () {
            Route::rule('list', 'admin/Templates/list', 'GET|POST');
            Route::rule('control', 'admin/Templates/control', 'GET|POST');
            Route::rule('add', 'admin/Templates/add', 'GET|POST');
            Route::rule('del', 'admin/Templates/del', 'POST');
        });
        #单个表单数据
        Route::group('template', function () {
            Route::rule('detail/[:id]', 'admin/Template/index', 'GET')->ext();
            Route::rule('ajax_data', 'admin/Template/dataList', 'POST')->ext('do');
            Route::rule('ajax_del', 'admin/Template/del', 'POST')->ext('do');
        });
        #文件创建模板
        Route::group('createByFile', function () {
            Route::rule('First', 'admin/Import/createTemplateFirst', 'GET|POST');
            Route::rule('Second', 'admin/Import/createTemplateSecond', 'GET|POST');
            Route::rule('Third', 'admin/Import/createTemplateThird', 'GET|POST');
            Route::rule('upload', 'admin/Import/upload', 'GET|POST')->ext('do');
        });
        #手动创建模板
        Route::group('createByHand', function () {
            Route::rule('index', 'admin/Hand/index', 'GET|POST');
            Route::rule('add', 'admin/Hand/add', 'POST')->ext('do');
        });

        #数据集
        Route::group('mydatas', function () {
            Route::rule('index', 'admin/MyData/index', 'GET|POST');
            Route::rule('create', 'admin/MyData/create', 'GET|POST');
            Route::rule('createByFile', 'admin/MyData/createByFile', 'POST');
            Route::rule('dataList/[:id]', 'admin/MyData/read', 'GET|POST')->ext();
            Route::rule('delete', 'admin/MyData/delete', 'POST')->ext('do');

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
