# BetterUs Push Library



## 平台支持

- [华为推送](https://developer.huawei.com/consumer/cn/service/hms/catalog/huaweipush_agent.html?page=hmssdk_huaweipush_api_reference_agent_s2)
- [华为新版推送](https://developer.huawei.com/consumer/cn/doc/development/HMS-References/push-sendapi)
- [小米推送](https://dev.mi.com/console/doc/detail?pId=1163)
- [魅族推送](https://github.com/MEIZUPUSH/PushAPI#api_standard_index)
- [Oppo推送](https://storepic.oppomobile.com/openplat/resource/201910/18/OPPO推送平台服务端API-V1.9.3.pdf)
- [Vivo推送](https://swsdl.vivo.com.cn/appstore/developer/uploadfile/20191210/we5XL6/PUSH-UPS-API接口文档%20-%202.7.0版.pdf)
- [iOS APNs推送](https://developer.apple.com/library/archive/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/APNSOverview.html#//apple_ref/doc/uid/TP40008194-CH8-SW1)
- [iOS APNs(base on token)推送](https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/establishing_a_token-based_connection_to_apns)
- [极光推送（仅支持安卓，iOS 建议走苹果推送通道）](https://docs.jiguang.cn/jpush/server/push/rest_api_v3_push)


---

## 环境需求

- PHP >= 8.0
- guzzlehttp/guzzle >= 6.0.0
- edamov/pushok >= 0.15.0

## 安装

```
composer require yunchuang/push
```


---

## 使用

```php
use BetterUs\Push\Push;

$iosCertContent =<<<EOF
-----BEGIN PRIVATE KEY-----
...
-----END PRIVATE KEY-----
EOF;

$config = [
    'huawei' => [
        'appPkgName' => '', // 包名
        'clientId' => '',
        'clientSecret' => ''
    ],
    'huawei-v2' => [
        'appPkgName' => '',
        'clientId' => '',
        'clientSecret' => ''
    ],
    'meizu' => [
        'appPkgName' => '',
        'appId' => '',
        'appSecret' => ''
    ],
    'xiaomi' => [
        'appPkgName' => '',
        'appSecret' => ''
    ],
    'oppo' => [
        'appPkgName' => '',
        'appKey' => '',
        'masterSecret' => ''
    ],
    'vivo' => [
        'appPkgName' => '',
        'appId' => '',
        'appKey' => '',
        'appSecret' => ''
    ],
    'ios' => [
        'isSandBox' => true, // 是否调试包
        'certPath' => '', // pem格式推送证书本地绝对路径
        'password' => '123', // 推送证书密码
    ],
    'ios-token' => [
        'isSandBox' => true,
        'teamId' => 'D4GSYVE6CN', // 开发者帐号teamId
        'keyId' => '99BYW4U4SZ', // token认证keyId
        'secretContent' => $iosCertContent, // 密钥内容，有值时忽略secretFile
        'secretFile' => 'xxx.p8', // token认证密钥文件本地绝对路径
        'bundleId' => 'com.mysoft.mdev' // 应用ID
    ],
    'jiguang' => [
        'appPkgName' => '',
        'appKey' => '',
        'masterSecret' => ''
    ]
];

$push = new Push($config);
$push->setPusher(通道名);
$push->pushNotice(设备token, 推送内容, 附加信息);

```

## 通道

目前支持以下通道：

- huawei-v2 华为新版(推荐)
- xiaomi 小米
- meizu 魅族
- oppo Oppo
- vivo Vivo
- ios-token 苹果(基于token认证，推荐)
- jiguang 极光

## 设备token

通过推送插件获得，支持以数组形式传入多个。

鉴于各厂商对多推的支持不一，建议单次最多100个设备。


## 推送内容

由于各厂商对推送的支持不一，现抽象定义了以下公有属性：

|参数|类型|说明
|:---:|:---:|:---:|
| businessId | string | 业务ID，相同业务ID只推送一次 |
| title | string | 标题 |
| subTitle | string | 副标题 |
| content | string | 内容 |
| badge | string | 角标 |
| extra | array | 服务端传给APP的自定义数据，建议转换为json字符串后长度不超过1024字节 |
| callback | string | 送达回执地址，供推送厂商调用，最大128个字节，具体请查阅各厂商文档。*华为仅支持在应用管理中心配置；魅族需在管理后台注册回执地址，每次推送时也需指定回执地址；苹果ios-token通道由SDK调用回执* |
| callbackParam | string | 自定义回执参数 |
| notifyId | string | 聚合标签，同标签消息在通知栏只显示一条。小米通道支持，字母、数字组合不超过8位 |
| gatewayOptions | array | 厂商扩展参数 |

### gatewayOptions厂商扩展参数说明
考虑到各厂商均有自己特有的参数，故提供此扩展参数来提供支持。如果扩展参数与通用参数有冲突，则取扩展参数中值。
参数类型为以厂商通道为键名的数组，格式如下：
```php
[
    "huawei": 与华为消息格式一致，下同,
    "xiaomi": xxx,
    "vivo": xxx,
    "oppo": xxx,
    "meizu": xxx,
    "ios-token": xxx
]
```

示例

```php
$message = [
    'businessId' => uniqid(),
    'title' => 'This is title',
    'content' => 'This is content',
    'extra' => [
        'key1' => 'v1',
        'key2' => 2
    ],
    'gatewayOptions' => [
        'xiaomi' => [
            'extra.notify_foreground' => '1',
        ],
        'huawei' => [
            'hps' => [
                'ext' => [
                    'badgeAddNum' => '1',
                    'badgeClass' => 'com.mysoft.core.activity.LauncherActivity',
                ]
            ]
        ]
    ],
];
```

## 附加信息

|参数|类型|说明
|:---:|:---:|:---:|
| token | string | 认证token |


## 标题等长度限制说明
> 当前库将根据以下厂商限制进行处理，请调用方根据实际情况设置标题等内容

| 厂商 | 标题 | 副标题 | 描述 | 回执地址 | 回执参数 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 华为 | 未说明 | - | 未说明 | - | - |
| 小米 | 未说明 | - | 未说明 | 未说明 | 64 |
| OPPO | 50 | 10 | 200 | 200 | 50|
| VIVO | 20 | - | 50 | 128 | 64 |
| 魅族 | 32 | - | 100 | 128 | 64 |
| 苹果 | 未说明 | 未说明 | 未说明 | - | - |


## 角标说明
目前仅华为新版、ios-token通道支持角标。其中华为新版支持角标累加，格式为`+1`，其他通道将只取数字值。

---

## 推送
```php

// 华为v2推送
$push->setPusher('huawei-v2');
print $push->pushNotice(
    '0864113036098917300002377300CN01',
    $message,
    ['token' => 'CFx88jTVr6adjsh6eVOLvhtqnDlhLxb7CljykbXxu7vLsnexatUJZM1lqXHPzfnurD0gknQnIu7SRvWhAPx/zQ==']
);


// 魅族推送
$push->setPusher('meizu');
print $push->pushNotice(
    ['ULY6c596e6a7d5b714a475a60527c6b5f7f655a6d6370'],
    $message
);

// 小米推送
$push->setPusher('xiaomi');
print $push->pushNotice(
    [
        'hncl+mMTtpA8BQZ66k7Fgpwa+ezlSL8AN/g8HKzTfg64GcTeTjY1C9bdrUcs2vR+',
        '0VcFXBPNTLifGLIYK+GdDAiOFJQ+uWAzkfs7QYtfszBgqFV720C8zli7mce1mHj6'
    ],
    $message
);

// Oppo推送
$push->setPusher('oppo');
$tokenInfo = $push->getAuthToken();
$options = [
    'token' => $tokenInfo['token']
];
print $push->pushNotice(
    'CN_40557c137ac2b5c68cbb8ac52616fefd',
    $message,
    $options
);

// Vivo推送
$push->setPusher('vivo');
print $push->pushNotice(
    [
        '15513410784181118114099',
    ],
    $message
);

// 苹果基于token推送(推荐)
$push->setPusher('ios-token');
print $push->pushNotice(
    [
        '7438f5ba512cba4dcd1613e530a960cb862bd1c7ca70eae3cfe73137583c3c0d',
        '720772a4df1938b14d2b732ee62ce4e157577f8453d6021f81156aaeca7032ae',
    ],
    $message
);

//极光推送
$push->setPusher('jiguang');
print $push->pushNotice(
    [
        '160a3797c912f272068',
    ],
    $message
);

```

## 认证

目前`华为`、`华为新版`、`Oppo`、`Vivo`、`ios-token`推送前需要获取先获取认证token，且对获取频次均有限制，故统一提供了获取token方法`getAuthToken`，建议缓存认证token，过期时间较返回的有效时间短，比如2小时。

此方法返回格式如下：

```php
[
    'token' => 认证token,
    'expires' => 有效时间，单位为秒
];

```

其余通道将返回`null`。

缓存的认证token请以[附加信息](#附加信息)传入。

## 推送角标

目前仅`ios-token` `华为新版`支持推送角标

其中`华为新版`支持角标累加，`badge`格式为`+ 5`


## 返回值

除苹果通道外，其余通道推送成功时均将返回推送任务ID。

调用过程中有可能抛出以下异常，请注意捕获。

- `BetterUs\Push\Exceptions\GatewayErrorException`
- `BetterUs\Push\Exceptions\InvalidArgumentException`
- `BetterUs\Push\Exceptions\ResponseException`

也可捕获上述异常的父类`BetterUs\Push\Exceptions\Exception`

## 自定义通道

本扩展支持自定义通道。

```php
use BetterUs\Push\Gateways\Gateway;

class MyGateway extends Gateway
{
    // ...
}

// 注册
$push->extend('custom', function ($config) {
    return new MyGateway($config);
}

// config中添加通道配置
$config['custom'] = [];

// 调用
$push->setPusher('custom');
print $push->pushNotice(
    '0864113036098917300002377300CN01',
    $message
);

```

## 各通道配置参照[$config](#使用)

---

## 各通道回执示例
> 建议判断如果是字符串，则进行json_decode

- ios-token
```
# 成功
{
    "businessId": "5cc55570a9faf",
    "deviceToken": "7438f5ba512cba4dcd1613e530a960cb862bd1c7ca70eae3cfe73137583c3c0d",
    "taskId": "3FC1E078-93CA-33BA-EC52-8E8A70AA0EB1",
    "status": "success"
}

# 失败
{
    "businessId": "5cc550a6d05ad",
    "deviceToken": "7438f5ba512cba4dcd1613e530a960cb862bd1c7ca70eae3cfe73137583c3c0d",
    "reason": "403 Forbidden for ExpiredProviderToken",
    "status": "fail"
}
```
- huawei
```
{
    "statuses": [
        {
            "timestamp": 1552459811754,
            "token": "0864113036098917300002377300CN01",
            "appid": "100405075",
            "biTag": "",
            "status": 0,
            "requestId": "155245981150032647501"
        }
    ]
}
```
- meizu
```
{
    "cb": "{\"NS20190313171303747_0_11579902_1_3-1\":{\"param\":\"\",\"type\":1,\"targets\":[\"S5Q4b726f7b466c797c584d54000503555c427160754b\"]}}",
    "access_token": "c68b05216e54409d95573912fad9c0de"
}
```
- xiaomi
```
{
    "data": "{\"scm527795524699919378t\":{\"barStatus\":\"Enable\",\"type\":1,\"targets\":\"0VcFXBPNTLifGLIYK+GdDAiOFJQ+uWAzkfs7QYtfszBgqFV720C8zli7mce1mHj6\",\"timestamp\":1552470320982},\"scm52278552470202304Mq\":{\"barStatus\":\"Enable\",\"type\":1,\"targets\":\"0VcFXBPNTLifGLIYK+GdDAiOFJQ+uWAzkfs7QYtfszBgqFV720C8zli7mce1mHj6\",\"timestamp\":1552470321182}}"
}
```
- vivo
```
{
    "555758542050050048": {
        "param": null,
        "targets": "15513410784181118114099"
    }
}
```
- oppo
```
[
    {
        "registrationIds": "CN_768799ad17f2b564707db038dabb14b6",
        "messageId": "5c89f5d30980ff58c6ff9914",
        "taskId": "0000",
        "eventType": "push_arrive",
        "appId": "3000604"
    }
]
```

---

## [推送限额说明](/docs/push_limit.md)

---

## [OPPO国际推送支持](/docs/oppo_international_push.md)

OPPO推送现已支持国际推送功能，可以自动根据设备的RegistrationID格式判断设备区域，并选择对应的API端点进行推送。

**主要特性：**
- 自动区域判断：根据RegistrationID格式自动识别国内/海外设备
- 智能端点选择：自动选择国内或海外API端点
- 混合设备支持：支持同时推送到国内和海外设备
- 向后兼容：现有代码无需修改

详细使用方法请参考：[OPPO国际推送文档](/docs/oppo_international_push.md)

---

## [华为推送v2接口优化](/docs/huawei_push_guide.md)

华为推送现已优化支持v2接口，提供按regId(token)推送和回调解析功能，支持完整的推送状态追踪。

**主要特性：**
- v2接口：使用华为推送v2接口，性能更优
- regId推送：支持单个和批量regId(token)推送
- 自动回调：自动设置回调地址为 `open.example.com/push/callback/huawei`
- 回调解析：提供完整的回调数据解析功能
- 状态追踪：支持送达、点击、无效token、已发送等状态追踪

详细使用方法请参考：[华为推送使用指南](/docs/huawei_push_guide.md)

---

## [iOS推送优化支持](/docs/ios_push_guide.md)

iOS推送现已优化支持最新的APNs协议，提供按regId/token推送和回调解析功能，支持完整的推送状态追踪。

**主要特性：**
- JWT token认证：基于最新的token认证方式，更安全可靠
- HTTP/2协议：使用最新的HTTP/2协议，性能更优
- regId推送：支持单个和批量设备token推送
- LiveKit支持：支持LiveCommunicationKit实时通信消息
- 回调解析：提供完整的回调数据解析功能
- 严格验证：严格验证设备token格式（64位十六进制）

详细使用方法请参考：[iOS推送使用指南](/docs/ios_push_guide.md)

---

## Oppo、Vivo推送服务开通答疑
![oppo](/docs/oppo_push.png)
![vivo](/docs/vivo_push.png)

---

## 注意
- 各厂商设备token长度不一致，目前识别出华为最长为130个字符
- 已将`gepo/apns-http2`替换为`edamov/pushok`，并移除了`firebase/php-jwt`依赖。新的pushok库内置JWT支持，性能更优，支持最新的APNs特性
- ios-token推送要求支持HTTP/2协议，另见 [参照](/docs/ios_token_http_2.md)
- [iOS两种推送形式比较](/docs/ios_push_compare.md)
- 对于非明源云移动应用，若有`extra`参数的使用场景，请与原生开发人员确认**Intent对应的schema**信息，具体代码为`src/Gateways/Gateway.php L110`
