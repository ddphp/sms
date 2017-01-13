# 东大短信平台

东大第三方短信接口 PHP 封装。

## 神州软科短信接口

目录

1. 常量
2. 初始化
3. 设置
4. 使用
5. 异常

### 常量

`\Sms\Szrk::API  // API URI`

### 初始化

```php
$smsSzrk = new \Sms\Szrk();
```

### 设置

```php
$smsSzrk->account($user, $password);  // 账号设置 必须 
$smsSzrk->sign($sign);  // 短信内容签名设置 (发送短信时必须设置项)
$smsSzrk->encode($encode);  // 短信字符集类型 默认：utf-8 可选
```

### 使用

```php
/* 发送短信 */
$smsSzrk->send($mobile, $content);  // 实时发送
$smsSzrk->send($mobile, $content, $sendTime);  // 定时发送

/* 获取短信剩余数量 */
$smsSzrk->selSum();
```

### 异常

当接口调用发生错误时触发 `\Sms\Exceptions\SzrkException` 异常。

