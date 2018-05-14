<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function password($password, $password_salt){
    return md5(md5($password) . md5($password_salt));
}

function getCaptcha(){
//    session_start();
    $image = imagecreatetruecolor(100, 30);
    $bgcolor = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $bgcolor);
    $content = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $captcha = "";
    for ($i = 0; $i < 4; $i++) {
        $fontsize = 10;
        $fontcolor = imagecolorallocate($image, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
        $fontcontent = substr($content, mt_rand(0, strlen($content)), 1);
        $captcha .= $fontcontent;
        $x = ($i * 100 / 4) + mt_rand(5, 10);
        $y = mt_rand(5, 10);
        imagestring($image, $fontsize, $x, $y, $fontcontent, $fontcolor);
        $captcha = strtolower($captcha);
        $_SESSION["captcha"] = $captcha;
    }
        for ($i = 0; $i < 200; $i++) {
            $pointcolor = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
            imagesetpixel($image, mt_rand(1, 99), mt_rand(1, 29), $pointcolor);
        }
        for ($i = 0; $i < 3; $i++) {
            $linecolor = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
            imageline($image, mt_rand(1, 99), mt_rand(1, 29), mt_rand(1, 99), mt_rand(1, 29), $linecolor);
        }

    header('content-type:image/png');
    imagepng($image);
    imagedestroy($image);
}

function upload($file){
    if($file){
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            return $info;
            // 成功上传后 获取上传信息
            // 输出 jpg
            echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            echo $info->getFilename();
        }else{
            // 上传失败获取错误信息
            return $file->getError();
        }
    }
}

function download($url, $path = '')
{
    $dir = date("Ymd").DS;

    $path = 'public'.DS.'uploads'.DS.$dir;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $file = curl_exec($ch);
    curl_close($ch);
//    echo "<pre>";var_dump($path);var_dump(file_exists($path));exit;
    if(!file_exists($path)){
        mkdir($path);
    }
    $filename = md5(microtime(true)).'.jpg';
//    echo "<pre>";var_dump($filename);var_dump($path . $filename);exit;
    $resource = fopen($path . $filename,"a");
    fwrite($resource, $file);
    fclose($resource);
    return $dir.$filename;
}

function getIp() {
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else
        if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else
            if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
                $ip = getenv("REMOTE_ADDR");
            else
                if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
                    $ip = $_SERVER['REMOTE_ADDR'];
                else
                    $ip = "unknown";
    return ($ip);
}

function getPoint($userId){ // 获取用户总积分
    $re = \app\index\model\Points::all([
        'user_id'=>$userId,
        'frozen_flag'=>0,
        'type'=>1,
    ]);
    $nowStamp = time();
    $mintime = \think\Db::query('select *  from yzt_points where user_id=? ORDER BY create_time limit 1',[$userId]);
    $minId = $mintime[0]['id'];
    $pointObj = new \app\index\model\Points();
    $nowYMD = date('Y-m-d H:i:s',strtotime(date('Y-m-d',$nowStamp)));

    $flag = \app\index\model\Points::get([
        'user_id' => $userId,
        'create_time'=>$nowYMD,
        'get_type'=>2,
        'type'=>1,
        'frozen_flag'=>1
    ]);

    $noUseAdd = \app\index\model\Points::where([
        'user_id' => $userId,
        'get_type'=>0,
        'type'=>1,
        'frozen_flag'=>0
    ])->sum('count');

    $noUseDel = \app\index\model\Points::where([
        'user_id' => $userId,
        'get_type'=>2,
        'type'=>0,
        'frozen_flag'=>0
    ])->sum('count');

    $noUseEnd = $noUseAdd-$noUseDel;

    $canUse = 0;
//    echo "<pre>";var_export($flag); echo "=========<br>";
    if(!$flag){
        \app\index\model\Points::destroy([
            'user_id' => $userId,
            'get_type'=>2,
            'type'=>1,
            'frozen_flag'=>1
        ]);
        foreach($re as $k=>$v){

            $t = $v->create_time;   //生成积分时间
            $time = strtotime($t);
            $t2 = date('Y-m-d',$time);
            $point = $v->count; //该订单积分数额
            $oneday = 60*60*24;
            if($v->id == $minId){
                $tomorrow = strtotime($t2)+60*60*24;    // 该订单的第二天凌晨

                if($nowStamp >= $tomorrow && $point > 0){
                    $shijiancha = $nowStamp-$tomorrow;
                    $x = floor($shijiancha/$oneday)+1;    //超过下订单的次日凌晨几天
                }
            }else{
                $tomorrow = strtotime($t2);    // 该订单的凌晨
                if($nowStamp >= $tomorrow && $point > 0){
                    $shijiancha = $nowStamp-$tomorrow;
                    $x = floor($shijiancha/$oneday)+1;    //超过下订单的当日凌晨几天
                }
            }

//        echo "<pre>";var_export(date('Y-m-d H:i:s',$tomorrow));
            for($i=0;$i<$x;$i++){
                $givePoint[$i]['user_id'] = $userId;
                $givePoint[$i]['count'] = $point*0.001;
                $givePoint[$i]['type'] = 1;
                $givePoint[$i]['get_type'] = 2;
                $givePoint[$i]['frozen_flag'] = 1;
                $givePoint[$i]['create_time'] = date('Y-m-d H:i:s',$tomorrow+60*60*24*$i);
                $givePoint[$i]['trade_number'] = $v->trade_number;
//            $a[$i]=$givePoint;
                $delPoint[$i]['user_id'] = $userId;
                $delPoint[$i]['count'] = $point*0.001;
                $delPoint[$i]['type'] = 0;
                $delPoint[$i]['get_type'] = 2;
                $delPoint[$i]['frozen_flag'] = 0;
                $delPoint[$i]['create_time'] = date("Y-m-d H:i:s",$tomorrow+60*60*24*$i);
                $delPoint[$i]['trade_number'] = $v->trade_number;
                $canUse += $point*0.001;
            }
//        $pointObj->data($givePoint);
//        $pointObj->saveAll
            $list = array_merge($givePoint,$delPoint);
            $pointObj->saveAll($list);

            unset($givePoint);
            unset($delPoint);
//        echo "<pre>";var_export(date('Y-m-d H:i:s',$tomorrow));
        }
    }else{
        $canUse = \app\index\model\Points::where([
            'user_id' => $userId,
            'get_type'=>2,
            'type'=>1,
            'frozen_flag'=>1
        ])->sum('count');
    }


//    exit;

    $arr = ['canUse'=>$canUse,'noUse'=>$noUseEnd];
    return $arr;

}
