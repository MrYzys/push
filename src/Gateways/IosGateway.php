<?php


namespace BetterUs\Push\Gateways;


use Apns\Client;
use Apns\Exception\ApnsException;
use Apns\Message;
use BetterUs\Push\AbstractMessage;
use BetterUs\Push\ApnsMessage;
use BetterUs\Push\Exceptions\GatewayErrorException;
use BetterUs\Push\Exceptions\InvalidArgumentException;
use BetterUs\Push\Support\ArrayHelper;

class IosGateway extends Gateway
{
    const GATEWAY_NAME = 'ios';

    protected $maxTokens = 100;

    /**
     * @var Client $pusher
     */
    private $pusher = null;

    private $bundleId;

    public function getAuthToken()
    {
        return null;
    }

    public function setPusher(Client $pusher)
    {
        $this->pusher = $pusher;
    }

    private function checkPusher()
    {
        if (!isset($this->pusher)) {
            $isSandBox = $this->config->get('isSandBox');
            $certPath = $this->config->get('certPath');
            if (!file_exists($certPath)) {
                throw new InvalidArgumentException('无效的推送证书地址 > ' . $certPath);
            }
            $password = $this->config->get('password');

            $this->pusher = new Client(
                [$certPath, $password],
                $isSandBox
            );
        }
    }

    private function parseBundleId()
    {
        $cert = openssl_x509_parse(file_get_contents($this->pusher->getSslCert()[0]));
        if (!$cert) {
            throw new InvalidArgumentException('证书解析失败');
        }
        $this->bundleId = $cert['subject']['UID'];
    }

    public function pushNotice($to, AbstractMessage $message, array $options = [])
    {
        $tokens = $this->formatTo($to);
        if (!$tokens) {
            throw new InvalidArgumentException('无有效的设备token');
        }
        if (!empty($options['push']) && $options['push'] instanceof Client) {
            $this->setPusher($options['push']);
        }
        $this->checkPusher();
        $this->parseBundleId();

        $payload = $this->createPayload($message);
        $messageEntity = new ApnsMessage();
        $messageEntity->setMessageEntity($payload);
        $messageEntity->setTopic($this->getApnsTopic($message));

        $result = [];
        foreach ($tokens as $token) {
            $msg = clone $messageEntity;
            $msg->setDeviceIdentifier($token);
            try {
                $this->pusher->send($msg);
            } catch (ApnsException $e) {
                $result[$token] = $e->getMessage();
            }
        }
        if ($result) {
            throw new GatewayErrorException(json_encode($result));
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
            return $this->bundleId . '.voip';
        }

        return $this->bundleId;
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

    private function createPayload(AbstractMessage $message)
    {
        if ($this->isLiveKitMessage($message)) {
            return $this->createLiveKitPayload($message);
        }

        $messageData = [
            'aps' => [
                'alert' => [
                    'title' => $message->title,
                    'body' => $message->content,
                    'subtitle' => $message->subTitle,
                ],
                'sound' => 'default',
            ]
        ];
        if (! empty($message->badge)) {
            $messageData['aps']['badge'] = intval($message->badge);
        }
        if (! empty($message->extra)) {
            $messageData = ArrayHelper::merge($messageData, $message->extra);
        }
        $iosGatewayOption = is_array($message->gatewayOptions) ?
            ArrayHelper::getValue($message->gatewayOptions, 'ios') : [];
        $messageData = empty($iosGatewayOption) ?
            $messageData : ArrayHelper::merge($messageData, $iosGatewayOption);
        if (ArrayHelper::getValue($messageData, 'aps.mutable-content') == 1) {
            unset($messageData['aps']['sound']);
        }
        return $messageData;
    }

    /**
     * 创建LiveKit专用的payload
     *
     * @param AbstractMessage $message
     * @return array
     */
    protected function createLiveKitPayload(AbstractMessage $message)
    {
        $messageData = [
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
            $messageData['aps']['alert'] = [
                'title' => $message->title ?: 'Incoming Call',
                'body' => $message->content ?: 'You have an incoming call',
            ];
        }

        // 合并额外的LiveKit参数
        if ($message->extra && is_array($message->extra)) {
            foreach ($message->extra as $key => $value) {
                if (strpos($key, 'livekit_') === 0) {
                    $livekitKey = substr($key, 8); // 移除 'livekit_' 前缀
                    $messageData['livekit'][$livekitKey] = $value;
                }
            }
        }

        // 合并网关选项
        $iosGatewayOption = is_array($message->gatewayOptions) ?
            ArrayHelper::getValue($message->gatewayOptions, 'ios') : [];
        $messageData = empty($iosGatewayOption) ?
            $messageData : ArrayHelper::merge($messageData, $iosGatewayOption);

        return $messageData;
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
            'gateway' => 'ios',
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

        // 解析消息ID
        if (isset($callbackData['messageId'])) {
            $result['message_id'] = $callbackData['messageId'];
        }

        // 解析事件类型
        if (isset($callbackData['status'])) {
            switch ($callbackData['status']) {
                case 'success':
                    $result['event_type'] = 'delivered'; // 送达成功
                    break;
                case 'error':
                case 'fail':
                    $result['event_type'] = 'failed'; // 推送失败
                    if (isset($callbackData['error'])) {
                        $result['error_reason'] = $callbackData['error'];
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

    public function __destruct()
    {
        $this->pusher && $this->getGatewayName() == static::GATEWAY_NAME && $this->pusher = null;
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