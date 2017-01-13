<?php
namespace Sms;


class Szrk
{
    /**
     * 神州软科Api SDK地址
     */
    const SZRK_URL = 'http://api.bjszrk.com/sdk/';

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

//    /**
//     * @var array 配置参数
//     */
//    private $config = [
//        'sign'      => '忻州东大',       //短信签名
//        'send_time' => '',       //定时发送
//        'encode'    => 'utf-8',  // 短信内容字符集
//    ];

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setPwd($password)
    {
        $this->pwd = $password;
        return $this;
    }

    public function setSign($sign)
    {
        $this->sign = $sign;
        return $this;
    }

    public function setEncode($encode)
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
     * @throws \Exception 当发送失败时
     */
    public function send($mobile, $content, $sendTime = '')
    {
        $res = intval($this->httpPost(self::SZRK_URL.'BatchSend.aspx', [
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
     * 剩余短信条数提醒
     * 当剩余短信数等于设定值时，会自动发送一条短信至指定手机号
     * @param int    $warn   发送短信剩余条数提醒的设定值
     * @param string $mobile 接收短信提醒的手机号
     */
    public function warnSms($warn, $mobile)
    {
        if ($this->selSum() <= $warn) {
            $content = '账号['.$this->user.']剩余短信数已不足'.$warn.'条,请及时充值。';
            $this->send($mobile, $content);
        }
    }

    /**
     * 获取短信剩余条数
     * @return int 剩余条数
     * @throws \Exception
     */
    public function selSum()
    {
        $res = intval($this->httpPost(self::SZRK_URL.'SelSum.aspx', [
            'CorpID'    => $this->user,
            'Pwd'       => $this->pwd
        ]));
        return $this->check($res);
    }

    /**
     * 检查返回值错误
     * @param int $res 接口返回值
     * @return bool
     * @throws \Exception
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
            throw new \Exception($errorCode[$res], $res);
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