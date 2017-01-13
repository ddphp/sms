<?php
namespace Sms;

use Sms\Exceptions\SzrkException;

class Szrk
{
    /**
     * 神州软科Api SDK地址
     */
    const API = 'http://api.bjszrk.com/sdk/';

    /**
     * @var string 账号
     */
    private $user;
    /**
     * @var string 密码
     */
    private $pwd;

    /**
     * @var string 短信签名
     */
    private $sign = '';

    /**
     * @var string 短信内容字符集
     */
    private $encode = 'utf-8';

    /**
     * 账号设置
     * @param string $user 账号名
     * @param string $password 密码
     * @return $this
     */
    public function account($user, $password)
    {
        $this->user = $user;
        $this->pwd  = $password;
        return $this;

    }

    /**
     * 设置签名
     * @param string $sign 签名
     * @return $this
     */
    public function sign($sign)
    {
        $this->sign = $sign;
        return $this;
    }

    /**
     * 设置短信内容字符集编码
     * @param string $encode
     * @return $this
     */
    public function encode($encode)
    {
        $this->encode = $encode;
        return $this;
    }

    /**
     * 发送短信(单条)
     * @param string $mobile   手机号
     * @param string $content  发送内容
     * @param string $sendTime 定时发送时间
     * @return string 发送状态ID 数据字串格式
     * @throws SzrkException
     */
    public function send($mobile, $content, $sendTime = '')
    {
        $res = intval($this->httpPost(self::API.'BatchSend.aspx', [
            'CorpID'    => $this->user,
            'Pwd'       => $this->pwd,
            'Mobile'    => $mobile,
            'Content'   => $content.'【'.$this->sign.'】',
            'Cell'      => '',
            'SendTime'  => $sendTime,
            'encode'    => $this->encode,
        ]));

        return $this->check($res);
    }

    /**
     * 获取短信剩余条数
     * @return int 剩余条数
     * @throws SzrkException
     */
    public function selSum()
    {
        $res = intval($this->httpPost(self::API.'SelSum.aspx', [
            'CorpID'    => $this->user,
            'Pwd'       => $this->pwd
        ]));
        return $this->check($res);
    }

    /**
     * 检查返回值错误
     * @param int $res 接口返回值
     * @return bool
     * @throws SzrkException
     */
    private function check($res)
    {
        /**
         * @var array 接口返回错误码清单
         */
        $errorCode = [
            -1    => '账号未注册',
            -2    => '其他错误',
            -3    => '帐号或密码错误',
            -4    => '一次提交信息不能超过10000个手机号码，号码逗号隔开',
            -5    => '余额不足，请先充值',
            -6    => '定时发送时间不是有效的时间格式',
            -8    => '发送内容需在3到250字之间',
            -9    => '发送号码为空',
            -104  => '短信内容包含关键字',
        ];
        if ($res < 0) {
            throw new SzrkException($errorCode[$res], $res);
        }

        return $res;
    }

    /**
     * PHP-CURL http post请求
     * @param string $url       url地址
     * @param array  $data      数据
     * @param array  $params    参数
     * @return string
     */
    private function httpPost($url, $data, $params = array())
    {
        if (!function_exists('curl_init')) exit('PHP CURL扩展未开启');
        $curl = curl_init();
        if (!empty($params)) {
            $url = $url . (strpos($url, '?') ? '&' : '?')
                . (is_array($params) ? http_build_query($params) : $params);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}