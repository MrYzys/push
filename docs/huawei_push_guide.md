# 华为推送使用指南

## 概述

华为推送现已优化支持v2接口，提供按regId(token)推送和回调解析功能，支持完整的推送状态追踪。

## 功能特性

- **v2接口**：使用华为推送v2接口，性能更优
- **regId推送**：支持单个和批量regId(token)推送
- **自动回调**：自动设置回调地址，支持多种事件回执
- **回调解析**：提供完整的回调数据解析功能
- **状态追踪**：支持送达、点击、无效token、已发送等状态追踪
- **向后兼容**：保持现有API的兼容性

## API端点

| 功能 | API端点 | 用途 |
|------|---------|------|
| 认证 | `https://oauth-login.cloud.huawei.com/oauth2/v2/token` | 获取访问令牌 |
| 推送 | `https://push-api.cloud.huawei.com/v1/[appid]/messages:send` | 按token推送消息 |

## 配置参数

```php
$config = [
    'huawei-v2' => [
        'appPkgName' => 'com.your.app',      // 应用包名
        'clientId' => 'your_client_id',      // 客户端ID
        'clientSecret' => 'your_client_secret' // 客户端密钥
    ]
];
```

## 基本使用

### 1. 单设备推送

```php
use BetterUs\Push\Push;

$config = [
    'huawei-v2' => [
        'appPkgName' => 'com.your.app',
        'clientId' => 'your_client_id',
        'clientSecret' => 'your_client_secret'
    ]
];

$push = new Push($config);
$push->setPusher('huawei-v2');

// 推送到单个设备
$token = 'target_device_token';
$message = [
    'title' => '推送标题',
    'content' => '推送内容'
];

$result = $push->pushNotice($token, $message);
echo "请求ID: " . $result;
```

### 2. 批量设备推送

```php
// 推送到多个设备（最多1000个）
$tokens = [
    'token_1',
    'token_2',
    'token_3'
];

$message = [
    'title' => '批量推送标题',
    'content' => '批量推送内容'
];

$result = $push->pushNotice($tokens, $message);
echo "请求ID: " . $result;
```

### 3. 自定义回调地址

```php
$message = [
    'title' => '推送标题',
    'content' => '推送内容',
    'callback' => 'https://your-domain.com/push/callback',
    'callbackParam' => 'custom_parameter'
];

$result = $push->pushNotice($token, $message);
```

## 回调处理

### 1. 默认回调地址

系统会自动设置回调地址为：`https://open.example.com/push/callback/huawei`

### 2. 回调数据解析

```php
use BetterUs\Push\Gateways\HuaweiV2Gateway;

// 创建网关实例
$gateway = new HuaweiV2Gateway($config['huawei-v2']);

// 接收回调数据（通常来自HTTP POST请求）
$callbackData = json_decode(file_get_contents('php://input'), true);

// 解析回调数据
$parsed = $gateway->parseCallback($callbackData);

// 处理不同类型的回调
switch ($parsed['event_type']) {
    case 'delivered':
        // 消息送达处理
        echo "消息 {$parsed['message_id']} 已送达到 " . count($parsed['registration_ids']) . " 个设备";
        break;
        
    case 'clicked':
        // 消息点击处理
        echo "消息 {$parsed['message_id']} 被点击";
        break;
        
    case 'invalid_token':
        // 无效token处理
        echo "发现无效的token: " . implode(', ', $parsed['registration_ids']);
        break;
        
    case 'sent':
        // 消息已发送处理
        echo "消息 {$parsed['message_id']} 已发送";
        break;
        
    default:
        echo "未知事件类型: {$parsed['event_type']}";
        break;
}
```

### 3. 回调数据结构

解析后的回调数据包含以下字段：

```php
[
    'gateway' => 'huawei-v2',                 // 推送网关
    'message_id' => 'req_12345',              // 请求ID
    'registration_ids' => ['token1', 'token2'], // 相关设备token列表
    'event_type' => 'delivered',              // 事件类型
    'timestamp' => 1640995200,                // 时间戳
    'raw_data' => [...]                       // 原始回调数据
]
```

## 回调类型说明

| 类型值 | 事件类型 | 说明 |
|--------|----------|------|
| 1 | delivered | 消息送达 |
| 2 | clicked | 消息点击 |
| 3 | invalid_token | 无效token |
| 10 | sent | 消息已发送 |

## 高级功能

### 1. 消息参数设置

```php
$message = [
    'title' => '推送标题',
    'content' => '推送内容',
    'notifyId' => 'notify_123',               // 通知ID
    'businessId' => 'business_id_456',        // 业务ID
    'badge' => '1',                           // 角标数字
    'extra' => [
        'custom_key' => 'custom_value'        // 自定义参数
    ]
];
```

### 2. 网关选项

```php
$message = [
    'title' => '推送标题',
    'content' => '推送内容',
    'gatewayOptions' => [
        'notification' => [
            'image' => 'https://example.com/image.png',
            'sound' => 'default',
            'vibrate_config' => ['1000', '500', '1000']
        ]
    ]
];
```

### 3. 使用缓存的认证Token

```php
// 获取认证token（建议缓存）
$tokenInfo = $push->getAuthToken();
// 返回：['token' => 'access_token', 'expires' => 3600]

// 使用缓存的token推送
$options = ['token' => $tokenInfo['token']];
$result = $push->pushNotice($tokens, $message, $options);
```

## 错误处理

```php
try {
    $result = $push->pushNotice($tokens, $message);
    echo "推送成功，请求ID: " . $result;
} catch (\BetterUs\Push\Exceptions\GatewayErrorException $e) {
    echo "推送失败: " . $e->getMessage();
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage();
}
```

## 注意事项

1. **设备限制**：单次推送最多支持1000个token
2. **流控限制**：默认3000QPS，单设备每天最多10万条
3. **回调验证**：建议验证回调来源的合法性
4. **错误重试**：对于推送失败的情况，建议实现重试机制
5. **日志记录**：建议记录推送和回调的详细日志
6. **性能优化**：大量推送时建议分批处理和缓存认证token

## 完整示例

```php
<?php

use BetterUs\Push\Push;
use BetterUs\Push\Gateways\HuaweiV2Gateway;

// 配置
$config = [
    'huawei-v2' => [
        'appPkgName' => 'com.your.app',
        'clientId' => 'your_client_id',
        'clientSecret' => 'your_client_secret'
    ]
];

// 推送消息
$push = new Push($config);
$push->setPusher('huawei-v2');

$tokens = ['token1', 'token2', 'token3'];
$message = [
    'title' => '重要通知',
    'content' => '您有新的消息，请查看',
    'callback' => 'https://your-domain.com/huawei/callback',
    'callbackParam' => 'order_id_123'
];

try {
    $requestId = $push->pushNotice($tokens, $message);
    echo "推送成功，请求ID: {$requestId}\n";
} catch (Exception $e) {
    echo "推送失败: " . $e->getMessage() . "\n";
}

// 处理回调（在回调接口中）
$gateway = new HuaweiV2Gateway($config['huawei-v2']);
$callbackData = json_decode(file_get_contents('php://input'), true);
$parsed = $gateway->parseCallback($callbackData);

// 记录回调日志
error_log("华为推送回调: " . json_encode($parsed));

// 响应回调
http_response_code(200);
echo "OK";
```

## 更新日志

- **v1.x.x**：优化华为推送功能
  - 使用v2接口提升性能
  - 更新回调地址设置逻辑
  - 添加回调数据解析功能
  - 优化regId(token)推送支持
  - 完善错误处理机制
