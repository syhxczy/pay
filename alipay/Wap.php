<?php
require_once dirname(dirname(__FILE__)).'/Alipay.php';

/**
 * 手机网站支付
 */
class Wap extends Alipay
{

    public function pay($order=[])
    {
        parent::pay($order);
        return $this->buildPayHtml();
    }

    /**
     * 接口名称
     * @Author   syh
     * @DateTime 2018-01-22
     */
    protected function getMethod()
    {
        return 'alipay.trade.wap.pay';
    }

    /**
     * 销售产品码
     * @Author   syh
     * @DateTime 2018-01-22
    */
    protected function getProductCode()
    {
        return 'QUICK_WAP_WAY';
    }
}
