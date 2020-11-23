## laravel7 结合 hhxsv5/laravel-s websocket客户端和服务端通信

## 安装配置及启动
- 安装配置laravel7
- 安装hhxsv5/laravel-s包
```$xslt
composer require hhxsv5/laravel-s
```

- 启动websocket服务可参考 [hhxsv5/laravel-s安装配置文档](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md)
````
php bin/laravels start
````
### 适用业务举例
- 导入导出实时通知等
- 实时数据

### NGINX代理配置
````
map $http_upgrade $connection_upgrade {
        default upgrade;
        ''      close;
    }
    upstream swoole {
        # 通过 IP:Port 连接
        server 127.0.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
        # 通过 UnixSocket Stream 连接，小诀窍：将socket文件放在/dev/shm目录下，可获得更好的性能
        #server unix:/yourpath/laravel-s-test/storage/laravels.sock weight=5 max_fails=3 fail_timeout=30s;
        #server 192.168.1.1:5200 weight=3 max_fails=3 fail_timeout=30s;
        #server 192.168.1.2:5200 backup;
        keepalive 16;
    }
    server{
            listen 80;
            server_name www.laravel7.com;
            index index.php;
            root /Library/WebServer/Documents/laravel7/public;
            try_files       $uri /index.php$is_args$query_string;
            location =/ws {
            # proxy_connect_timeout 60s;
            # proxy_send_timeout 60s;
            proxy_read_timeout 600s;
            proxy_http_version 1.1;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Real-PORT $remote_port;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header Host $http_host;
            proxy_set_header Scheme $scheme;
            proxy_set_header Server-Protocol $server_protocol;
            proxy_set_header Server-Name $server_name;
            proxy_set_header Server-Addr $server_addr;
            proxy_set_header Server-Port $server_port;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection $connection_upgrade;
            proxy_pass http://swoole;
        }
            location ~ \.php$ {
                    try_files                   $uri = 404;
                    fastcgi_split_path_info     ^(.+\.php)(/.+)$;
                    fastcgi_connect_timeout     30s;
                    fastcgi_read_timeout        100s;
                    fastcgi_pass                127.0.0.1:9002;
                    fastcgi_param               SCRIPT_FILENAME  $document_root$fastcgi_script_name;
                    fastcgi_param               SCRIPT_NAME $fastcgi_script_name;
                    include                     fastcgi_params;
            }
    }
````
### laravel-s拦截器配置

- 客户端连接携带身份信息如前后端分离项目可以携带token等,拦截器根据token信息解析用户id，websocket连接($fd)绑定身份信息($userId)。
- 自行维护$fd和userId的映射关系接口
### 服务端推送信息
- 路由示例
````
\Illuminate\Support\Facades\Route::get('/test','TestController@test');//websocket服务
\Illuminate\Support\Facades\Route::get('/push','TestController@push');//业务接口
````
- 代码示例
````
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
````
### 其他注意事项
连接保持时长配置(客户端可以在该时间周期内发送心跳数据)
````$xslt
    proxy_read_timeout 600s;//十分钟保持客户端服务端连接（nginx配置文件）。
````
### 客户端示例代码   file.html
````$xslt
<!DOCTYPE HTML>
<html>
   <head>
   <meta charset="utf-8">
   <title>websocket</title>
    
      <script type="text/javascript">
         function WebSocketTest()
         {
            if ("WebSocket" in window)
            {
               console.log("您的浏览器支持 WebSocket!");
               
               // 打开一个 web socket
               var ws = new WebSocket("ws://www.laravel7.com/ws?id=4");
                
               ws.onopen = function()
               {
                  // Web Socket 已连接上，使用 send() 方法发送数据
                  ws.send("发送数据");
                  console.log("客户端数据发送中...");
               };
                
               ws.onmessage = function (evt) 
               { 
                  var received_msg = evt.data;
                  console.log(received_msg);

               };
               ws.onclose = function()
               { 
                  // 关闭 websocket
                  console.log("连接已关闭..."); 
               };
            }
            
            else
            {
               // 浏览器不支持 WebSocket
               console.log("您的浏览器不支持 WebSocket!");
            }
         }
      </script>
        
   </head>
   <body>
   
      <div id="sse">
         <a href="javascript:WebSocketTest()">test</a>
      </div>
      
   </body>
</html>
````

