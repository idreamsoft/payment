<?php
namespace Payment;

use Payment\Common\BaseStrategy;
use Payment\Common\PayException;
// use Payment\Redpack\AliRedpack;
use Payment\Redpack\WxRedpack;

/**
 * 红包上下文
 * @link      https://www.gitbook.com/book/helei112g1/payment-sdk/details
 * @link      https://helei112g.github.io/
 * Class RedpackContext
 * @package Payment
 */
class RedpackContext
{
    /**
     * 转款渠道
     * @var BaseStrategy
     */
    protected $redpack;

    /**
     * 设置对应的退款渠道
     * @param string $channel 退款渠道
     *  - @see Config
     *
     * @param array $config 配置文件
     * @throws PayException
     * @author helei
     */
    public function initRedpack($channel, array $config)
    {
        try {
            switch ($channel) {
                case Config::ALI_RED:
                    $this->redpack = new AliRedpack($config);
                    break;
                case Config::WX_RED:
                    $this->redpack = new WxRedpack($config);
                    break;
                default:
                    throw new PayException('当前仅支持：ALI WEIXIN两个常量');
            }
        } catch (PayException $e) {
            throw $e;
        }
    }

    /**
     * 通过环境类调用支付转款操作
     *
     * @param array $data
     *
     * @return array
     * @throws PayException
     * @author helei
     */
    public function redpack(array $data)
    {
        if (! $this->redpack instanceof BaseStrategy) {
            throw new PayException('请检查初始化是否正确');
        }

        try {
            return $this->redpack->handle($data);
        } catch (PayException $e) {
            throw $e;
        }
    }
}
