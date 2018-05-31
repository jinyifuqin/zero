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
    'pointItems' => ['admin/item/point_items', ['method' => 'get']],
    'itemAdd' => ['admin/item/itemAdd', ['method' => 'get']],
    'pointItemAdd' => ['admin/item/point_item_add', ['method' => 'get']],
    'itemDel/:id' => ['admin/item/itemDelById', ['method' => 'get']],
    'pointItemDel/:id' => ['admin/item/point_item_del', ['method' => 'get']],
    'itemDelAll' => ['admin/item/itemDelAll', ['method' => 'get']],
    'pointItemDelAll' => ['admin/item/point_item_delAll', ['method' => 'get']],
    'itemStatus' => ['admin/item/itemStatus', ['method' => 'get']],//商品上下架
    'pointItemStatus' => ['admin/item/point_item_status', ['method' => 'get']],//商品上下架
    'itemEdit/:id' => ['admin/item/itemEdit', ['method' => 'get']],
    'pointItemEdit/:id' => ['admin/item/point_item_edit', ['method' => 'get']],
    'itemUpdate' => ['admin/item/itemUpdate', ['method' => 'post']],
    'pointItemUpdate' => ['admin/item/point_item_update', ['method' => 'post']],
    'itemSave' => ['admin/item/itemSave', ['method' => 'post']],
    'pointItemSave' => ['admin/item/point_item_save', ['method' => 'post']],
    'cats' => ['admin/item/catList', ['method' => 'get']],
    'addCat' => ['admin/item/addCat', ['method' => 'get']],
    'saveCat' => ['admin/item/saveCat', ['method' => 'post']],
    'catEdit/:id' => ['admin/item/catEdit', ['method' => 'get']],
    'catDelAll' => ['admin/item/catDelAll', ['method' => 'get']],
    'catDelById' => ['admin/item/catDelById', ['method' => 'get']],
    'trade' => ['admin/trade/index', ['method' => 'get']],
    'pointTrade' => ['admin/trade/point_trade', ['method' => 'get']],
    'tradeDelById/:id' => ['admin/trade/trade_del_by_id', ['method' => 'get']],
    'tradeDelAll' => ['admin/trade/trade_del_all', ['method' => 'get']],
    'send/:id' => ['admin/trade/send', ['method' => 'get']],
    'sendMore' => ['admin/trade/send_more', ['method' => 'post']],
    'billSendMore' => ['admin/trade/bill_send_more', ['method' => 'post']],
    'billsend/:id' => ['admin/trade/billSend', ['method' => 'get']],
    'memberList' => ['admin/index/member_list', ['method' => 'get']],
    'unMemberList' => ['admin/index/un_member_list', ['method' => 'get']],
    'memberAdd' => ['admin/index/member_add', ['method' => 'get']],
    'adminList' => ['admin/index/admin_list', ['method' => 'get']],
    'adminAdd' => ['admin/index/admin_add', ['method' => 'get']],
    'adminDelAll' => ['admin/index/admin_del_all', ['method' => 'get']],
    'adminDelById' => ['admin/index/admin_del', ['method' => 'get']],
    'saveAdmin' => ['admin/index/save_admin', ['method' => 'post']],
    'adminEdit/:id' => ['admin/index/admin_edit', ['method' => 'get']],
    'discount' => ['admin/index/discount', ['method' => 'get']],
    'pointSet' => ['admin/index/point_set', ['method' => 'get']],
    'savePointSet' => ['admin/index/save_point_set', ['method' => 'post']],
    'addDiscount' => ['admin/index/add_discount', ['method' => 'get']],
    'saveDiscount' => ['admin/index/save_discount', ['method' => 'post']],
    'discountDel' => ['admin/index/del_discount', ['method' => 'get']],
    'discountDelAll' => ['admin/index/del_discount_all', ['method' => 'get']],
    'qrcode' => ['admin/index/qrcode', ['method' => 'get']],
    'chooseService' => ['admin/trade/choose_service', ['method' => 'get']],
    'serviceTrade/:id' => ['admin/trade/service_trade', ['method' => 'get']],
    'articleMenu' => ['admin/article/article_menu', ['method' => 'get']],
    'addArticleMenus' => ['admin/article/add_article_menus', ['method' => 'get']],
    'saveArticleMenus' => ['admin/article/save_article_menus', ['method' => 'post']],
    'articleMenuEdit/:id' => ['admin/article/article_menu_edit', ['method' => 'get']],
    'articleMenuDel/:id' => ['admin/article/article_menu_del', ['method' => 'get']],
    'articleMenuDelAll' => ['admin/article/article_menu_del_all', ['method' => 'get']],
    'article' => ['admin/article/article', ['method' => 'get']],
    'articleDel/:id' => ['admin/article/article_del', ['method' => 'get']],
    'articleDelAll' => ['admin/article/article_del_all', ['method' => 'get']],
    'addArticle' => ['admin/article/add_article', ['method' => 'get']],
    'articleSave' => ['admin/article/article_save', ['method' => 'post']],
    'articleStatus/:id' => ['admin/article/article_status', ['method' => 'get']],
    'articleEdit/:id' => ['admin/article/article_edit', ['method' => 'get']],
    'level' => ['admin/index/level', ['method' => 'get']],
    'lowHeightSave' => ['admin/index/low_height_save', ['method' => 'post']],
    'selfInfo' => ['admin/index/server_self_info', ['method' => 'get']],
    'saveQr' => ['admin/index/save_qr', ['method' => 'post']],
    'serverPhoneSave' => ['admin/index/server_phone_save', ['method' => 'post']],
    'entrustMember' => ['admin/index/entrust_member', ['method' => 'get']],
    'entrustAction' => ['admin/index/entrust_action', ['method' => 'post']],
    'selfPoint' => ['admin/index/self_point', ['method' => 'get']],
    'itemPrice' => ['admin/item/item_price', ['method' => 'get']],
    'putForward' => ['admin/index/put_forward', ['method' => 'get']],
    'confirmPf' => ['admin/index/confirm_pf', ['method' => 'post']],
    'partMember' => ['admin/index/part_member', ['method' => 'get']],
    'truePart/:id' => ['admin/index/true_part', ['method' => 'get']],
    'indexPic' => ['admin/index/index_pic', ['method' => 'get']],
    'indexPicAdd' => ['admin/index/index_pic_add', ['method' => 'get']],
    'indexPicSave' => ['admin/index/index_pic_save', ['method' => 'post']],
    'indexPicEdit/:id' => ['admin/index/index_pic_edit', ['method' => 'get']],
    'indexPicUp' => ['admin/index/index_pic_up', ['method' => 'post']],
    'indexPicDel' => ['admin/index/index_pic_del', ['method' => 'get']],
    'indexPicDelAll' => ['admin/index/index_pic_del_all', ['method' => 'get']],
    'voting' => ['admin/index/voting', ['method' => 'get']],
    'addVoting' => ['admin/index/add_voting', ['method' => 'get']],
    'saveVoting' => ['admin/index/save_voting', ['method' => 'post']],
    'votingEdit/:id' => ['admin/index/voting_edit', ['method' => 'get']],
    'votingDel/:id' => ['admin/index/voting_del', ['method' => 'get']],
    'votingDelAll' => ['admin/index/voting_delAll', ['method' => 'get']],
    'votingDetail/:id' => ['admin/index/voting_detail', ['method' => 'get']],
    '/' => 'admin/index/index', // 首页访问路由
]);

Route::post([
    'tradeCreate' => ['item/tradeCreate', ['method' => 'post']],
    'pointTradeCreate' => ['item/point_trade_create', ['method' => 'post']],
]);

Route::group('',[
    '/$' => 'index', // 首页访问路由
    'my' => 'ajax', // 静态地址路由
    'getInfo' => ['getInfo', ['method' => 'get']],
    'itemList' => ['item/itemList', ['method' => 'get']],
    'item/:id' => ['item/item', ['method' => 'get']],
    'pointItem/:id' => ['item/point_item', ['method' => 'get']],
    'item/checkDiscount' => ['item/check_discount', ['method' => 'get']],
    'wxlogin/:id' => ['wxLogin', ['method' => 'get']],
    'pointwxlogin/:id' => ['point_wx_login', ['method' => 'get']],
    'buy' => ['buy', ['method' => 'get']],
    'pointBuy' => ['point_buy', ['method' => 'get']],
    'addAddr' => ['item/addAddr', ['method' => 'get']],
    'addAddrInfo' => ['item/add_addr_info', ['method' => 'get']],
    'saveAddr' => ['item/saveAddr', ['method' => 'post']],
    'changeAddr' => ['item/change_addr', ['method' => 'post']],
    'addrEdit/:id' => ['item/addr_edit', ['method' => 'get']],
    'userInfo' => ['user_info', ['method' => 'get']],
    'myTrade/[:type]' => ['user_trade', ['method' => 'get']],
    'tradeDetail/:id/:type' => ['trade_detail', ['method' => 'get']],
    'ajaxGetTrade' => ['ajax_get_trade', ['method' => 'post']],
    'trueGet' => ['trade_true_get', ['method' => 'get']],
    'tradeDel' => ['trade_del', ['method' => 'get']],
    'showPointList' => ['show_point_list', ['method' => 'get']],
    'showNoUsePointList' => ['show_no_use_point_list', ['method' => 'get']],
    'selfInfo' => ['User/self_info', ['method' => 'get']],
    'saveHead' => ['User/save_head', ['method' => 'post']],
    'saveAddrJs' => ['User/save_addr', ['method' => 'post']],
    'detaiAddr' => ['User/self_detail_addr', ['method' => 'get']],
    'saveDetaiAddr' => ['User/save_detail_addr', ['method' => 'post']],
    'phoneNum' => ['User/phone_num', ['method' => 'get']],
    'savePhoneNum' => ['User/save_phone_num', ['method' => 'post']],
    'choose_service_cent' => ['User/choose_service_cent', ['method' => 'get']],
    'saveServiceCent' => ['User/save_service_cent', ['method' => 'post']],
    'accountNumber' => ['User/account_number', ['method' => 'get']],
    'saveCollections' => ['User/save_account_number', ['method' => 'post']],
    'myQrcode' => ['User/my_qrcode', ['method' => 'get']],
    'loginCheck' => ['User/login_check', ['method' => 'post']],
    'weiXinLogin' => ['User/weixin_login', ['method' => 'get']],
    'register' => ['User/register', ['method' => 'get']],
    'captcha' => ['User/register_captcha', ['method' => 'get']],
    'saveUser' => ['User/save_user', ['method' => 'post']],
    'exitLogin' => ['User/exit_login', ['method' => 'get']],
    'givePoint' => ['User/give_point', ['method' => 'get']],
    'searchMember' => ['User/search_member', ['method' => 'get']],
    'givePointMember' => ['User/give_point_member', ['method' => 'get']],
    'serviceCent' => ['User/service_cent', ['method' => 'get']],
    'entrust' => ['User/entrust', ['method' => 'get']],
    'givePointService' => ['User/give_point_service', ['method' => 'get']],
    'userGet' => ['User/user_get', ['method' => 'get']],
    'saveNickname' => ['User/save_nickname', ['method' => 'post']],
    'openbank' => ['User/openbank', ['method' => 'get']],
    'saveOpenBank' => ['User/save_open_bank', ['method' => 'post']],
    'putForward' => ['User/put_forward', ['method' => 'get']],
    'pfAction' => ['User/pf_action', ['method' => 'get']],
    'entrustShow' => ['User/entrust_show', ['method' => 'get']],
    'surePart' => ['User/sure_part', ['method' => 'get']],
    'nickname' => ['User/nickname', ['method' => 'get']],
    'community/[:id]' => ['community', ['method' => 'get']],
    'articleDetail/:id' => ['article_detail', ['method' => 'get']],
    'artTalk' => ['artTalk', ['method' => 'post']],
    'artGiveGood/:id' => ['art_give_good', ['method' => 'get']],
    'plGiveGood/:id' => ['pl_give_good', ['method' => 'get']],
    'userVoting' => ['User/user_voting', ['method' => 'get']],
    'votingDetail/:id' => ['User/voting_detail', ['method' => 'get']],
    'votingAction' => ['User/voting_action', ['method' => 'post']],

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
