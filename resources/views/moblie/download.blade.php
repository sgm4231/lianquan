<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>APP下载</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link href="https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- jQuery (Bootstrap 的所有 JavaScript 插件都依赖 jQuery，所以必须放在前边) -->
    <script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
    <!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
    <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <style>
        body{
            background-color: #fff;
        }
        .navbar-default{
            text-align: center;
            line-height: 64px;
            font-size: 23px;
            color:#666;
        }
        .container-fluid{
            margin-top: 64px;
            text-align: center;
        }
        .list-group-item{
            border: none;
        }
        .logo{
            padding-top: 20px;
            border-bottom: 2px dotted #eee;
        }
        .xia{
            padding-top: 30px;
            padding-bottom: 30px;
        }
        .desc{
            border-top: 2px dotted #eee;
            text-align: left;
        }
        #showtext{
            padding: 10px;
            text-align: left;
            border: 1px dashed #cdcdcd;
            margin: 10px;
        }
        .btn-group-lg>.btn, .btn-lg{
            border-radius: 17px;
        }
        button{
            margin-top: 5px;
        }
    </style>
    <script>
        function is_weixn(){
            var ua = navigator.userAgent.toLowerCase();
            if(ua.match(/MicroMessenger/i)=="micromessenger") {
                //在微信中打开
                $("#tishi").show();
                return true;
            } else {
                return false;
            }
        }
    </script>
</head>
<body>

{{--头部--}}
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        链圈财经APP下载
    </div>
</nav>
<div class="container-fluid">
    <ul class="list-group">
        {{--APPlogo--}}
        <li class="list-group-item logo">
            <img src="http://www.faceke.com/api/v2/files/208" alt="..." class="img-rounded">
        </li>
        <li class="list-group-item xia">
            <i class="fa fa-apple" aria-hidden="true" style="font-size:36px;color:#666d71;"></i>
            <br/>
            <a href="https://itunes.apple.com/cn/app/链圈财经/id1252888389?mt=8">
                <button type="button" class="btn btn-info btn-lg" ><i class="fa fa-download"></i>下载 iOS 版   </button></a>
            <br/>
            <span class="label label-success">适用于IOS设备,点击进入App Store下载</span>
        </li>
        <li class="list-group-item xia">
            <i class="fa fa-android" aria-hidden="true" style="font-size:36px;color:#666d71;"></i>
            <br/>
            <a href="http://1806243173.fx.sj.360.cn/qcms/view/t/detail?id=3864549">
                <button type="button" class="btn btn-info btn-lg" ><i class="fa fa-download"></i> 下载 Android 版   </button></a>
            <br/>
            <span class="label label-success">适用于Android设备，点击进入360手机助手下载</span>
        </li>

        <li class="list-group-item desc"></li>
    </ul>

</div>
{{--提示微信打开浏览器下载--}}

<div class="container" style="display: none" id="tishi">
    <div class="row">
        <div id="showtext">
            <span class="label label-info">温馨提示</span>
            点击右上角按钮，然后在弹出的菜单中，点击在浏览器中打开，即可安装
        </div>
    </div>
</div>
</body>
</html>

<script>
    is_weixn();
</script>