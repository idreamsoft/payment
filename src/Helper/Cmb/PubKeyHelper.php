<?php
namespace Payment\Helper\Cmb;

use Payment\Common\Cmb\CmbBaseStrategy;
use Payment\Common\Cmb\Data\PubKeyData;
use Payment\Common\CmbConfig;
use Payment\Config;

/**
 * 招商公钥获取
 * Class PubKeyHelper
 * @package Payment\Helper\Cmb
 */
class PubKeyHelper extends CmbBaseStrategy
{
    public function getBuildDataClass()
    {
        $this->config->getewayUrl = 'https://b2b.cmbchina.com/CmbBank_B2B/UI/NetPay/DoBusiness.ashx';
        if ($this->config->useSandbox) {// 测试
            $this->config->getewayUrl = 'http://121.15.180.72/CmbBank_B2B/UI/NetPay/DoBusiness.ashx';
        }

        // return PubKeyData::class;
        return 'Payment\Common\Cmb\Data\PubKeyData';
    }

    protected function retData(array $ret)
    {
        $json = json_encode($ret, JSON_UNESCAPED_UNICODE);

        $postData = CmbConfig::REQ_FILED_NAME . '=' . $json;
        $retData = $this->sendReq($postData);

        if ($this->config->returnRaw) {
            $retData['channel'] = Config::CMB_PUB_KEY;
            return $retData;
        }

        // 正确情况
        $rData = array(
            'is_success'    => 'T',
            'response'  => array(
	        'pub_key'   => $retData['fbPubKey'],
                'channel'   => Config::CMB_PUB_KEY,
                'time'   => date('Y-m-d H:i:s', strtotime($retData['dateTime'])),// Y-m-d H:i:s,
            ),
        );

        return $rData;
    }
}
