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
    $re = \app\index\model\Points::all(['user_id'=>$userId]);
    $nowStamp = time();
    $prevAllCount = \app\index\model\Points::where('user_id',$userId)->sum('count');
//    $mintime = \think\Db::query('select *  from yzt_points where user_id=? and create_time=(
//                  SELECT MIN(create_time) from yzt_points
//                )',[$userId]);
//    $mintime = $mintime[0]['create_time'];
    $showMony = 0;
    foreach($re as $k=>$v){
        $tmp[]=$v->count;
        $allPoint = 0;  //总余额
        $t = $v->create_time;   //生成积分时间
        $trueCount = $v->true_count;
        $time = strtotime($t);
        $t2 = date('Y-m-d',$time);
        $tomorrow = strtotime($t2)+60*60*24;    // 该订单的第二天凌晨
        $oneday = 60*60*24;
        $point = $v->count; //该订单积分数额
//        $adminGetBillType = \app\index\model\Trades::where('trade_number',$v->trade_number)->value('admin_get_bill_type');
//        echo "<pre>";var_dump($tradeRe);
        if($nowStamp >= $tomorrow && $point > 0){
            $shijiancha = $nowStamp-$tomorrow;
            $x = floor($shijiancha/$oneday)+1;    //超过下订单的次日凌晨几天
            $sum=0;
            foreach($tmp as $kk=>$vv){
                $sum += $vv;
            }
            $allPoint += $sum * 0.0001*$x;
//            echo "<pre>";var_dump($allPoint);
        }else{
            $allPoint = $trueCount;
        }
        $showMony += $allPoint;
        $v->true_count = $allPoint;
        $v->save();
    }

    $arr = ['pointAll'=>$prevAllCount,'true_cont'=>$showMony];
    return $arr;

}
