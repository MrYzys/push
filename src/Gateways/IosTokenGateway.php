<?php

namespace BetterUs\Push\Gateways;


use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use BetterUs\Push\AbstractMessage;
use BetterUs\Push\Exceptions\InvalidArgumentException;
use BetterUs\Push\Support\ArrayHelper;

class IosTokenGateway extends Gateway
{
    // https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/establishing_a_token-based_connection_to_apns

    const ALGORITHM = 'ES256';

    const GATEWAY_NAME = 'ios-token';

    protected $maxTokens = 100;

    public function getAuthToken()
    {
        $token = $this->generateJwt();
        return [
            'token' => $token,
            'expires' => strtotime('+ 50 minutes') - time()
        ];
    }

    protected function generateJwt()
    {
        $payload = [
            'iss' => $this->config->get('teamId'),
            'iat' => time()
        ];
        $header = [
            'alg' => static::ALGORITHM,
            'kid' => $this->config->get('keyId')
        ];
        $secretContent = $this->config->get('secretContent');
        if (! $secretContent) {
            $secretFile = $this->config->get('secretFile');
            if (!file_exists($secretFile)) {
                throw new InvalidArgumentException('无效的推送密钥证书地址 > ' . $secretFile);
            }
            $secretContent = file_get_contents($secretFile);
        }

        return JWT::encode($payload, $secretContent, static::ALGORITHM, null, $header);
    }

    public function pushNotice($to, AbstractMessage $message, array $options = [])
    {
        $to = $this->formatTo($to);
        if (!$to) {
            throw new InvalidArgumentException('无有效的设备token');
        }

        if (isset($options['token'])) {
            $token = $options['token'];
            unset($options['token']);
        } else {
            $tokenInfo = $this->getAuthToken();
            $token = $tokenInfo['token'];
        }

        $header = [
            'authorization' => sprintf('bearer %s', $token),
            'apns-topic' => $this->getApnsTopic($message),
            'content-type' => 'application/json',
        ];

        // 设置推送类型和优先级
        $this->setApnsHeaders($header, $message);

        !is_null($message->businessId) && $header['apns-id'] = $message->businessId;
        !is_null($message->notifyId) && $header['apns-collapse-id'] = $message->notifyId;
        $payload = $this->createPayload($message);

        $callback = [];
        if ($message->callback) {
            $callback['url'] = $message->callback;
            if ($message->callbackParam) {
                $callback['params'] = $message->callbackParam;
            }
        }
        $this->_push($to, $payload, $header, $callback);
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
     * 设置APNs头部信息
     *
     * @param array $header
     * @param AbstractMessage $message
     */
    protected function setApnsHeaders(array &$header, AbstractMessage $message)
    {
        if ($this->isLiveKitMessage($message)) {
            // LiveKit消息使用高优先级
            $header['apns-priority'] = '10';
            $header['apns-push-type'] = 'voip';
        } else {
            // 普通消息使用标准优先级
            $header['apns-priority'] = '5';
            $header['apns-push-type'] = 'alert';
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

    protected function createPayload(AbstractMessage $message)
    {
        if ($this->isLiveKitMessage($message)) {
            return $this->createLiveKitPayload($message);
        }

        $payload = [
            'aps' => [
                'alert' => [
                    'title' => $message->title,
                    'subtitle' => $message->subTitle ? $message->subTitle : '',
                    'body' => $message->content,
                ],
                'sound' => 'default',
                'badge' => $message->badge ? intval($message->badge) : 0,
            ],
        ];
        if ($message->extra && is_array($message->extra)) {
            $payload = array_merge($payload, $message->extra);
        }
        $payload = $this->mergeGatewayOptions($payload, $message->gatewayOptions);
        if (ArrayHelper::getValue($payload, 'aps.mutable-content') == 1) {
            unset($payload['aps']['sound']);
        }
        return json_encode($payload);
    }

    /**
     * 创建LiveKit专用的payload
     *
     * @param AbstractMessage $message
     * @return string
     */
    protected function createLiveKitPayload(AbstractMessage $message)
    {
        $payload = [
            'aps' => [
                'content-available' => 1,
            ],
            'livekit' => [
                'room_name' => $message->extra['room_name'] ?? '',
                'caller_id' => $message->extra['caller_id'] ?? '',
                'call_type' => $message->extra['call_type'] ?? 'voice',
                'timestamp' => time(),
            ]
        ];

        // 添加标题和内容（用于显示通知）
        if ($message->title || $message->content) {
            $payload['aps']['alert'] = [
                'title' => $message->title ?: 'Incoming Call',
                'body' => $message->content ?: 'You have an incoming call',
            ];
        }

        // 合并额外的LiveKit参数
        if ($message->extra && is_array($message->extra)) {
            foreach ($message->extra as $key => $value) {
                if (strpos($key, 'livekit_') === 0) {
                    $livekitKey = substr($key, 8); // 移除 'livekit_' 前缀
                    $payload['livekit'][$livekitKey] = $value;
                }
            }
        }

        // 合并网关选项
        $payload = $this->mergeGatewayOptions($payload, $message->gatewayOptions);

        return json_encode($payload);
    }

    private function getPushUrl()
    {
        $isSandBox = $this->config->get('isSandBox');
        if ($isSandBox) {
            return 'https://api.sandbox.push.apple.com/3/device/';
        } else {
            return 'https://api.push.apple.com/3/device/';
        }
    }

    protected function _push($deviceTokens, $payload, $header, $callback = [])
    {
        $client = new Client();

        $requests = function ($deviceTokens, $payload, $header) {
            $baseUrl = $this->getPushUrl();
            foreach ($deviceTokens as $deviceToken) {
                yield new Request(
                    'POST',
                    $baseUrl . $deviceToken,
                    $header,
                    $payload,
                    2.0
                );
            }
        };

        $pool = new Pool($client, $requests($deviceTokens, $payload, $header), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) use ($deviceTokens, $callback, $payload) {
                $apnsIds = $response->getHeader('apns-id');
                $apnsId = array_pop($apnsIds);
                $deviceToken = $deviceTokens[$index];
                $result = [
                    'deviceToken' => $deviceToken,
                    'status' => 'success',
                    'taskId' => $apnsId
                ];
                $this->notifyCallback($callback, $result, $payload);
            },
            'rejected' => function ($reason, $index) use ($deviceTokens, $callback, $payload) {
                $errorMsg = $reason->getMessage();
                $deviceToken = $deviceTokens[$index];
                if (preg_match_all('/(\d{3} [^`]+).*"reason":"(.+)"/is', $errorMsg, $matches)) {
                    $msg = sprintf('%s for %s', $matches[1][0], $matches[2][0]);
                } else {
                    $msg = $errorMsg;
                }
                $result = [
                    'deviceToken' => $deviceToken,
                    'status' => 'fail',
                    'reason' => $msg
                ];
                $this->notifyCallback($callback, $result, $payload);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    protected function notifyCallback($callback, $data, $payload)
    {
        if (!$callback) {
            return;
        }
        if (isset($callback['params'])) {
            $data['params'] = $callback['params'];
        }
        $payload = json_decode($payload, true);
        $data['businessId'] = $payload['aps']['alert']['apns-collapse-id'];

        $client = new Client();
        $promise = $client->postAsync($callback['url'], ['json' => $data]);
        $promise->wait();
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