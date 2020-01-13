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


    Route::rule('/', 'index/index/index', 'GET|POST');
    // 动态注册域名的路由规则
    Route::rule('excel', 'excel/Excel/excel', 'GET|POST');
    Route::rule('importExcel', 'excel/OutExcel/index', 'GET|POST');
    Route::rule('importChaxun', 'excel/Excel/importChaxun', 'GET|POST');
    Route::rule('reset', 'excel/Excel/reset', 'GET|POST');
    Route::rule('unique', 'excel/Excel/unique', 'GET|POST');

    Route::rule('getinfo', 'excel/GetInfo/index', 'GET|POST');
    
    Route::rule('fill/:id', 'admin/Template/readTemplate', 'GET|POST');
    Route::group('createTemplate', function () {
        Route::rule('First', 'admin/Import/createTemplateFirst', 'GET|POST');
        Route::rule('Second', 'admin/Import/createTemplateSecond', 'GET|POST');
        Route::rule('Third', 'admin/Import/createTemplateThird', 'GET|POST');
    });
    Route::rule('upload', 'admin/Import/upload', 'GET|POST')->ext('do');
    Route::rule('import', 'admin/Import/index', 'GET|POST');

    
