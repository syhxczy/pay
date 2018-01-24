<?php

/**
* 支付宝类
* @param array $config 配置
* @param string $url HTTPS请求地址
* @param string $alipay_public_key 支付宝公钥
* @param string $rsaPrivateKey 商户私钥
*/
class Alipay
{
    public $config;

    public $url = 'https://openapi.alipay.com/gateway.do';

    public $alipay_public_key = "";

    public $rsaPrivateKey = "";

    public function __construct()
    {
        $this->config = array(
            'app_id' => '',
            'format' => 'json', 
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'return_url'  => '',
            'notify_url'  => '',
            'method'      => '',
            'sign'        => '',
            'biz_content' => '',
        );
    }

    /**
     * 支付
     * @Author   syh
     * @DateTime 2018-01-22
     * @param    array      $order 订单详情
     */
    public function pay($order=[])
    {
        $order['product_code'] = $this->getProductCode();
        $this->config['method'] = $this->getMethod();
        $this->config['biz_content'] = json_encode($order);
        $this->config['sign'] = $this->getSign();
    }

    /**
     * 获取签名
     * @Author   syh
     * @DateTime 2018-01-22
     * @return   string     签名
     */
    public function getSign() {
        $priKey = $this->rsaPrivateKey;
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($this->getSignContent($this->config), $sign, $res, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 获取内容签名
     * @Author   syh
     * @DateTime 2018-01-22
     * @param    array      $data   签名的内容
     * @param    boolean    $verify 是否验证sign与sign_type
     * @return   string             内容签名
     */
    public function getSignContent($data=[], $verify=false)
    {
        $stringToBeSigned = '';
        ksort($data);
        foreach ($data as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
        }
        return trim($stringToBeSigned, '&');
    }

    /**
     * 验证的内容签名
     * @Author   syh
     * @DateTime 2018-01-22
     * @param    array      $params 签名的内容
     * @return   string             内容签名
     */
    public function rSignContent($params=[]) {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, "UTF-8");
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 创建表单
     * @Author   syh
     * @DateTime 2018-01-22
     */
    public function buildPayHtml()
    {
        $para_temp = $this->config;
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->url."?charset=utf-8' method='POST'>";
        while (list ($key, $val) = each ($para_temp)) {
            if (false === $this->checkEmpty($val)) {
                $val = str_replace("'","&apos;",$val);
                $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
            }
        }
        $sHtml = $sHtml."<input type='submit' value='ok' style='display:none;''></form>";
        $sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
        return $sHtml;
    }

    /**
     * 同步/异步验证
     * @Author   syh
     * @DateTime 2018-01-22
     * @param    array      $data 验证的内容
     * @return   boolean          真或假
     */
    public function verify($data=[]) {
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->alipay_public_key, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        $sign = $data['sign'];
        $data['sign_type'] = null;
        $data['sign'] = null;
        $toVerify = $this->rSignContent($data);
        $result = (bool)openssl_verify($toVerify, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        return $result;
    }

    /**
     * 检查是否为空
     * @Author   syh
     * @DateTime 2018-01-22
     * @return   boolean          真或假
     */
    public function checkEmpty($value='') {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }

    /**
     * 转换字符集编码
     * @Author   syh
     * @DateTime 2018-01-24
     * @return   string
     */
    public function characet($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }
} 

?>