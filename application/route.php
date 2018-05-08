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
    'check' => ['admin/index/check_user', ['method' => 'post']],
    '/$' => ['admin/index/index', ['method' => 'get']],
    'welcome' => ['admin/index/welcome', ['method' => 'get']],
    'getCaptcha' => ['admin/index/get_captcha', ['method' => 'get']],
    'signout' => ['admin/index/signOut', ['method' => 'get']],
    'brand$' => ['admin/index/brand', ['method' => 'get']],
    'brand/show/:id' => ['admin/index/brandShow', ['method' => 'get']],
    'addbrand' => ['admin/index/addbrand', ['method' => 'post']],
    'brandUpdate' => ['admin/index/brandUpdate', ['method' => 'post']],
    'brandDelById' => ['admin/index/brandDelById', ['method' => 'get']],
    'brandDelAll' => ['admin/index/brandDelAll', ['method' => 'get']],
    'items' => ['admin/item/index', ['method' => 'get']],
    'itemAdd' => ['admin/item/itemAdd', ['method' => 'get']],
    'itemDel/:id' => ['admin/item/itemDelById', ['method' => 'get']],
    'itemDelAll' => ['admin/item/itemDelAll', ['method' => 'get']],
    'itemStatus' => ['admin/item/itemStatus', ['method' => 'get']],//商品上下架
    'itemEdit/:id' => ['admin/item/itemEdit', ['method' => 'get']],
    'itemUpdate' => ['admin/item/itemUpdate', ['method' => 'post']],
    'itemSave' => ['admin/item/itemSave', ['method' => 'post']],
    'cats' => ['admin/item/catList', ['method' => 'get']],
    'addCat' => ['admin/item/addCat', ['method' => 'get']],
    'saveCat' => ['admin/item/saveCat', ['method' => 'post']],
    'catEdit/:id' => ['admin/item/catEdit', ['method' => 'get']],
    'catDelAll' => ['admin/item/catDelAll', ['method' => 'get']],
    'catDelById' => ['admin/item/catDelById', ['method' => 'get']],
    'trade' => ['admin/trade/index', ['method' => 'get']],
    'tradeDelById/:id' => ['admin/trade/trade_del_by_id', ['method' => 'get']],
    'tradeDelAll' => ['admin/trade/trade_del_all', ['method' => 'get']],
    'send/:id' => ['admin/trade/send', ['method' => 'get']],
    'billsend/:id' => ['admin/trade/billSend', ['method' => 'get']],
    'memberList' => ['admin/index/member_list', ['method' => 'get']],
    'memberAdd' => ['admin/index/member_add', ['method' => 'get']],
    'adminList' => ['admin/index/admin_list', ['method' => 'get']],
    'adminAdd' => ['admin/index/admin_add', ['method' => 'get']],
    'saveAdmin' => ['admin/index/save_admin', ['method' => 'post']],
    'adminEdit/:id' => ['admin/index/admin_edit', ['method' => 'get']],
    'discount' => ['admin/index/discount', ['method' => 'get']],
    'addDiscount' => ['admin/index/add_discount', ['method' => 'get']],
    'discountDel' => ['admin/index/del_discount', ['method' => 'get']],
    'discountDelAll' => ['admin/index/del_discount_all', ['method' => 'get']],
    '/' => 'admin/index/index', // 首页访问路由
]);

Route::post([
    'tradeCreate' => ['item/tradeCreate', ['method' => 'post']],

]);

Route::group('',[
    '/$' => 'index', // 首页访问路由
    'my' => 'ajax', // 静态地址路由
    'getInfo' => ['getInfo', ['method' => 'get']],
    'itemList' => ['item/itemList', ['method' => 'get']],
    'item/:id' => ['item/item', ['method' => 'get']],
    'wxlogin/:id' => ['wxLogin', ['method' => 'get']],
    'buy' => ['buy', ['method' => 'get']],
    'addAddr' => ['item/addAddr', ['method' => 'get']],
    'addAddrInfo' => ['item/add_addr_info', ['method' => 'get']],
    'saveAddr' => ['item/saveAddr', ['method' => 'post']],
    'changeAddr' => ['item/change_addr', ['method' => 'post']],
    'addrEdit/:id' => ['item/addr_edit', ['method' => 'get']],
    'userInfo' => ['user_info', ['method' => 'get']],
    'myTrade/[:type]' => ['user_trade', ['method' => 'get']],
    'ajaxGetTrade' => ['ajax_get_trade', ['method' => 'post']],
// ':user/:blog_id'=>'Blog/read',// 全动态地址
]);
//return [
//    '__pattern__' => [
//        'name' => '\w+',
//    ],
//    '[hello]'     => [
//        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
//        ':name' => ['index/hello', ['method' => 'post']],
//    ],
//
//];
