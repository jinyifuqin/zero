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

    $flag = false;
    $nowStamp = time();
    $mintime = \think\Db::query('select *  from yzt_points where user_id=? ORDER BY create_time limit 1',[$userId]);
    if($mintime){
        $mmtime = strtotime(date('Y-m-d',strtotime($mintime[0]['create_time'])));
        $pointObj = new \app\index\model\Points();
        $nowYMD = strtotime(date('Y-m-d',$nowStamp));
        $x = ($nowYMD-$mmtime)/(24*60*60);
        $flag = \app\index\model\Points::get([
            'user_id' => $userId,
            'create_time'=>date('Y-m-d H:i:s',$nowYMD),
            'get_type'=>2,
            'type'=>1,
            'frozen_flag'=>1
        ]);
    }

//    echo "<pre>";var_dump($x);exit;
    $canUse = 0;
    if(!$flag && !empty($mintime) && $x){
        \app\index\model\Points::destroy([
            'user_id' => $userId,
            'get_type'=>2,
            'type'=>1,
            'frozen_flag'=>1
        ]);
        call_self($userId,$x,$mmtime,$pointObj);

    }

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
//    $canUse = \app\index\model\Points::where([
//        'user_id' => $userId,
//        'get_type'=>2,
//        'type'=>1,
//        'frozen_flag'=>1
//    ])->sum('count');

    $pO = new \app\index\model\Points();
    $canUse = $pO->where('user_id',"=",$userId)
        ->where('get_type',">",0)
        ->where('type',"=",1)
        ->where('frozen_flag',"=",1)
        ->sum('count');

    $arr = ['canUse'=>round($canUse,3),'noUse'=>round($noUseEnd,3)];
    return $arr;

}

function call_self($userId,$x,$mmtime,$pointObj){
    static $i=0;
    $y = $mmtime+60*60*24*($i+2);
    $noUseAdd = \think\Db::table('yzt_points')
        ->where('create_time','<',date('Y-m-d H:i:s',$y))
        ->where('user_id',$userId)
        ->where('get_type',0)
        ->where('type',1)
        ->where('frozen_flag',0)
        ->sum('count');

    $noUseDel = \app\index\model\Points::where([
        'user_id' => $userId,
        'get_type'=>2,
        'type'=>0,
        'frozen_flag'=>0
    ])->sum('count');
    $true = $noUseAdd - $noUseDel;
    $givePoint['user_id'] = $userId;
    $givePoint['count'] = $true*0.001;
    $givePoint['type'] = 1;
    $givePoint['get_type'] = 2;
    $givePoint['frozen_flag'] = 1;
    $givePoint['create_time'] = date('Y-m-d H:i:s',$mmtime+60*60*24*($i+1));

    $delPoint['user_id'] = $userId;
    $delPoint['count'] = $true*0.001;
    $delPoint['type'] = 0;
    $delPoint['get_type'] = 2;
    $delPoint['frozen_flag'] = 0;
    $delPoint['create_time'] = date('Y-m-d H:i:s',$mmtime+60*60*24*($i+1));

    $list[] = $givePoint;
    $list[] = $delPoint;

    $pointObj->saveAll($list);
//    echo "<pre>";var_dump(date('Y-m-d',$mmtime));var_dump($list);exit;
    if($i<$x-1){
        $i++;
        call_self($userId,$x,$mmtime,$pointObj);
    }
}


function getQrcodePic($str){
    $userid = $_SESSION['adminUserInfo']->id;
    $url = "http://".$_SERVER['HTTP_HOST']."?userid=$userid";
    $link = "http://qr.liantu.com/api.php?text=$url";
    $src_path = $link;
    $img = imagecreatefrompng($src_path);// 加载已有图像
    list($width,$height)=getimagesize($src_path);
    $canvas = imagecreatetruecolor($width,$height+50);
    $white=imagecolorallocate($canvas,255,255,255);
    $black=imagecolorallocate($canvas,0,2,22);
    imagecolortransparent($canvas,$white); //3.设置透明色
    imagefill($canvas,0,0,$white);
    imagecopyresampled($canvas,$img,0,0,0,0,$width,$height,$width,$height);
    imagestring($img,6,5,5,5,5);
    $font = 'public'.DS.'admins'.DS.'font'.DS.'abc.ttf';
    imagettftext($canvas,20,0,50,$height+20,$black,$font,$str);
    header('Content-Type: image/png');
    imagepng($canvas);
    imagedestroy($canvas);
}

function i_array_column($input, $columnKey, $indexKey=null){

    if(!function_exists('array_column')){
        $columnKeyIsNumber  = (is_numeric($columnKey))?true:false;
        $indexKeyIsNull            = (is_null($indexKey))?true :false;
        $indexKeyIsNumber     = (is_numeric($indexKey))?true:false;
        $result                         = array();
        foreach((array)$input as $key=>$row){
            if($columnKeyIsNumber){
                $tmp= array_slice($row, $columnKey, 1);

                $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null;
            }else{
                $tmp= isset($row[$columnKey])?$row[$columnKey]:null;
            }
            if(!$indexKeyIsNull){
                if($indexKeyIsNumber){
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key))?current($key):null;
                    $key = is_null($key)?0:$key;
                }else{
                    $key = isset($row[$indexKey])?$row[$indexKey]:0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }else{
        return array_column($input, $columnKey, $indexKey);
    }
}
