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
    
    

    Route::rule('import', 'excel/Import/index', 'GET|POST');
    Route::rule('createTemplateFirst', 'excel/Import/createTemplateFirst', 'GET|POST');
    
    Route::rule('tableupload', 'excel/Import/upload', 'POST');
    Route::rule('template/[:id]', 'excel/template/index', 'GET|POST');
