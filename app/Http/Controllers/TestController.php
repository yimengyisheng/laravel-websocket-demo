<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use SwooleTW\Http\Websocket\Facades\Websocket;


class TestController extends Controller
{
    public function index(Websocket $websocket,$data)
    {
        Log::info('socket',[1,2,2,3]);
        $websocket->emit('message', 'this is a test');
    }

    public function test()
    {
        Websocket::emit ('message','this is a test');
    }
}
