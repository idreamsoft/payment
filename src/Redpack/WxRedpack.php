<?php
namespace Payment\Redpack;

use Payment\Common\Weixin\Data\RedpackData;
use Payment\Common\Weixin\WxBaseStrategy;
use Payment\Config;
use Payment\Utils\ArrayUtil;

/**
 * 微信企业红包接口
 * Class WxRedpack
 * @package Payment\Redpack
 * anthor helei
 */
class WxRedpack extends WxBaseStrategy
{
    protected $reqUrl = 'https://api.mch.weixin.qq.com/{debug}/mmpaymkttransfers/sendredpack';

    public function getBuildDataClass()
    {
        // return RedpackData::class;
        return 'Payment\Common\Weixin\Data\RedpackData';
    }

    /**
     * 转款的返回数据
     * @param array $ret
     * @return mixed
     */
    protected function retData(array $ret)
    {
        if ($this->config->returnRaw) {
            $ret['channel'] = Config::WX_RED;
            return $ret;
        }

        // 请求失败，可能是网络
        if ($ret['return_code'] != 'SUCCESS') {
            return $retData = array(
                'is_success'    => 'F',
                'error' => $ret['return_msg'],
                'channel'   => Config::WX_RED,
            );
        }

        // 业务失败
        if ($ret['result_code'] != 'SUCCESS') {
            return $retData = array(
                'is_success'    => 'F',
                'error' => $ret['err_code_des'],
                'channel'   => Config::WX_RED,
            );
        }

        return $this->createBackData($ret);
    }

    /**
     * 返回数据
     * @param array $data
     * @return array
     */
    protected function createBackData(array $data)
    {
        $retData = array(
            'is_success'    => 'T',
            'response'  => array(
            	'trans_no'   => $data['mch_billno'],
                'transaction_id'  => $data['send_listid'],
                'total_amount'  => $data['total_amount'],
                'pay_date' => time(),// 红包成功时间
                'device_info' => ArrayUtil::get($data, 'device_info', 'WEB'),
                'channel'   => Config::WX_RED,
            ),
        );

        return $retData;
    }

    /**
     * 企业转账，不需要签名，使用返回true
     * @param array $retData
     * @return bool
     */
    protected function verifySign(array $retData)
    {
        return true;
    }
}
