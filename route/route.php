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
        Route::rule('register', 'admin/Index/register', 'GET|POST');
        Route::rule('checkid/', 'admin/Index/checkid/', 'GET|POST');
        Route::rule('forget', 'admin/Index/forget', 'GET|POST');
        Route::rule('reset', 'admin/Index/reset', 'POST');

        Route::rule('index', 'admin/Home/index', 'GET|POST');
        Route::rule('logout', 'admin/Home/logout', 'POST');

        Route::rule('templateList', 'admin/Templates/list', 'GET|POST');
        Route::rule('templateControl', 'admin/Templates/control', 'GET|POST');
        Route::rule('templateAdd', 'admin/Templates/add', 'GET|POST');
        Route::rule('templateDel', 'admin/Templates/del', 'POST');

        Route::rule('templateData/[:id]', 'admin/Templates/detail', 'GET')->ext();
        Route::rule('templateDataList', 'admin/Templates/dataList', 'POST')->ext('do');

        #创建模板
        Route::group('createTemplate', function () {
            Route::rule('First', 'admin/Import/createTemplateFirst', 'GET|POST');
            Route::rule('Second', 'admin/Import/createTemplateSecond', 'GET|POST');
            Route::rule('Third', 'admin/Import/createTemplateThird', 'GET|POST');
            Route::rule('upload', 'admin/Import/upload', 'GET|POST')->ext('do');
        });

        #用户
        Route::rule('memberList', 'admin/Member/list', 'GET|POST');
        Route::rule('memberEdit', 'admin/Member/edit', 'GET|POST');
        Route::rule('memberAdd', 'admin/Member/add', 'GET|POST');
        Route::rule('memberDel', 'admin/Member/del', 'GET|POST');
        #管理员
        Route::rule('adminList', 'admin/Admin/list', 'GET');
        Route::rule('adminAdd', 'admin/Admin/add', 'GET|POST');
        Route::rule('adminStatus', 'admin/Admin/status', 'POST');
        Route::rule('adminEdit/[:id]', 'admin/Admin/edit', 'GET|POST');
        Route::rule('adminDel', 'admin/Admin/del', 'GET|POST');

    }
);
