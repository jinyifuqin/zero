<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
//        echo password(123,456);exit;
        return view();
    }

    public function ajax()
    {
//        echo password(123,456);exit;
        $arr = array(
            "a"=>"hello",
            "b"=>"world"
        );
        $re = json_encode($arr);
        echo $re;
//        return view();
    }
}
