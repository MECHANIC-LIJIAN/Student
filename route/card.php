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

Route::group(
    'card',
    function () {
        Route::rule('/', 'card/index/index', 'GET|POST');
        Route::rule('read', 'card/index/read', 'POST');
        Route::rule('create', 'card/index/create', 'GET|POST');
        Route::rule('createQrCode', 'card/index/createQrCode', 'GET|POST');
    }
);
