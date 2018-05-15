<?php
namespace app\Admin\controller;
use app\admin\model\Adminusers;
use app\admin\model\Brands;
use app\admin\model\Discounts;
use app\index\model\Addrs;
use app\index\model\Users;
use \think\Controller;
use think\Db;
use think\Request;

class Index extends Controller
{
    public $logo;

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
//        unset($_SESSION['adminUserInfo']);
        parent::__construct($request);
    }

    public function index()
    {
        if(!array_key_exists('adminUserInfo',$_SESSION)){
            $this->redirect('/admin/login');
        }

//        echo "<pre>";var_dump($_SESSION);exit;
        $admin = $_SESSION['adminUserInfo'];
        return view("admin@index/index",['admin'=>$admin]);
    }

    public function welcome()
    {
        return view("admin@index/welcome");
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
        $disList = Discounts::all();
        return view("admin@index/discount",['disList'=>$disList]);
    }

    public function add_discount(){
        $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $len = 30;
        $content = "";
        for($i=0;$i<30;$i++){
            $num = mt_rand(0,strlen($str)-1);
            $content .= substr($str,$num,1);
        }
        $dis = new Discounts([
            'number'  =>  $content,
        ]);
        $re = $dis->save();
        if($re){
            return view("admin@index/alertDiscount",['number'=>$content]);
        }
//        echo "<pre>";var_dump($content);exit;

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


}
