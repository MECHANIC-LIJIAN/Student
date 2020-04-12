<?php

use think\facade\Route;

Route::group(
    'admin/auth',
    function () {
        Route::rule('/', 'admin/rule/index', "GET|POST");
        Route::rule('add', 'admin/rule/add', "POST");
        Route::rule('edit', 'admin/rule/edit', "POST");
        Route::rule('delete', 'admin/rule/delete', "POST");

        Route::rule('group', 'admin/rule/group', "GET|POST");
        Route::rule('add_group', 'admin/rule/add_group', "POST"); 
        Route::rule('edit_group', 'admin/rule/edit_group', "POST");
        Route::rule('delete_group', 'admin/rule/delete_group', "POST");
        
        Route::rule('rule_group', 'admin/rule/rule_group', "GET|POST");
        Route::rule('check_user', 'admin/rule/check_user', "GET|POST");
        Route::rule('add_user_to_group', 'admin/rule/add_user_to_group', "GET|POST");
        Route::rule('delete_user_from_group', 'admin/rule/delete_user_from_group', "POST");
        
        Route::rule('admin_user_list', 'admin/rule/admin_user_list', "GET|POST");
        Route::rule('add_admin', 'admin/rule/add_admin', "GET|POST");
        Route::rule('edit_admin', 'admin/rule/edit_group', "GET|POST");
    }
);
