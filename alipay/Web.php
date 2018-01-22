<?php
require_once dirname(dirname(__FILE__)).'/Alipay.php';
class Web extends Alipay
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
        return 'alipay.trade.page.pay';
    }

    /**
     * 销售产品码
     * @Author   syh
     * @DateTime 2018-01-22
    protected function getProductCode()
    {
        return 'FAST_INSTANT_TRADE_PAY';
    }
}
