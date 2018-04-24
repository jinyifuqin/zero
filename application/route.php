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
use think\Route;
Route::group('admin',[
//    '/' => 'admin/index/index', // 首页访问路由
    'login' => ['admin/index/login', ['method' => 'get']],
    '/$' => ['admin/index/index', ['method' => 'get']],
    'welcome' => ['admin/index/welcome', ['method' => 'get']],
    '/getEr' => ['admin/index/getEr', ['method' => 'get']],
    '/create' => ['admin/index/createUser', ['method' => 'get']],
    ':id'   => ['Blog/read', ['method' => 'get'], ['id' => '\d+']],
    ':name' => ['Blog/read', ['method' => 'post']],
    '/' => 'admin/index/index', // 首页访问路由
]);

Route::get([
    '/' => 'index', // 首页访问路由
    'my' => 'ajax', // 静态地址路由
//    'admin' => 'admin/index/index', // 静态地址路由
//    'admin/login' => 'admin/index/login', // 静态地址路由
//    'admin/welcome' => 'admin/index/welcome', // 静态地址路由
    'blog/:id' => 'Blog/read', // 静态地址和动态地址结合
    'new/:year/:month/:day'=>'News/read', // 静态地址和动态地址结合
    'new/:id'=>'hello/index/index',
    'blog/:name'=>'Blog/detail',
// ':user/:blog_id'=>'Blog/read',// 全动态地址
]);
return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
