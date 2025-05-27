<?php

namespace BetterUs\Push\Gateways;


use Pushok\AuthProvider\Token;
use Pushok\Client;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert;
use BetterUs\Push\AbstractMessage;
use BetterUs\Push\Exceptions\InvalidArgumentException;
use BetterUs\Push\Exceptions\GatewayErrorException;
use BetterUs\Push\Support\ArrayHelper;
use Pushok\Response;

class IosTokenGateway extends Gateway
{
    // https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/establishing_a_token-based_connection_to_apns

    const ALGORITHM = 'ES256';

    const GATEWAY_NAME = 'ios-token';

    protected $maxTokens = 100;

    public function getAuthToken()
    {
        // pushok内置JWT支持，不需要手动生成token
        return null;
    }

    /**
     * 创建pushok认证提供者
     *
     * @return Token
     */
    protected function createAuthProvider()
    {
        $secretContent = $this->config->get('secretContent');
        if (!$secretContent) {
            $secretFile = $this->config->get('secretFile');
            if (!file_exists($secretFile)) {
                throw new InvalidArgumentException('无效的推送密钥证书地址 > ' . $secretFile);
            }
            $secretContent = file_get_contents($secretFile);
        }

        return Token::create([
            'key_id' => $this->config->get('keyId'),
            'team_id' => $this->config->get('teamId'),
            'app_bundle_id' => $this->config->get('bundleId'),
            'private_key_content' => $secretContent,
        ]);
    }

    public function pushNotice($to, AbstractMessage $message, array $options = [])
    {
        $to = $this->formatTo($to);
        if (!$to) {
            throw new InvalidArgumentException('无有效的设备token');
        }

        try {
            // 创建认证提供者
            $authProvider = $this->createAuthProvider();

            // 创建客户端
            $client = new Client($authProvider, !($message->gatewayOptions[self::GATEWAY_NAME]['isSandBox'] ?? !$this->config->get('isSandBox', false)));

            // 创建通知
            foreach ($to as $deviceToken) {
                $notification = $this->createNotification($deviceToken, $message);
                $client->addNotification($notification);
            }

            // 发送推送
            $responses = $client->push();

            // 处理回调
            $this->handlePushResponses($responses, $message);

        } catch (\Exception $e) {
            throw new GatewayErrorException('iOS推送失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取APNs Topic
     *
     * @param AbstractMessage $message
     * @return string
     */
    protected function getApnsTopic(AbstractMessage $message)
    {
        // 检查是否为LiveKit消息
        if ($this->isLiveKitMessage($message)) {
            return $this->config->get('bundleId') . '.voip';
        }

        return $this->config->get('bundleId');
    }

    /**
     * 创建pushok通知对象
     *
     * @param string $deviceToken
     * @param AbstractMessage $message
     * @return Notification
     */
    protected function createNotification($deviceToken, AbstractMessage $message)
    {
        // 创建payload
        $payload = $this->createPushokPayload($message);

        $payload->setPushType('alert');

        // 创建通知
        $notification = new Notification($payload, $deviceToken);

        // 设置优先级
        if ($this->isLiveKitMessage($message)) {
            $notification->setHighPriority();
        } else {
            $notification->setLowPriority();
        }

        // 设置其他属性
        if ($message->businessId) {
            $notification->setId($message->businessId);
        }

        if ($message->notifyId) {
            $notification->setCollapseId($message->notifyId);
        }

        return $notification;
    }

    /**
     * 处理推送响应
     *
     * @param array $responses
     * @param AbstractMessage $message
     */
    protected function handlePushResponses($responses, AbstractMessage $message)
    {
        $errors = [];

        foreach ($responses as $response) {
            if (Response::APNS_SUCCESS != $response->getStatusCode()) {
                $errors[$response->getDeviceToken()] = $response->getErrorReason();
            }

            // 处理回调
            if ($message->callback) {
                $callbackData = [
                    'deviceToken' => $response->getDeviceToken(),
                    'status' => $response->isSuccessful() ? 'success' : 'fail',
                    'taskId' => $response->getApnsId(),
                    'timestamp' => time()
                ];

                if (Response::APNS_SUCCESS != $response->getStatusCode()) {
                    $callbackData['reason'] = $response->getErrorReason();
                }

                $this->notifyCallback($message->callback, $callbackData, $message->callbackParam);
            }
        }

        if (!empty($errors)) {
            throw new GatewayErrorException('部分设备推送失败: ' . json_encode($errors));
        }
    }

    /**
     * 检查是否为LiveKit消息
     *
     * @param AbstractMessage $message
     * @return bool
     */
    protected function isLiveKitMessage(AbstractMessage $message)
    {
        // 检查extra中是否包含LiveKit相关字段
        if ($message->extra && is_array($message->extra)) {
            return isset($message->extra['livekit']) ||
                isset($message->extra['call_type']) ||
                isset($message->extra['room_name']) ||
                isset($message->extra['caller_id']);
        }

        // 检查gatewayOptions中是否指定了LiveKit
        if ($message->gatewayOptions && is_array($message->gatewayOptions)) {
            $iosOptions = ArrayHelper::getValue($message->gatewayOptions, 'ios', []);
            return isset($iosOptions['livekit']) && $iosOptions['livekit'] === true;
        }

        return false;
    }

    /**
     * 创建pushok Payload对象
     *
     * @param AbstractMessage $message
     * @return Payload
     */
    protected function createPushokPayload(AbstractMessage $message)
    {
        if ($this->isLiveKitMessage($message)) {
            return $this->createLiveKitPushokPayload($message);
        }

        // 创建普通payload
        $payload = Payload::create();

        // 设置alert
        $alert = Alert::create()
            ->setTitle($message->title)
            ->setBody($message->content);

        if ($message->subTitle) {
            $alert->setSubtitle($message->subTitle);
        }

        $payload->setAlert($alert);

        // 设置其他属性
        if ($message->badge) {
            $payload->setBadge(intval($message->badge));
        }

        $payload->setSound('default');

        // 添加自定义数据
        if ($message->extra && is_array($message->extra)) {
            foreach ($message->extra as $key => $value) {
                $payload->setCustomValue($key, $value);
            }
        }

        // 合并网关选项
        if ($message->gatewayOptions && is_array($message->gatewayOptions)) {
            $iosOptions = ArrayHelper::getValue($message->gatewayOptions, 'ios', []);
            if (isset($iosOptions['aps'])) {
                foreach ($iosOptions['aps'] as $key => $value) {
                    switch ($key) {
                        case 'mutable-content':
                            $payload->setMutableContent($value == 1);
                            if ($value == 1) {
                                $payload->setSound(null); // 移除声音
                            }
                            break;
                        case 'content-available':
                            $payload->setContentAvailability($value == 1);
                            break;
                        case 'category':
                            $payload->setCategory($value);
                            break;
                        case 'thread-id':
                            $payload->setThreadId($value);
                            break;
                    }
                }
            }

            // 添加其他自定义字段
            foreach ($iosOptions as $key => $value) {
                if ($key !== 'aps') {
                    $payload->setCustomValue($key, $value);
                }
            }
        }

        return $payload;
    }

    /**
     * 创建LiveKit专用的pushok Payload
     *
     * @param AbstractMessage $message
     * @return Payload
     */
    protected function createLiveKitPushokPayload(AbstractMessage $message)
    {
        $payload = Payload::create();

        // 设置content-available
        $payload->setContentAvailability(true);

        // 添加alert（用于显示通知）
        if ($message->title || $message->content) {
            $alert = Alert::create()
                ->setTitle($message->title ?: 'Incoming Call')
                ->setBody($message->content ?: 'You have an incoming call');
            $payload->setAlert($alert);
        }

        // 添加LiveKit数据
        $livekitData = [
            'room_name' => $message->extra['room_name'] ?? '',
            'caller_id' => $message->extra['caller_id'] ?? '',
            'call_type' => $message->extra['call_type'] ?? 'voice',
            'timestamp' => time(),
        ];

        // 合并额外的LiveKit参数
        if ($message->extra && is_array($message->extra)) {
            foreach ($message->extra as $key => $value) {
                if (strpos($key, 'livekit_') === 0) {
                    $livekitKey = substr($key, 8); // 移除 'livekit_' 前缀
                    $livekitData[$livekitKey] = $value;
                }
            }
        }

        $payload->setCustomValue('livekit', $livekitData);

        // 合并网关选项
        if ($message->gatewayOptions && is_array($message->gatewayOptions)) {
            $iosOptions = ArrayHelper::getValue($message->gatewayOptions, 'ios', []);
            foreach ($iosOptions as $key => $value) {
                if ($key !== 'aps' && $key !== 'livekit') {
                    $payload->setCustomValue($key, $value);
                }
            }
        }

        return $payload;
    }

    /**
     * 处理回调通知
     *
     * @param string $callbackUrl
     * @param array $data
     * @param string $callbackParam
     */
    protected function notifyCallback($callbackUrl, $data, $callbackParam = null)
    {
        if (!$callbackUrl) {
            return;
        }

        if ($callbackParam) {
            $data['params'] = $callbackParam;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $client->postAsync($callbackUrl, ['json' => $data])->wait();
        } catch (\Exception $e) {
            // 忽略回调错误，不影响主流程
        }
    }

    /**
     * 解析iOS推送回调数据
     *
     * @param array $callbackData 回调数据
     * @return array 解析后的数据
     */
    public function parseCallback(array $callbackData)
    {
        $result = [
            'gateway' => 'ios-token',
            'device_token' => null,
            'message_id' => null,
            'event_type' => null,
            'timestamp' => null,
            'raw_data' => $callbackData
        ];

        // 解析设备token
        if (isset($callbackData['deviceToken'])) {
            $result['device_token'] = $callbackData['deviceToken'];
        }

        // 解析消息ID (apns-id)
        if (isset($callbackData['taskId'])) {
            $result['message_id'] = $callbackData['taskId'];
        }

        // 解析事件类型
        if (isset($callbackData['status'])) {
            switch ($callbackData['status']) {
                case 'success':
                    $result['event_type'] = 'delivered'; // 送达成功
                    break;
                case 'fail':
                    $result['event_type'] = 'failed'; // 推送失败
                    if (isset($callbackData['reason'])) {
                        $result['error_reason'] = $callbackData['reason'];
                    }
                    break;
                default:
                    $result['event_type'] = 'unknown';
                    break;
            }
        }

        // 解析时间戳
        if (isset($callbackData['timestamp'])) {
            $result['timestamp'] = $callbackData['timestamp'];
        } else {
            $result['timestamp'] = time();
        }

        return $result;
    }

    protected function formatTo($to)
    {
        if (!is_array($to)) {
            $to = [$to];
        }
        // 验证iOS设备token格式：64位十六进制字符串
        return array_filter($to, function ($item) {
            return ctype_xdigit($item) && strlen($item) == 64;
        });
    }
}