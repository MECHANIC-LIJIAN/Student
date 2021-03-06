   <?php
//Auth配置
return [
    // auth配置
    'auth' => [
        'auth_on' => 1, // 权限开关
        'auth_type' => (int)env('auth.auth_type', 2), // 认证方式，1为实时认证；2为登录认证。
        'auth_group' => 'auth_group', // 用户组数据不带前缀表名
        'auth_group_access' => 'auth_group_access', // 用户-用户组关系不带前缀表
        'auth_rule' => 'auth_rule', // 权限规则不带前缀表
        'auth_user' => 'admin', // 用户信息不带前缀表
    ],
];