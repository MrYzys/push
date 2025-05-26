# OPPO国际推送支持

## 概述

OPPO推送现已支持国际推送功能，可以自动根据设备的RegistrationID格式判断设备区域，并选择对应的API端点进行推送。

## 功能特性

- **自动区域判断**：根据RegistrationID格式自动识别国内/海外设备
- **智能端点选择**：自动选择国内或海外API端点
- **混合设备支持**：支持同时推送到国内和海外设备
- **向后兼容**：现有代码无需修改，自动兼容
- **透明处理**：开发者无需关心底层实现细节

## API端点

| 环境 | API端点 | 用途 |
|------|---------|------|
| 国内 | `https://api.push.oppomobile.com/server/v1` | 推送到国内设备 |
| 海外 | `https://api-intl.push.oppomobile.com/server/v1` | 推送到海外设备 |

## 设备区域判断规则

根据OPPO官方文档，RegistrationID使用"_"符号分隔，判断规则如下：

### 国内设备
- **单段格式**：`b6bbd94b59cdb5df8391642c1509b7fe`
- **CN前缀**：`CN_b6bbd94b59cdb5df8391642c1509b7fe`
- **OPPO_CN前缀**：`OPPO_CN_b6bbd94b59cdb5df8391642c1509b7fe`

### 海外设备
- **非CN前缀**：`US_b6bbd94b59cdb5df8391642c1509b7fe`
- **OPPO_非CN前缀**：`OPPO_US_b6bbd94b59cdb5df8391642c1509b7fe`

## 使用方法

### 基本使用

```php
use BetterUs\Push\Push;

$config = [
    'oppo' => [
        'appKey' => 'your_app_key',
        'masterSecret' => 'your_master_secret',
        'appPkgName' => 'com.your.app'
    ]
];

$push = new Push($config);
$push->setPusher('oppo');

// 单设备推送 - 自动判断区域
$domesticToken = 'b6bbd94b59cdb5df8391642c1509b7fe';  // 国内设备
$internationalToken = 'US_b6bbd94b59cdb5df8391642c1509b7fe';  // 海外设备

$message = [
    'title' => '推送标题',
    'content' => '推送内容'
];

// 推送到国内设备（自动使用国内API）
$result1 = $push->pushNotice($domesticToken, $message);

// 推送到海外设备（自动使用海外API）
$result2 = $push->pushNotice($internationalToken, $message);
```

### 混合设备推送

```php
// 混合设备列表推送
$mixedTokens = [
    'b6bbd94b59cdb5df8391642c1509b7fe',           // 国内
    'CN_b6bbd94b59cdb5df8391642c1509b7fe',        // 国内
    'US_b6bbd94b59cdb5df8391642c1509b7fe',        // 海外
    'OPPO_UK_b6bbd94b59cdb5df8391642c1509b7fe',   // 海外
];

// 自动分组推送
$result = $push->pushNotice($mixedTokens, $message);

// 返回结果格式：
// [
//     'domestic' => 'message_id_for_domestic_devices',
//     'international' => 'message_id_for_international_devices'
// ]
```

### 使用缓存的认证Token

```php
// 获取认证token（建议缓存）
$tokenInfo = $push->getAuthToken();
// 返回：['token' => 'auth_token', 'expires' => 86400]

// 使用缓存的token推送
$options = ['token' => $tokenInfo['token']];
$result = $push->pushNotice($tokens, $message, $options);
```

## 实现细节

### 自动分组逻辑

当推送到多个设备时，系统会：

1. **设备分类**：根据RegistrationID格式将设备分为国内和海外两组
2. **分别推送**：对每组设备使用对应的API端点进行推送
3. **结果合并**：返回包含两个区域推送结果的数组

### 认证Token处理

- 国内和海外API需要分别获取认证token
- 系统会根据推送目标自动获取对应的token
- 支持传入预缓存的token以提高性能

### 向后兼容性

- 现有代码无需修改
- 单设备推送返回格式保持不变
- 多设备推送到同一区域时返回格式保持不变
- 只有混合区域推送时返回格式为数组

## 注意事项

1. **认证限制**：国内和海外API的认证token是独立的，需要分别管理
2. **推送限额**：国内和海外的推送限额可能不同，请参考OPPO官方文档
3. **网络环境**：确保服务器可以访问两个API端点
4. **错误处理**：混合推送时，部分区域失败不会影响其他区域的推送

## 错误处理

```php
try {
    $result = $push->pushNotice($tokens, $message);
} catch (\BetterUs\Push\Exceptions\GatewayErrorException $e) {
    // 处理推送错误
    echo "推送失败: " . $e->getMessage();
}
```

## 更新日志

- **v1.x.x**：新增OPPO国际推送支持
  - 添加海外API端点常量
  - 实现设备区域自动判断
  - 支持混合设备列表推送
  - 保持向后兼容性
