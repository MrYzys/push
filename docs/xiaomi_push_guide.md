# 小米推送使用指南

## 概述

小米推送现已优化支持按regId推送和回调解析功能，提供完整的推送状态追踪能力。

## 功能特性

- **regId推送**：支持单个和批量regId推送
- **自动回调**：自动设置回调地址，支持送达和点击回执
- **回调解析**：提供完整的回调数据解析功能
- **状态追踪**：支持送达、点击、无效目标等状态追踪
- **向后兼容**：保持现有API的兼容性

## API端点

| 功能 | API端点 | 用途 |
|------|---------|------|
| regId推送 | `https://api.xmpush.xiaomi.com/v3/message/regid` | 按regId推送消息 |

## 配置参数

```php
$config = [
    'xiaomi' => [
        'appSecret' => 'your_app_secret',    // 应用密钥
        'appPkgName' => 'com.your.app'       // 应用包名
    ]
];
```

## 基本使用

### 1. 单设备推送

```php
use BetterUs\Push\Push;

$config = [
    'xiaomi' => [
        'appSecret' => 'your_app_secret',
        'appPkgName' => 'com.your.app'
    ]
];

$push = new Push($config);
$push->setPusher('xiaomi');

// 推送到单个设备
$regId = 'target_registration_id';
$message = [
    'title' => '推送标题',
    'content' => '推送内容'
];

$result = $push->pushNotice($regId, $message);
echo "消息ID: " . $result;
```

### 2. 批量设备推送

```php
// 推送到多个设备（最多100个）
$regIds = [
    'regid_1',
    'regid_2',
    'regid_3'
];

$message = [
    'title' => '批量推送标题',
    'content' => '批量推送内容'
];

$result = $push->pushNotice($regIds, $message);
echo "消息ID: " . $result;
```

### 3. 自定义回调地址

```php
$message = [
    'title' => '推送标题',
    'content' => '推送内容',
    'callback' => 'https://your-domain.com/push/callback',
    'callbackParam' => 'custom_parameter'
];

$result = $push->pushNotice($regId, $message);
```

## 回调处理

### 1. 默认回调地址

系统会自动设置回调地址为：`https://open.example.com/push/callback/xiaomi`

### 2. 回调数据解析

```php
use BetterUs\Push\Gateways\XiaomiGateway;

// 创建网关实例
$gateway = new XiaomiGateway($config['xiaomi']);

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
        
    case 'invalid_target':
        // 无效目标处理
        echo "发现无效的regId: " . implode(', ', $parsed['registration_ids']);
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
    'gateway' => 'xiaomi',                    // 推送网关
    'message_id' => 'msg_12345',              // 消息ID
    'registration_ids' => ['regid1', 'regid2'], // 相关设备ID列表
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
| 3 | delivered + clicked | 送达和点击回执（设置时使用） |
| 16 | invalid_target | 无效目标设备 |

## 高级功能

### 1. 消息参数设置

```php
$message = [
    'title' => '推送标题',
    'content' => '推送内容',
    'notifyId' => 'job_key_123',              // 消息批次标识
    'businessId' => 'business_id_456',        // 业务ID
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
        'extra.sound_uri' => 'android.resource://com.your.app/raw/notification',
        'extra.notify_foreground' => '1',
        'time_to_live' => 86400000            // 消息有效期（毫秒）
    ]
];
```

## 错误处理

```php
try {
    $result = $push->pushNotice($regIds, $message);
    echo "推送成功，消息ID: " . $result;
} catch (\BetterUs\Push\Exceptions\GatewayErrorException $e) {
    echo "推送失败: " . $e->getMessage();
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage();
}
```

## 注意事项

1. **设备限制**：单次推送最多支持100个regId
2. **回调验证**：建议验证回调来源的合法性
3. **错误重试**：对于推送失败的情况，建议实现重试机制
4. **日志记录**：建议记录推送和回调的详细日志
5. **性能优化**：大量推送时建议分批处理

## 完整示例

```php
<?php

use BetterUs\Push\Push;
use BetterUs\Push\Gateways\XiaomiGateway;

// 配置
$config = [
    'xiaomi' => [
        'appSecret' => 'your_app_secret',
        'appPkgName' => 'com.your.app'
    ]
];

// 推送消息
$push = new Push($config);
$push->setPusher('xiaomi');

$regIds = ['regid1', 'regid2', 'regid3'];
$message = [
    'title' => '重要通知',
    'content' => '您有新的消息，请查看',
    'callback' => 'https://your-domain.com/xiaomi/callback',
    'callbackParam' => 'order_id_123'
];

try {
    $messageId = $push->pushNotice($regIds, $message);
    echo "推送成功，消息ID: {$messageId}\n";
} catch (Exception $e) {
    echo "推送失败: " . $e->getMessage() . "\n";
}

// 处理回调（在回调接口中）
$gateway = new XiaomiGateway($config['xiaomi']);
$callbackData = json_decode(file_get_contents('php://input'), true);
$parsed = $gateway->parseCallback($callbackData);

// 记录回调日志
error_log("小米推送回调: " . json_encode($parsed));

// 响应回调
http_response_code(200);
echo "OK";
```

## 更新日志

- **v1.x.x**：优化小米推送功能
  - 更新回调地址设置逻辑
  - 添加回调数据解析功能
  - 优化regId推送支持
  - 完善错误处理机制
