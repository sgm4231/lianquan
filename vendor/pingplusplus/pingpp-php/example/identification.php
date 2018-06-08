<?php
/**
 * Ping++ Server SDK
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可根据自己网站需求按照技术文档编写, 并非一定要使用该代码。
 * 该代码仅供学习和研究 Ping++ SDK 使用，仅供参考。
 */

require dirname(__FILE__) . '/../init.php';
// 示例配置文件，测试请根据文件注释修改其配置
require 'config.php';
// 设置 API Key
\Pingpp\Pingpp::setApiKey(APP_KEY);

// 设置私钥
\Pingpp\Pingpp::setPrivateKeyPath(__DIR__ . '/your_rsa_private_key.pem');

// 调用身份证认证接口
try {
    $result = \Pingpp\Identification::identify(array(
        'type' => 'id_card',
        'app' => APP_ID,
        'data' => array(
            'id_name' => '张三', // 姓名
            'id_number' => '310181198910107641' // 身份证号
        )
    ));
    echo $result;
} catch (\Pingpp\Error\Base $e) {
    echo $e->getMessage();
}

// 调用银行卡认证接口
try {
    $result = \Pingpp\Identification::identify(array(
        'type' => 'bank_card',
        'app' => APP_ID,
        'data' => array(
            'id_name' => '张三', // 姓名
            'id_number' => '310181198910107641', // 身份证号,
            'card_number' => '6201111122223333', // 银行卡号
            'phone_number' => '18623234545' // 银行预留手机号，不支持 178 号段
        )
    ));
    echo $result;
} catch (\Pingpp\Error\Base $e) {
    echo $e->getMessage();
}
