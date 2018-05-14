<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
//echo 1;exit;
// [ 应用入口文件 ]
// 定义appID   wx6776429a5eb23a64 正式
define('WX_APPID', 'wx8e4f59fdf635407f');
//define('WX_APPID', 'wx6776429a5eb23a64');//正式
// 定义appsecret   bd62c90a0c1eca2e8ae55cb89729f1bb 正式
define('WX_APPSCRET', 'fe224c15419d6f46bf8b802aa6b93b5d');
//define('WX_APPSCRET', 'bd62c90a0c1eca2e8ae55cb89729f1bb');
// 定义应用目录
//define('APP_PATH', __DIR__ . '/../application/');
// 加载框架引导文件
//require __DIR__ . '/../thinkphp/start.php';


// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 开启调试模式
define('APP_DEBUG', true);
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
