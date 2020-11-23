<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class TestController extends Controller
{
    //websocket服务中的发送消息到指定客户端
    public function test(Request $request)
    {
        $fd=$request->get ('fd',10);
        $content=$request->get ('content','content');
        Log::info ($fd);
        $swoole = app('swoole');
        $swoole->push($fd, $content);
    }

    /**
     * @业务接口（模拟文档导出）
     */
    public function push(Request $request)
    {
        $content=$request->get('content');//要推送的内容
        $fd=$request->get('fd');//目标客户端
        //$fd是客户端连接的fd,可以在中间件里去和系统的用户id绑定映射
        //模拟文档导出完毕推送消息给客户端
        if(true){
            $client=new Client();
            //url为websocket服务
            $res=$client->get ('www.laravel7.com:5200/test?fd='.$fd.'&content='.$content);
            $data =  $res->getBody()->getContents();
        }
        if($res->getStatusCode() !==200){
            Log::error('swoole_http请求错误:',['msg'=>$data]);
        }
    }

}
