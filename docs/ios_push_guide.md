# iOS推送使用指南

## 概述

iOS推送现已优化支持最新的APNs协议，提供按regId/token推送和回调解析功能，支持完整的推送状态追踪。

## 功能特性

- **双重认证方式**：支持JWT token认证和推送证书认证
- **HTTP/2协议**：使用最新的HTTP/2协议，性能更优
- **regId推送**：支持单个和批量设备token推送
- **LiveKit支持**：支持LiveCommunicationKit实时通信消息
- **回调解析**：提供完整的回调数据解析功能
- **状态追踪**：支持送达、失败等状态追踪
- **严格验证**：严格验证设备token格式
- **环境支持**：支持沙盒和生产环境

## API端点

| 环境 | API端点 | 用途 |
|------|---------|------|
| 生产环境 | `https://api.push.apple.com/3/device/` | 推送到生产环境设备 |
| 沙盒环境 | `https://api.sandbox.push.apple.com/3/device/` | 推送到开发/测试设备 |

## 配置参数

### 1. JWT Token认证（推荐）

```php
$config = [
    'ios-token' => [
        'isSandBox' => true,                    // 是否沙盒环境
        'teamId' => 'D4GSYVE6CN',              // 团队ID
        'keyId' => '99BYW4U4SZ',               // 密钥ID
        'secretFile' => 'path/to/key.p8',      // .p8密钥文件路径
        'bundleId' => 'com.your.app'           // 应用Bundle ID
    ]
];
```

### 2. 推送证书认证

```php
$config = [
    'ios' => [
        'isSandBox' => true,                    // 是否沙盒环境
        'certPath' => 'path/to/cert.pem',      // .pem证书文件路径
        'password' => 'cert_password'          // 证书密码
    ]
];
```

## 基本使用

### 1. 单设备推送

```php
use BetterUs\Push\Push;

$config = [
    'ios-token' => [
        'isSandBox' => true,
        'teamId' => 'D4GSYVE6CN',
        'keyId' => '99BYW4U4SZ',
        'secretFile' => 'path/to/key.p8',
        'bundleId' => 'com.your.app'
    ]
];

$push = new Push($config);
$push->setPusher('ios-token');

// 推送到单个设备
$deviceToken = 'a00915e74d60d71ba3fb80252a5e197b60f2e7743f61b4411c713e9aabd2854f';
$message = [
    'title' => '推送标题',
    'content' => '推送内容',
    'subTitle' => '副标题',
    'badge' => 1
];

$result = $push->pushNotice($deviceToken, $message);
```

### 2. 批量设备推送

```php
// 推送到多个设备（最多100个）
$deviceTokens = [
    'a00915e74d60d71ba3fb80252a5e197b60f2e7743f61b4411c713e9aabd2854f',
    'b11026e85e71e82cb4fc91363a6f208c71f3e8844f72c5522c824f0fbce3965e'
];

$message = [
    'title' => '批量推送标题',
    'content' => '批量推送内容'
];

$result = $push->pushNotice($deviceTokens, $message);
```

### 3. 高级消息配置

```php
$message = [
    'title' => '推送标题',
    'content' => '推送内容',
    'subTitle' => '副标题',
    'badge' => 1,
    'extra' => [
        'custom_key' => 'custom_value',
        'action_data' => ['id' => 123]
    ],
    'gatewayOptions' => [
        'ios' => [
            'aps' => [
                'sound' => 'custom_sound.wav',
                'category' => 'MESSAGE_CATEGORY',
                'thread-id' => 'thread-123',
                'mutable-content' => 1
            ]
        ]
    ]
];

$result = $push->pushNotice($deviceTokens, $message);
```

## LiveKit消息支持

### 1. LiveKit消息推送

```php
// 方式1：通过extra字段自动检测
$liveKitMessage = [
    'title' => 'Incoming Call',
    'content' => 'John is calling you',
    'extra' => [
        'room_name' => 'room_12345',
        'caller_id' => 'user_john',
        'call_type' => 'video',
        'livekit_server_url' => 'wss://livekit.example.com',
        'livekit_access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
    ]
];

$result = $push->pushNotice($deviceTokens, $liveKitMessage);
```

### 2. 通过网关选项指定

```php
// 方式2：通过gatewayOptions明确指定
$liveKitMessage = [
    'title' => 'Video Call',
    'content' => 'Incoming video call',
    'gatewayOptions' => [
        'ios' => [
            'livekit' => true
        ]
    ]
];

$result = $push->pushNotice($deviceTokens, $liveKitMessage);
```

### 3. LiveKit消息特性

- **VoIP推送类型**：自动使用`voip`推送类型
- **高优先级**：使用最高优先级（priority=10）
- **特殊Topic**：自动添加`.voip`后缀到Bundle ID
- **专用Payload**：构建LiveKit专用的payload格式

### 4. LiveKit检测规则

系统会自动检测以下情况并启用LiveKit模式：

1. **extra字段包含**：
   - `livekit`
   - `call_type`
   - `room_name`
   - `caller_id`

2. **gatewayOptions设置**：
   - `gatewayOptions.ios.livekit = true`

### 5. LiveKit Payload格式

```json
{
  "aps": {
    "content-available": 1,
    "alert": {
      "title": "Incoming Call",
      "body": "You have an incoming call"
    }
  },
  "livekit": {
    "room_name": "room_12345",
    "caller_id": "user_john",
    "call_type": "video",
    "timestamp": 1640995200,
    "server_url": "wss://livekit.example.com",
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

### 6. 自定义LiveKit参数

使用`livekit_`前缀的extra字段会自动添加到livekit对象中：

```php
$message = [
    'title' => 'Conference Call',
    'content' => 'Join the meeting',
    'extra' => [
        'room_name' => 'meeting_room',
        'caller_id' => 'admin',
        'livekit_server_url' => 'wss://livekit.example.com',
        'livekit_participant_name' => 'John Doe',
        'livekit_metadata' => '{"role":"moderator"}'
    ]
];
```

## 回调处理

### 1. 回调数据解析

```php
use BetterUs\Push\Gateways\IosTokenGateway;

// 创建网关实例
$gateway = new IosTokenGateway($config['ios-token']);

// 接收回调数据（通常来自HTTP POST请求）
$callbackData = json_decode(file_get_contents('php://input'), true);

// 解析回调数据
$parsed = $gateway->parseCallback($callbackData);

// 处理不同类型的回调
switch ($parsed['event_type']) {
    case 'delivered':
        // 消息送达处理
        echo "消息 {$parsed['message_id']} 已送达到设备 {$parsed['device_token']}";
        break;

    case 'failed':
        // 推送失败处理
        echo "消息推送失败: {$parsed['error_reason']}";
        break;

    default:
        echo "未知事件类型: {$parsed['event_type']}";
        break;
}
```

### 2. 回调数据结构

解析后的回调数据包含以下字段：

```php
[
    'gateway' => 'ios-token',                 // 推送网关
    'device_token' => 'a00915e74d...',        // 设备token
    'message_id' => 'apns-id-12345',          // 消息ID
    'event_type' => 'delivered',              // 事件类型
    'timestamp' => 1640995200,                // 时间戳
    'error_reason' => 'InvalidDeviceToken',   // 错误原因（失败时）
    'raw_data' => [...]                       // 原始回调数据
]
```

## 事件类型说明

| 事件类型 | 说明 |
|----------|------|
| delivered | 消息送达成功 |
| failed | 推送失败 |

## 常见错误处理

### 1. 设备Token验证

```php
// 系统会自动验证设备token格式
// 有效token：64位十六进制字符串
$validToken = 'a00915e74d60d71ba3fb80252a5e197b60f2e7743f61b4411c713e9aabd2854f';

// 无效token会被自动过滤
$invalidTokens = [
    'invalid_token',                    // 格式错误
    'a00915e74d60d71ba3fb80252a5e197',  // 长度不足
    'g00915e74d60d71ba3fb80252a5e197b60f2e7743f61b4411c713e9aabd2854f' // 包含非十六进制字符
];
```

### 2. 错误处理

```php
try {
    $result = $push->pushNotice($deviceTokens, $message);
    echo "推送成功";
} catch (\BetterUs\Push\Exceptions\GatewayErrorException $e) {
    echo "推送失败: " . $e->getMessage();
    // 解析具体的失败设备
    $errors = json_decode($e->getMessage(), true);
    foreach ($errors as $token => $error) {
        echo "设备 {$token} 推送失败: {$error}";
    }
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage();
}
```

## 环境要求

### 1. 系统要求

- **PHP版本**: >= 7.0
- **cURL版本**: >= 7.54.0 (支持HTTP/2)
- **OpenSSL版本**: >= 1.0.2s
- **扩展要求**: curl, openssl, json

### 2. 苹果开发者要求

- 有效的苹果开发者账号
- 已配置的App ID和推送证书/密钥
- 正确的Bundle ID配置

## 推送限制

1. **设备限制**：单次推送最多支持100个设备token
2. **Token格式**：必须是64位十六进制字符串
3. **JWT有效期**：JWT token最长有效期1小时
4. **证书有效期**：推送证书最长有效期1年
5. **消息大小**：payload最大4KB

## 最佳实践

### 1. 认证方式选择

- **推荐使用JWT token认证**：更安全，无需定期更新证书
- 证书认证适用于旧版本兼容

### 2. 错误处理

```php
// 建议实现重试机制
$maxRetries = 3;
$retryCount = 0;

while ($retryCount < $maxRetries) {
    try {
        $result = $push->pushNotice($deviceTokens, $message);
        break; // 成功则跳出循环
    } catch (Exception $e) {
        $retryCount++;
        if ($retryCount >= $maxRetries) {
            throw $e; // 达到最大重试次数，抛出异常
        }
        sleep(1); // 等待1秒后重试
    }
}
```

### 3. 性能优化

```php
// 批量推送时分批处理
$batchSize = 100;
$deviceChunks = array_chunk($deviceTokens, $batchSize);

foreach ($deviceChunks as $chunk) {
    $push->pushNotice($chunk, $message);
}
```

## 完整示例

```php
<?php

use BetterUs\Push\Push;
use BetterUs\Push\Gateways\IosTokenGateway;

// 配置
$config = [
    'ios-token' => [
        'isSandBox' => true,
        'teamId' => 'D4GSYVE6CN',
        'keyId' => '99BYW4U4SZ',
        'secretFile' => 'path/to/key.p8',
        'bundleId' => 'com.your.app'
    ]
];

// 推送消息
$push = new Push($config);
$push->setPusher('ios-token');

$deviceTokens = [
    'a00915e74d60d71ba3fb80252a5e197b60f2e7743f61b4411c713e9aabd2854f'
];

$message = [
    'title' => '重要通知',
    'content' => '您有新的消息，请查看',
    'badge' => 1,
    'extra' => ['order_id' => 123]
];

try {
    $push->pushNotice($deviceTokens, $message);
    echo "推送成功\n";
} catch (Exception $e) {
    echo "推送失败: " . $e->getMessage() . "\n";
}

// 处理回调（在回调接口中）
$gateway = new IosTokenGateway($config['ios-token']);
$callbackData = json_decode(file_get_contents('php://input'), true);
$parsed = $gateway->parseCallback($callbackData);

// 记录回调日志
error_log("iOS推送回调: " . json_encode($parsed));

// 响应回调
http_response_code(200);
echo "OK";
```

## 更新日志

- **v1.x.x**：优化iOS推送功能
  - 添加回调数据解析功能
  - 优化设备token验证逻辑
  - 改进错误处理机制
  - 完善文档和示例
