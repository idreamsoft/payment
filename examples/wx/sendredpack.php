<?php
/**
 * Created by PhpStorm.
 * User: helei
 * Date: 2017/4/30
 * Time: 下午4:13
 */

require_once __DIR__ . '/../../autoload.php';

use Payment\Common\PayException;
use Payment\Client\Redpack;
use Payment\Config;

date_default_timezone_set('Asia/Shanghai');
$wxConfig = require_once __DIR__ . '/../wxconfig.php';

$data = array(
    'trans_no' => time(),
    'openid' => 'oEpymv96jSqtVKe86JURaBJQF3AY',
    'amount' => '0.01',
    'total_num' => '1',//红包发放总人数
    'act_name'=>'活动名称',
    'send_name' => '商户名称',
    'scene_id' => 'PRODUCT_3',
    'wishing' => '红包祝福语',//红包祝福语
    'remark' => '猜越多得越多，快来抢！',//备注
);
$wxConfig['app_id'] = 'wx2cb18020197974af';
var_dump($wxConfig);

try {
    $ret = Redpack::run(Config::WX_RED, $wxConfig, $data);
} catch (PayException $e) {
    echo $e->errorMessage();
    exit;
}

echo json_encode($ret, JSON_UNESCAPED_UNICODE);
