<html lang="zh-CN" style="font-size: 53.3333px;">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui" />
    <link rel="shortcut icon" href="/h5/favicon.ico" />
    <meta name="keywords" content="plus, think, php, spa" />
    <meta name="description" content="会员注册" />
    <script src="/h5/libs/strophe-1.2.8.min.js"></script>

    <title>链圈财经</title>

    <link rel="prefetch" href="/h5/js/rank.js" />
    <link rel="prefetch" href="/h5/js/message.js" />
    <link rel="prefetch" href="/h5/js/question.js" />
    <link rel="prefetch" href="/h5/js/post.js" />
    <link rel="prefetch" href="/h5/js/wallet.js" />
    <link rel="prefetch" href="/h5/js/feed.js" />
    <link rel="prefetch" href="/h5/js/news.js" />
    <link href="/h5/css/app.00c6e98a.css" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="chrome-extension://pioclpoplcdbaefihamjohnefbikjilc/content.css" />
    <style>
        .m-head-top{
            width:100%;
        }
        .c_59b6d7{
            min-width: 75px;
        }
    </style>
</head>
<body style="">

<div id="app" class="wap-wrap">
    <!---->
    <div class="p-signup">
        <header class="m-box m-aln-center m-head-top m-pos-f m-main m-bb1">

            <div class="m-box m-aln-center m-justify-center m-flex-grow1 m-flex-base0 m-head-top-title">
                <span style="font-size: .40rem;">新用户注册</span>
            </div>

        </header>
        <main style="padding-top: 0.9rem;">
            <div class="m-form-row m-main">
                <label for="username">用户名</label>
                <div class="m-input">
                    <input type="text" id="username" placeholder="用户名不能低于2个中文或4个英文" maxlength="22" onpropertychange="is_ok()" oninput="is_ok(this.value)" />
                </div>
                <svg class="m-style-svg m-svg-def" style="display: none;">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#base-clean"></use>
                </svg>
            </div>
            <div class="m-form-row m-main">
                <label for="phone">手机号</label>
                <div class="m-input">
                    <input id="phone" type="number" pattern="[0-9]*" oninput="is_ok(value=value.slice(0, 11))" placeholder="输入11位手机号" onpropertychange="is_ok()" />
                </div>
                <span class="signup-form--row-append c_59b6d7 disabled" >获取验证码</span>
            </div>
            <!---->
            <div class="m-form-row m-main">
                <label for="code">验证码</label>
                <div class="m-input">
                    <input id="code" type="number" pattern="[0-9]*" oninput="is_ok(value=value.slice(0, 6))" placeholder="输入4-6位验证码" onpropertychange="is_ok()" oninput="is_ok(this.value)"/>
                </div>
                <svg class="m-style-svg m-svg-def" style="display: none;">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#base-clean"></use>
                </svg>
            </div>
            <div class="m-form-row m-main">
                <label for="password">密码</label>
                <div class="m-input">
                    <input id="password" maxlength="16" type="password" placeholder="输入6位以上登录密码" onpropertychange="is_ok()" oninput="is_ok(this.value)"/>
                </div>
                <svg class="m-style-svg m-svg-def">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#eye-close"></use>
                </svg>
            </div>

            <div class="m-form-row m-main">
                <label for="username">邀请码</label>
                <div class="m-input">
                    <input type="text" id="user_code" placeholder="填写推荐码（必填）" maxlength="22"  name="user_code" value="{{$user_code}}" onpropertychange="is_ok()" oninput="is_ok(this.value)"/>
                </div>
                <svg class="m-style-svg m-svg-def" style="display: none;">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#base-clean"></use>
                </svg>
            </div>
            <div class="m-box m-aln-center m-text-box m-form-err-box">
                <span id="tishi"></span>
            </div>
            <div class="m-form-row" style="border: 0px;">
                <button disabled="disabled" class="m-long-btn m-signin-btn"><span>注册</span></button>
            </div>
        </main>
    </div>

</div>

<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.js"></script>
<script src="https://cdn.bootcss.com/layer/3.1.0/layer.js"></script>
</body>
</html>
<script>

    function is_ok(){
        console.log(222);
        checkBtn()
    }

    function checkBtn() {
      
        //检查是否都输入了,如果都输入了就显示颜色按钮，否则是灰色
        var username  = $('#username').val();
        var phone  = $('#phone').val();
        var code  = $('#code').val();
        var password  = $('#password').val();
        var user_code  = $('#user_code').val();

        // 处理发送短信按钮状态
        if(phone.length == 11){
            // console.log(phone);
            $(".signup-form--row-append").removeClass('disabled');
            //增加点击发送按钮

        }else{
            // console.log('手机号码还不够11位');
            $(".signup-form--row-append").addClass('disabled');
            //取消点击发送按钮
            $('.m-long-btn').attr("disabled","disabled");
            return false;
        }

        //验证码判断
        if(code.length >4 && code.length <=6){
            // console.log('验证码合法);
        }else{
            // console.log('验证码不合法');
            $('.m-long-btn').attr("disabled","disabled");
        }

        //密码长度判断
        if(password.length <6){
            $('.m-long-btn').attr("disabled","disabled");
            return false;

        }

        //判断是否都填写了，如果都填写了就显示按钮亮色，否则灰色
        if(username != '' && phone != '' && code != '' && password !='' && user_code != ''){
            //都填写了
           // console.log('都填写了');
            $('.m-long-btn').removeAttr("disabled");
            //可以调用

        }else{
            //还没全写
            //console.log('还没全写')
            $('.m-long-btn').attr("disabled","disabled");

        }

    }

    var secs = 60;
    var is_can_send = 1;
    //发送短信
    $(".signup-form--row-append").click(function(){

        var phone  = $('#phone').val();
        if(phone == ''){
            return false;
        }

        if(is_can_send != 1){
            return false;
        }else{
            sendSMS(phone);
        }

        //禁止提交
        is_can_send = 0;
        //灰色按钮
        $('.signup-form--row-append').addClass("disabled");
        console.log(1);
        //处理计时
        for(var i=1;i<=secs;i++) {
            window.setTimeout("update(" + i + ")", i * 1000);
        }

        $('.signup-form--row-append').removeClass("disabled");

    })


    //计时器处理点击验证码显示
    function update(i) {

        if(i == secs) {
            console.log('恢复')
            is_can_send = 1;
            $('.signup-form--row-append').html('获取验证码');
            $(".signup-form--row-append").removeClass('disabled');
        }else{
            var num = secs - i ;
            $('.signup-form--row-append').html(num +'s后重发');
        }
    }

    function sendSMS() {

        var phone  = $('#phone').val();
        $.ajax({
            type: "POST",
            url: "/api/v2/verifycodes/register",
            data: {phone:phone},
            dataType: "json",
            success: function(data){

            },error:function(data){
                //错误提示
                console.log(data.responseJSON.errors.phone[0]);
                $("#tishi").html(data.responseJSON.errors.phone[0]);
            }
        });
    }

    //点击提交
    $(".m-long-btn").click(function(){
        //开始注册
        //检查是否都输入了,如果都输入了就显示颜色按钮，否则是灰色
        var username  = $('#username').val();
        var phone  = $('#phone').val();
        var code  = $('#code').val();
        var password  = $('#password').val();
        var user_code  = $('#user_code').val();


        $.ajax({
            type: "POST",
            url: "/api/v2/users",
            data: {name:username,phone:phone,verifiable_code:code,password:password,verifiable_type:'sms',user_code:user_code,},
            dataType: "json",
            success: function(data){
              //注册成功
                $(".m-long-btn > span").html('注册成功，即将进入下载APP页面');
                window.location.href="/api/v2/share/appDownload";

            },error:function(data){
                //错误提示
                //
                var info = data.responseJSON.message;

                for(var i in data.responseJSON.errors) {
                    info +=','+data.responseJSON.errors[i][0];
                    console.log(data.responseJSON.errors[i][0]);
                }

                console.log(info);
                $("#tishi").html(info);

            }
        });
    })




</script>

