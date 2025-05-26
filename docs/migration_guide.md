# 推送网关迁移指南

## 概述

为了提供更好的性能和功能，我们移除了一些旧的推送网关实现，并保留了更现代的版本。本文档将指导您完成迁移过程。

## 🔄 **已移除的网关**

### 1. iOS推送网关

| 旧网关 | 新网关 | 状态 |
|--------|--------|------|
| `IosGateway` | `IosTokenGateway` | ✅ 已移除 |
| `ApnsMessage` | 内置于pushok | ✅ 已移除 |

### 2. 华为推送网关

| 旧网关 | 新网关 | 状态 |
|--------|--------|------|
| `HuaweiGateway` | `HuaweiV2Gateway` | ✅ 已移除 |

## 📋 **迁移对照表**

### iOS推送迁移

#### 旧的实现（已移除）
```php
// 基于证书的推送（已不支持）
$config = [
    'ios' => [
        'isSandBox' => true,
        'certPath' => '/path/to/cert.pem',
        'password' => 'cert_password'
    ]
];

$push->setPusher('ios');
```

#### 新的实现（推荐）
```php
// 基于token的推送（推荐）
$config = [
    'ios-token' => [
        'isSandBox' => true,
        'teamId' => 'D4GSYVE6CN',
        'keyId' => '99BYW4U4SZ',
        'secretFile' => 'path/to/key.p8',
        'bundleId' => 'com.your.app'
    ]
];

$push->setPusher('ios-token');
```

### 华为推送迁移

#### 旧的实现（已移除）
```php
// v1接口（已不支持）
$config = [
    'huawei' => [
        'appId' => 'your_app_id',
        'appSecret' => 'your_app_secret',
        'appPkgName' => 'com.your.app'
    ]
];

$push->setPusher('huawei');
```

#### 新的实现（推荐）
```php
// v2接口（推荐）
$config = [
    'huawei-v2' => [
        'appId' => 'your_app_id',
        'appSecret' => 'your_app_secret',
        'appPkgName' => 'com.your.app'
    ]
];

$push->setPusher('huawei-v2');
```

## 🔧 **向后兼容性**

为了确保现有代码的兼容性，我们提供了自动映射功能：

### 自动网关映射

| 旧名称 | 自动映射到 | 说明 |
|--------|------------|------|
| `ios` | `ios-token` | 自动使用token推送 |
| `apple` | `ios-token` | 自动使用token推送 |
| `huawei` | `huawei-v2` | 自动使用v2接口 |

### 示例：无需修改代码

```php
// 这些旧的调用方式仍然可用
$push->setPusher('ios');        // 自动映射到 ios-token
$push->setPusher('apple');      // 自动映射到 ios-token  
$push->setPusher('huawei');     // 自动映射到 huawei-v2

// 但推荐使用新的名称
$push->setPusher('ios-token');  // 推荐
$push->setPusher('huawei-v2');  // 推荐
```

## 📈 **新功能和改进**

### iOS推送改进

1. **更现代的库**：使用`edamov/pushok`替代`gepo/apns-http2`
2. **内置JWT支持**：移除了`firebase/php-jwt`依赖
3. **LiveKit支持**：支持LiveCommunicationKit实时通信消息
4. **更好的错误处理**：改进的错误处理和响应解析
5. **HTTP/2优化**：更好的HTTP/2连接处理

### 华为推送改进

1. **v2接口**：使用华为推送v2接口，性能更优
2. **回调支持**：自动设置回调地址
3. **状态追踪**：支持送达、点击、无效token等状态追踪
4. **批量推送**：更好的批量推送支持

## 🚀 **迁移步骤**

### 步骤1：更新配置

#### iOS推送配置更新

```php
// 旧配置（不再支持）
$config = [
    'ios' => [
        'isSandBox' => true,
        'certPath' => '/path/to/cert.pem',
        'password' => 'cert_password'
    ]
];

// 新配置（推荐）
$config = [
    'ios-token' => [
        'isSandBox' => true,
        'teamId' => 'D4GSYVE6CN',
        'keyId' => '99BYW4U4SZ',
        'secretFile' => 'path/to/key.p8',
        'bundleId' => 'com.your.app'
    ]
];
```

#### 华为推送配置更新

```php
// 旧配置（自动映射）
$config = [
    'huawei' => [
        'appId' => 'your_app_id',
        'appSecret' => 'your_app_secret',
        'appPkgName' => 'com.your.app'
    ]
];

// 新配置（推荐）
$config = [
    'huawei-v2' => [
        'appId' => 'your_app_id',
        'appSecret' => 'your_app_secret',
        'appPkgName' => 'com.your.app'
    ]
];
```

### 步骤2：更新推送代码

```php
// 旧代码（仍可用，但不推荐）
$push->setPusher('ios');
$push->setPusher('huawei');

// 新代码（推荐）
$push->setPusher('ios-token');
$push->setPusher('huawei-v2');
```

### 步骤3：测试验证

```php
// 测试iOS推送
try {
    $push->setPusher('ios-token');
    $result = $push->pushNotice($deviceTokens, $message);
    echo "iOS推送成功\n";
} catch (Exception $e) {
    echo "iOS推送失败: " . $e->getMessage() . "\n";
}

// 测试华为推送
try {
    $push->setPusher('huawei-v2');
    $result = $push->pushNotice($tokens, $message);
    echo "华为推送成功\n";
} catch (Exception $e) {
    echo "华为推送失败: " . $e->getMessage() . "\n";
}
```

## ⚠️ **注意事项**

### iOS推送注意事项

1. **证书推送已移除**：不再支持基于证书的推送方式
2. **需要.p8密钥文件**：必须使用苹果开发者账号生成的.p8密钥文件
3. **配置参数变化**：需要提供teamId、keyId、bundleId等参数

### 华为推送注意事项

1. **v1接口已移除**：不再支持华为推送v1接口
2. **配置保持兼容**：配置参数保持不变，可无缝迁移
3. **回调地址变化**：新的回调地址格式

## 🔍 **故障排除**

### 常见问题

#### 1. iOS推送失败

**问题**：`Class 'BetterUs\Push\Gateways\IosGateway' not found`

**解决方案**：
```php
// 错误的方式
$push->setPusher('ios'); // 如果配置中没有ios-token

// 正确的方式
$config['ios-token'] = [...]; // 确保配置了ios-token
$push->setPusher('ios-token');
```

#### 2. 华为推送失败

**问题**：`Class 'BetterUs\Push\Gateways\HuaweiGateway' not found`

**解决方案**：
```php
// 错误的方式
$push->setPusher('huawei'); // 如果配置中没有huawei-v2

// 正确的方式  
$config['huawei-v2'] = [...]; // 确保配置了huawei-v2
$push->setPusher('huawei-v2');
```

#### 3. 依赖问题

**问题**：缺少pushok依赖

**解决方案**：
```bash
composer require edamov/pushok
composer remove gepo/apns-http2 firebase/php-jwt
```

## 📚 **相关文档**

- [iOS推送使用指南](/docs/ios_push_guide.md)
- [华为推送使用指南](/docs/huawei_push_guide.md)
- [推送限额说明](/docs/push_limit.md)

## 🎯 **迁移检查清单**

- [ ] 更新iOS推送配置为ios-token
- [ ] 获取苹果.p8密钥文件
- [ ] 更新华为推送配置为huawei-v2
- [ ] 测试所有推送功能
- [ ] 更新回调处理逻辑
- [ ] 验证错误处理
- [ ] 更新文档和注释

## 📞 **技术支持**

如果在迁移过程中遇到问题，请：

1. 查看相关文档
2. 检查配置参数
3. 验证依赖安装
4. 测试网络连接

迁移完成后，您将享受到更好的性能、更丰富的功能和更稳定的推送服务。
