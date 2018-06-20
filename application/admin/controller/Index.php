<?php
namespace app\Admin\controller;
use app\admin\model\Adminusers;
use app\admin\model\Brands;
use app\admin\model\Discounts;
use app\admin\model\IndexPics;
use app\admin\model\VotingList;
use app\admin\model\Votings;
use app\index\model\Entrusts;
use app\index\model\Addrs;
use app\index\model\Points;
use app\index\model\PutForwards;
use app\index\model\Trades;
use app\index\model\Users;
use think\Config;
use \think\Controller;
use think\Db;
use think\db\Query;
use think\Request;

class Index extends Controller
{
    public $logo;
    private $configPath;

    protected $beforeActionList = [
        'check_login' =>  ['except'=>'get_captcha,login,check_user'],
    ];

    public function check_login(){
        $check = array_key_exists('adminUserInfo',$_SESSION);
        if(!$check)
            $this->redirect('/admin/login');
    }


    public function __construct(Request $request = null)
    {
        session_start();
        $configName = 'config.xml';
        $this->configPath = APP_PATH.'admin'.DS.'config'.DS.$configName;
        Config::load($this->configPath);
        parent::__construct($request);
    }

    public function index()
    {
        if(!array_key_exists('adminUserInfo',$_SESSION)){
            $this->redirect('/admin/login');
        }

        $admin = $_SESSION['adminUserInfo'];
        return view("admin@index/index",['admin'=>$admin]);
    }

    public function welcome()
    {
        if(array('adminUserInfo',$_SESSION)){
            $adminObj = $_SESSION['adminUserInfo'];
        }
        $adminCount = Adminusers::where('type',0)->count();
        $tradeCount = Trades::count();
        $userCount = Users::count();
        $re = ['adminInfo'=>$adminObj,'tradeCount'=>$tradeCount,'adminCount'=>$adminCount,'userCount'=>$userCount];
//        echo "<pre>";var_dump($_SESSION);exit;
        return view("admin@index/welcome",['re'=>$re]);
    }

    public function login()
    {
//        echo "<pre>";var_dump(isset($_SESSION['adminUserInfo']));exit;
        if(isset($_SESSION['adminUserInfo'])){
            $url = url('admin/index/index');
            return redirect($url);
        }

        return view("login");
    }

    public function check_user(Request $request){
        $captcha = $_SESSION['captcha'];
        $username = $request->param('username');
        $password = $request->param('password');
//        echo "<pre>";var_dump($request->param());exit;
        if(strtolower($request->param('captcha')) != $captcha){
            $msg = array(
                "error"=>"验证码输入有误!",
            );
            return $msg;
        }
        $userObj = new Adminusers();
        $re = $userObj->where('username', $username)->where('password',$password)
            ->find();
        if($re){
            $_SESSION['adminUserInfo'] = $re;
            $ip = getIp();
            $time = date('Y-m-d H:i:s');
            Adminusers::update(['id' => $re->id, 'last_login_ip' => $ip , 'last_login_time'=>$time]);
            $url = url('admin/index/index');
            $msg = array(
                "url"=>$url,
            );
            return($msg);
        }else{
            $url = url('admin/index/login');
            $msg = array(
                "url"=>$url,
            );
            return $msg;
        }
    }

    public function get_captcha(){
        getCaptcha();
    }

    public function createUser(){
        $user = new Adminusers([
            'username'  =>  'admin',
            'password' =>  'admin'
        ]);
        $user->save();
    }

    public function signOut(){
        unset($_SESSION['adminUserInfo']);
        $url = url('admin/index/login');
        return redirect($url);
    }

    public function brand(){
        $re = Brands::all();
        foreach ($re as $k=>&$v){
            $v->logo = preg_replace('/\\\\/','/',$v->logo);
        }
        return view("admin@index/brand",['re'=>$re]);
    }

    public function brandShow(Request $request){
        $id = $request->param('id');
        $re = Brands::get(['id' => $id]);
        $re->logo = preg_replace('/\\\\/','/',$re->logo);
        return view("admin@index/brandShow",['re'=>$re]);
    }

    public function addbrand(Request $request){
        $file = $request->file('file');
        $name = $request->param('name');
        $sort = $request->param('sort');
        $creatTime = date('Y-m-d H:i:s',time());
        $re = upload($file);
        $end = htmlspecialchars($re->getSaveName());
        if($re->getError() == ''){
            $logo = $end;
            $brandObj = new Brands([
                'name'  =>  $name,
                'sort' =>  $sort,
                'logo' => $logo,
                'create_time' =>$creatTime
            ]);
            $result = $brandObj->save();
            if($result)
                $msg = array('status'=>'Success');
                echo json_encode($msg);
        }

    }

    public function brandUpdate(Request $request){
//        echo "<pre>";var_dump($request->file('file-2'));exit;
        if($request->file('file-2')){
            $file = $request->file('file-2');
            $fileRe = upload($file);
            $logo = htmlspecialchars($fileRe->getSaveName());
            $re = array('re'=>$logo);
            echo json_encode($re);
            return;
        }

            $logo = $request->param('logopath');
            $check = strrpos($logo,'uploads');

            $brand = new Brands();
            $creatTime = date('Y-m-d H:i:s',time());
            $id = $request->param('id');
            $name = $request->param('name');
            $desc = $request->param('desc');
            $sort = $request->param('sort');
            $re = Brands::get(['id' => $id]);
            if($check){
                $logo = $re->logo;
            }
            $result = $brand->save(
                ['logo'=>$logo,'name'=>$name,'desc'=>$desc,'sort'=>$sort,'create_time'=>$creatTime],
                ['id'=>$id]
            );

        $re = array('type'=>$result);
        echo json_encode($re);
    }

    public function brandDelById(Request $request){
        $id = $request->param('id');
        $branInfo = Brands::get($id);
        $re = $branInfo->delete();
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function brandDelAll(Request $request){
        $ids = $request->param()['ids'];
        $re = Brands::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function member_list(){
        $id = $_SESSION['adminUserInfo']->id;
        $memberList = Db::query("SELECT id FROM `yzt_users` WHERE service_cent_id = $id 
                and id in (SELECT user_id FROM `yzt_trades` GROUP BY user_id);");
        if($memberList != null){
            $memberList = implode(',',i_array_column($memberList,'id'));
            $memberList = Users::where('id','IN',$memberList)->select();
            foreach ($memberList as $k=>&$v){
                $v->nickname = json_decode(urldecode($v->nickname));
                if(isset($v->address)){
                    $addr = Addrs::where('id',$v->address)->value('desc');
                    $addr = preg_replace('/%2C/',' ',$addr);
                    $v->address = $addr;
                }
            }
        }


//                echo "<pre>";var_dump($addr);exit;
        return view("admin@index/memberList",['re'=>$memberList]);
    }

    public function un_member_list(){
        $id = $_SESSION['adminUserInfo']->id;
        $memberList = Db::query("SELECT id FROM `yzt_users` WHERE service_cent_id = $id 
                and id in (SELECT user_id FROM `yzt_trades` GROUP BY user_id);");
//        echo "<pre>";var_dump($memberList);exit;
        if($memberList != null){
            $memberList = implode(',',i_array_column($memberList,'id'));
            $memberList = Users::where('id','NOT IN',$memberList)->select();
            foreach ($memberList as $k=>&$v){
                $v->nickname = json_decode(urldecode($v->nickname));
                if(isset($v->address)){
                    $addr = Addrs::where('id',$v->address)->value('desc');
                    $addr = preg_replace('/%2C/',' ',$addr);
                    $v->address = $addr;
                }
            }
        }

        return view("admin@index/memberList",['re'=>$memberList]);
    }

    public function member_add(){
        return view("admin@index/memberAdd");
    }

    public function admin_list(){
        $re = Adminusers::all();
        return view("admin@index/adminList",['re'=>$re]);
    }

    public function admin_add(){
        return view("admin@index/adminAdd");
    }

    public function admin_edit(Request $request){
        $id = $request->param('id');
        $re = Adminusers::get(['id' => $id]);
        return view("admin@index/adminEdit",['re'=>$re]);
    }

    public function admin_del(Request $request){
        $id = $request->param("id");
        $admin = Adminusers::get($id);
        $re = $admin->delete();
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function admin_del_all(Request $request){
        $ids = $request->param()['ids'];
//        echo "<pre>";var_dump($ids);exit;
        $re = Adminusers::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function save_admin(Request $request){
        $post = $request->param();
        $ip = getIp();
        $data = [
            'nickname'=>$post['nickname'],
            'username'=>$post['adminName'],
            'password'=>$post['password'],
            'sex'=>$post['sex'],
            'type'=>$post['type'],
            'phone_number'=>$post['phone'],
            'email'=>$post['email'],
            'last_login_ip'=>$ip,
            'last_login_time'=>date('Y-m-d H:i:s')
        ];
        if(array_key_exists('id',$post)){
            $result = Adminusers::where('id', $post['id'])
                ->update($data);
        }else{
            $adminObj = new Adminusers($data);
            $result = $adminObj->save();
        }


        if($result){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
//        echo "<pre>";var_dump($post);
    }

    public function discount(){
        $adminId = $_SESSION['adminUserInfo']->id;
        $adminUsername = $_SESSION['adminUserInfo']->username;
        if($adminUsername == "admin"){
            $disList = Discounts::all();
            foreach($disList as &$v){
                $nameobj = $v->adminusers->username;
                $v->service_cent_name = $nameobj;
            }
            $flag = true;
        }else{
            $disList = Discounts::all(['service_cent_id'=>$adminId]);
            $flag = false;
        }

        return view("admin@index/discount",['disList'=>$disList,'flag'=>$flag]);
    }

    public function add_discount(){
        $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $len = 12;
        $content = "";
        for($i=0;$i<$len;$i++){
            $num = mt_rand(0,strlen($str)-1);
            $content .= substr($str,$num,1);
        }
        $serviceList = Adminusers::all(['type'=>0]);
        $re = ['service'=>$serviceList,'number'=>$content];
//        echo "<pre>";var_dump($re);exit;
        return view("admin@index/alertDiscount",['re'=>$re]);
    }

    public function save_discount(Request $request){
        $adminId = $request->param('service');
        $content = $request->param('number');
        $zk = $request->param('zk');
        $canUseCount = $request->param('canUseCount');
//        echo "<prE>";var_dump($adminId);var_dump($content);var_dump($zk);exit;
        $dis = new Discounts([
            'number'  =>  $content,
            'service_cent_id' => $adminId,
            'zk' => $zk,
            'can_use_count' => $canUseCount
        ]);
        $re = $dis->save();
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function del_discount(Request $request){
        $id = $request->param('id');
        $re = Discounts::destroy($id);
        return $re;
    }

    public function del_discount_all(Request $request){
        $ids = $request->param()['ids'];
        $re = Discounts::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function qrcode(){
        $str = $_SESSION['adminUserInfo']->nickname;
        getQrcodePic($str);exit;
    }

    public function point_set(){
        $config = config('setPointCount');
        $dbconfig = config('setDoublePointCount');
        $sharePointFilterConfig = config('sharePointFilterConfig');
        $setGivePointBrokerage = config('setGivePointBrokerage');
        $all = [
            'config'=>$config,
            'dbconfig'=>$dbconfig,
            'sharePointFilterConfig'=>$sharePointFilterConfig,
            'setGivePointBrokerage'=>$setGivePointBrokerage
        ];
        return view("admin@index/pointSet",['all'=>$all]);
    }
    
    public function save_point_set(Request $request){
        $config = $request->param('pointCount');
        $sharePointFilterConfig = $request->param('sharePointFilterConfig');
        $setGivePointBrokerage = $request->param('setGivePointBrokerage');
        $path = $this->configPath;
        $content = file_get_contents($path);
        $replace = "/(?<=\<setPointCount\>)(\d+)(?=\<\/setPointCount\>)/";
        $shfreplace = "/(?<=\<sharePointFilterConfig\>)(\d+)(?=\<\/sharePointFilterConfig\>)/";
        $givereplace = "/(?<=\<setGivePointBrokerage\>)(\d+)(?=\<\/setGivePointBrokerage\>)/";
        $str = preg_replace($replace,$config,$content);
        $str = preg_replace($shfreplace,$sharePointFilterConfig,$str);
        $str = preg_replace($givereplace,$setGivePointBrokerage,$str);
//        echo "<pre>";var_dump($str);exit;
        $re = file_put_contents($this->configPath,$str);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function level(){
        $list['ALow'] = config('setALow');
        $list['BLow'] = config('setBLow');
        $list['BHeight'] = config('setBHeight');
        $list['CLow'] = config('setCLow');
        $list['CHeight'] = config('setCHeight');
        $list['DLow'] = config('setDLow');
        $list['DHeight'] = config('setDHeight');
//        echo "<pre>";var_dump($list);exit;
        return view("admin@index/level",['list'=>$list]);
    }

    public function low_height_save(Request $request){
        $path = $this->configPath;
        $content = file_get_contents($path);
        $post = $request->param();
        $obj = $post['obj'];
        if(array_key_exists('height',$post)){
            $height = $post['height'];
        }
        $low = $post['low'];

        switch ($obj){
            case 'a':
                $replace = "/(?<=\<setALow\>)(\d+)(?=\<\/setALow\>)/";
                $str = preg_replace($replace,$low,$content);
                break;
            case 'b':
                $lowReplace = "/(?<=\<setBLow\>)(\d+)(?=\<\/setBLow\>)/";
                $heightReplace = "/(?<=\<setBHeight\>)(\d+)(?=\<\/setBHeight\>)/";
                $str = preg_replace($lowReplace,$low,$content);
                $str = preg_replace($heightReplace,$height,$str);
                break;
            case 'c':
                $lowReplace = "/(?<=\<setCLow\>)(\d+)(?=\<\/setCLow\>)/";
                $heightReplace = "/(?<=\<setCHeight\>)(\d+)(?=\<\/setCHeight\>)/";
                $str = preg_replace($lowReplace,$low,$content);
                $str = preg_replace($heightReplace,$height,$str);
                break;
            case 'd':
                $lowReplace = "/(?<=\<setDLow\>)(\d+)(?=\<\/setDLow\>)/";
                $heightReplace = "/(?<=\<setDHeight\>)(\d+)(?=\<\/setDHeight\>)/";
                $str = preg_replace($lowReplace,$low,$content);
                $str = preg_replace($heightReplace,$height,$str);
                break;
        }
        $re = file_put_contents($this->configPath,$str);
        if($re){
            $msg = array('status'=>'success');
        }else{
            $msg = array('status'=>'error');
        }
        echo json_encode($msg);
    }

    public function server_self_info(){
        $info = $_SESSION['adminUserInfo'];
        return view("admin@index/serverSelfInfo",['re'=>$info]);
//        echo "<pre>";var_dump($info);
    }

    public function save_qr(Request $request){
        $info = $_SESSION['adminUserInfo'];
        $qrPic = $request->file('file');
        $serverId = $info->id;
//        echo "<pre>";var_dump($_SESSION);exit;
        $re = upload($qrPic);
        $end = htmlspecialchars($re->getSaveName());
        if($re->getError() == ''){
            $logo = $end;
            $serObj = new Adminusers();
            $result = $serObj->save([
                'qrcode'  =>  $logo,
            ],['id'=>$serverId]);
            if($result){
                $_SESSION['adminUserInfo']->qrcode = $logo;
                $msg = array('status'=>'success','path'=>$logo);
            }
            echo json_encode($msg);
        }
    }

    public function server_phone_save(Request $request){
        $info = $_SESSION['adminUserInfo'];
        $serverId = $info->id;
        $serObj = new Adminusers();
        $phone = $request->param('phone');
        $result = $serObj->save([
            'phone_number'  =>  $phone,
        ],['id'=>$serverId]);
        if($result){
            $_SESSION['adminUserInfo']->phone_number = $phone;
            $msg = array('status'=>'success');
        }
        echo json_encode($msg);
    }

    public function entrust_member(){
        $info = $_SESSION['adminUserInfo'];
        $serverId = $info->id;
        $enList = Entrusts::all(['service_id'=>$serverId]);
//        $idArr = array_column($memList,'id');
        foreach ($enList as $k=>&$v){

            $v->trueName = $v->users->truename;
            $v->phone_number = $v->users->phone_number;
            if($v->type == '已接受' || $v->type == '已拒绝'){
                $v->entrustType = true;
            }else{
                $v->entrustType = false;
            }

        }
//        echo "<pre>";var_dump($enList);exit;
        return view("admin@index/entrustMember",['list'=>$enList]);
    }

    public function entrust_action(Request $request){
        $post = $request->param();
        $info = $_SESSION['adminUserInfo'];
        $memId = trim($post['memId']);
        $enId = $request->param('id');
        $type = $request->param('type');
        $pId = $request->param('pointId');
        if($type == 'add'){
            $enObj = Entrusts::get($enId);
            $enObj->type = 1;
            $re = $enObj->save();
            if($re){
                $msg = array('status'=>'success');
                echo json_encode($msg);
            }else{
                $msg = array('status'=>'error');
                echo json_encode($msg);
            }
        }else{
            $p = Points::get($pId);
            $re = $p->delete();
            $enObj = Entrusts::get($enId);
            $enObj->type = 2;
            $re = $enObj->save();
            if($re){
                $msg = array('status'=>'success');
                echo json_encode($msg);
            }else{
                $msg = array('status'=>'error');
                echo json_encode($msg);
            }
        }
    }

    public function self_point(){
        $list['ALow'] = config('setALow');
        $list['BLow'] = config('setBLow');
        $list['BHeight'] = config('setBHeight');
        $list['CLow'] = config('setCLow');
        $list['CHeight'] = config('setCHeight');
        $list['DLow'] = config('setDLow');
        $list['DHeight'] = config('setDHeight');
        $info = $_SESSION['adminUserInfo'];
        $serverId = $info->id;
        $count = \think\Db::table('yzt_entrusts')
            ->where('service_id',$serverId)
            ->where('type',1)
            ->sum('count');
        if($list['DLow'] <= $count && $count <= $list['DHeight']){
            $belong = "D级";
        }elseif($list['CLow'] <= $count && $count <= $list['CHeight']){
            $belong = "C级";
        }elseif($list['BLow'] <= $count && $count <= $list['BHeight']){
            $belong = "B级";
        }elseif($list['ALow'] <= $count){
            $belong = "A级";
        }else{
            $belong = "无等级（通过普通会员托管获取积分，提升等级）";
        }
        $re = ['count'=>$count,'belong'=>$belong];
        return view("admin@index/selfPoint",['re'=>$re]);
//        echo "<pre>";var_dump($list);exit;
    }
    
    public function put_forward(){
        $pfObj = PutForwards::all();

        foreach ($pfObj as $k=>&$v){
            $trueName = $v->users->truename;
            $phone = $v->users->phone_number;
            $collections = $v->users->collections;
            $open_bank = $v->users->open_bank;
            $v['trueName'] = $trueName;
            $v['phone_number'] = $phone;
            $v['collections'] = $collections;
            $v['open_bank'] = $open_bank;
        }
//        echo "<pre>";var_dump($pObj);exit;
        return view("admin@index/putForward",['re'=>$pfObj]);
    }

    public function confirm_pf(Request $request){
        $id = $request->param('id');
        $pfObj = PutForwards::get($id);
        $pfObj->type = 1;
        $re = $pfObj->save();
        if($re){
            $msg = array('status'=>'success');
            echo json_encode($msg);
        }else{
            $msg = array('status'=>'error');
            echo json_encode($msg);
        }
    }

    public function part_member(){
        $admin = $_SESSION['adminUserInfo'];
        $adminId = $admin->id;
        $e = new Entrusts();
        $usersId = $e->where('service_id', $adminId)
            ->where('type','=' ,1)
            ->group('user_id')
            ->column('user_id');
        $usersId = implode(',',$usersId);
        $uObj = new Users();
        $users = $uObj->where('id','in',$usersId )
            ->select();
        foreach($users as &$v){
            $v->nickname = json_decode(urldecode($v->nickname));
        }
//        echo "<pre>";var_dump($users);exit;
        return view("admin@index/partMember",['re'=>$users]);
    }

    public function true_part(Request $request){
        $userId = $request->param('id');
        $e = new Entrusts();
        $first = $e->where('user_id', $userId)
            ->limit(1)
            ->order('create_time', 'asc')
            ->column('create_time');
        $first = $first[0];
        $checkTime = strtotime("+3 months", strtotime($first));
        $count = $e->where('user_id',$userId)
            ->where('type','in','0,1')
            ->sum('count');
//        echo "<pre>";var_dump($count);exit;
        if($checkTime < time()){
            $partPoint['user_id'] = $userId;
            $partPoint['count'] = $count;
            $partPoint['type'] = 1;
            $partPoint['get_type'] = 4;
            $partPoint['frozen_flag'] = 0;
            $partPoint['create_time'] = date('Y-m-d H:i:s',time());
            $pObj = new Points();
            $pObj->data($partPoint);
            $re = $pObj->save();
            $re2 = Entrusts::destroy(['user_id' => $userId]);
            if($re && $re2){
                $msg = ['type'=>'success','msg'=>'解除托管成功！'];
                echo json_encode($msg);
            }else{
                $msg = ['type'=>'error','msg'=>'解除托管失败！'];
                echo json_encode($msg);
            }
        }else{
            $msg = ['type'=>'error','msg'=>'观察期，三个月之后才可解除！'];
            echo json_encode($msg);
        }
//        echo "<pre>";var_dump($id);exit;
    }

    public function index_pic(){
        $all = IndexPics::all();
        $count = IndexPics::count();
        $data = ['all'=>$all,'count'=>$count];
        return view("admin@index/indexPic",['re'=>$data]);
    }

    public function index_pic_save(Request $request){
        $count = IndexPics::count();
        if($count >= 3){
            $msg = array('status'=>'error','msg'=>'只能上传三张！');
            echo json_encode($msg);
            exit;
        }
//        echo "<pre>";var_dump($count);exit;
        $file = $request->file('file');
        $sort = $request->param('sort');
        $creatTime = date('Y-m-d H:i:s',time());
        $re = upload($file);
        $end = htmlspecialchars($re->getSaveName());
        if($re->getError() == ''){
            $logo = $end;
            $brandObj = new IndexPics([
                'sort' =>  $sort,
                'pic' => $logo,
                'create_time' =>$creatTime
            ]);
            $result = $brandObj->save();
            if($result)
                $msg = array('status'=>'Success');
            echo json_encode($msg);
        }
    }

    public function index_pic_edit(Request $request){
        $id = $request->param('id');
        $re = IndexPics::get($id);
        return view("admin@index/indexPicEdit",['re'=>$re]);
    }

    public function index_pic_up(Request $request){
        if($request->file('file-2')){
            $file = $request->file('file-2');
            $fileRe = upload($file);
            $logo = htmlspecialchars($fileRe->getSaveName());
            $re = array('re'=>$logo);
            echo json_encode($re);
            return;
        }

        $logo = $request->param('logopath');
        $check = strrpos($logo,'uploads');

        $picObj = new IndexPics();
        $upTime = date('Y-m-d H:i:s',time());
        $id = $request->param('id');
        $sort = $request->param('sort');
        $re = IndexPics::get(['id' => $id]);
        if($check){
            $logo = $re->pic;
        }
        $result = $picObj->save(
            ['pic'=>$logo,'sort'=>$sort,'update_time'=>$upTime],
            ['id'=>$id]
        );

        $re = array('type'=>$result);
        echo json_encode($re);
    }

    public function index_pic_del(Request $request){
        $id = $request->param('id');
        $re = IndexPics::destroy($id);
        if($re){
            $data = ['status'=>'success'];
            echo json_encode($data);
        }
    }

    public function index_pic_del_all(Request $request){
        $ids = $request->param()['ids'];
//        echo "<pre>";var_dump($request->param());exit;
        $re = IndexPics::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function voting(){
        $re = Votings::all();
//        echo "<pre>";var_dump($re);exit;
        return view("admin@index/voting",['re'=>$re]);
    }

    public function add_voting(){
        return view("admin@index/addVoting");
    }

    public function save_voting(Request $request){
        $post = $request->param();
        $data['point_count'] = $post['point_count'];
        $data['voting_count'] = $post['voting_count'];
        $data['title'] = $post['title'];
        $data['desc'] = $post['desc'];
        $data['content'] = $post['content'];
        $v = new Votings();
//        echo "<pre>";var_dump($post);exit;
        if(array_key_exists('id',$post)){
            $data['id'] = $post['id'];
            $re = $v->save($data,['id'=>$data['id']]);
        }else{
            $v->data($data);
            $re = $v->save();
        }

        if($re){
            $msg = array('status'=>'success','msg'=>'投票添加成功！');
        }else{
            $msg = array('status'=>'fails','msg'=>'投票添加成功！');
        }
        echo json_encode($msg);
//        echo "<pre>";var_dump($post);exit;
    }

    public function voting_edit($id){
        $re = Votings::get($id);
        return view("admin@index/editVoting",['re'=>$re]);
    }

    public function voting_del($id){

        $v = Votings::get($id);
//        echo "<pre>";var_dump($v);exit;
        $re = $v->delete();
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function voting_delAll($ids){
        $re = Votings::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function voting_detail($id){
        $vObj = Votings::get($id);
        $content = $vObj->content;
        $content = trim($content);
        $content = explode('|',$content);
        $vList = new VotingList();
        $arr = [];
        foreach($content as $k=>$v){
            $count = $vList->where('voting_info','=',$v)
                ->where('voting_id','=',$id)
                ->count();
            $arr[$v] = $count;
        }
        $vObj->selectArr = $arr;
        return view("admin@index/votingDetail",['re'=>$vObj]);
//        echo "<pre>";var_dump($vObj);exit;
    }

    public function change_psw(){

        return view("admin@index/changePsw");
    }

    public function save_change_psw(Request $request){
        $psw = $request->param('password');
        $info = $_SESSION['adminUserInfo'];
        $serverId = $info->id;
        $user = Adminusers::get($serverId);
        $user->password = $psw;
        $re = $user->save();
        if($re){
            $msg = array('status'=>'success');
        }else{
            $msg = array('status'=>'error');
        }
        echo json_encode($msg);
    }

    public function user_point(){
        $user = Users::all();
//            ->order(['create_time'=>'desc']);
//        echo "<pre>";var_dump($user);exit;
        $pO = new \app\index\model\Points();
        foreach ($user as &$v){
            $noUseAdd = $pO->where('user_id',$v->id)
                ->where('type',1)
                ->where('frozen_flag',0)
                ->sum('count');

            $noUseDel = $pO->where('user_id',$v->id)
                ->where('type',0)
                ->where('frozen_flag',0)
                ->sum('count');
            $noUseEnd = $noUseAdd-$noUseDel;

            $canUseAdd = $pO->where('user_id',"=",$v->id)
                ->where('get_type',">",0)
                ->where('type',"=",1)
                ->where('frozen_flag',"=",1)
                ->sum('count');

            $canUseDel = $pO->where('user_id',"=",$v->id)
                ->where('get_type',">",0)
                ->where('type',"=",0)
                ->where('frozen_flag',"=",1)
                ->sum('count');

            $canUseEnd = $canUseAdd - $canUseDel;
            $v->noUseEnd = round($noUseEnd,'3');
            $v->canUseEnd = round($canUseEnd,3);
        }

//        echo "<pre>";var_dump($user);exit;
        return view("admin@index/userPoint",['re'=>$user]);
    }
    
    public function point_detail($id){
        $pO = new \app\index\model\Points();
        $detail = $pO->where('user_id',"=",$id)
            ->order('create_time','desc')
            ->select();

//        echo "<pre>";var_dump($detail);exit;
        return view("admin@index/pointDetail",['re'=>$detail]);
    }


}
