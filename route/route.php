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
// Route::rule('saveTestdatas', 'index/Template/saveTestdatas', 'GET|POST');
// Route::rule('getTestdatas', 'index/Template/getTestdatas', 'GET|POST');

Route::rule('qr', 'admin/QrcodeClass/create', 'GET|POST');


Route::rule('/', 'index/index/index', 'GET|POST');

Route::rule('fill/:id', 'index/Template/readTemplate', 'GET|POST');

Route::rule('collect', 'index/Template/collect', 'GET|POST');

Route::rule('collectUpdate', 'index/Template/collectUpdate', 'POST');

// Route::get('ip','index/Ip/index');

Route::rule('datasToMysql', 'index/Template/datasToMysql', 'GET|POST');
