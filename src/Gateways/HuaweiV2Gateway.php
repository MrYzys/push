<?php

namespace BetterUs\Push\Gateways;


use BetterUs\Push\AbstractMessage;
use BetterUs\Push\Exceptions\GatewayErrorException;
use BetterUs\Push\Traits\HasHttpRequest;

class HuaweiV2Gateway extends Gateway
{
    use HasHttpRequest;

    // https://developer.huawei.com/consumer/cn/doc/development/HMS-References/push-sendapi

    const AUTH_URL = 'https://oauth-login.cloud.huawei.com/oauth2/v2/token';

    // https://push-api.cloud.huawei.com/v1/[appid]/messages:send
    const PUSH_URL = 'https://push-api.cloud.huawei.com/v1/%s/messages:send';

    const OK_CODE = '80000000';

    const GATEWAY_NAME = 'huawei-v2';

    protected $maxTokens = 1000;

    protected $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded',
    ];

    public function pushNotice($to, AbstractMessage $message, array $options = [])
    {
        if (! empty($options['token'])) {
            $token = $options['token'];
            unset($options['token']);
        } else {
            $tokenInfo = $this->getAuthToken();
            $token = $tokenInfo['token'];
        }

        $androidConfig = [
            'collapse_key' => -1,
            'bi_tag' => $message->businessId ?: '',
            'notification' => [
                'title' => $message->title,
                'body' => $message->content ,
                'tag' => $message->notifyId ?: null,
                'notify_id' => $message->notifyId ?: -1,
                'click_action' => [
                    'type' => 1,
                    'intent' => $this->generateIntent($this->config->get('appPkgName'), $message->extra),
                ]
            ]
        ];

        // 设置回调地址
        $this->setCallbackUrl($androidConfig, $message);
        if ($message->badge) {
            if (preg_match('/^\d+$/', $message->badge)) {
                $androidConfig['notification']['badge'] = [
                    'set_num' => intval($message->badge),
                    'class' => 'com.mysoft.core.activity.LauncherActivity'
                ];
            } else {
                $androidConfig['notification']['badge'] = [
                    'add_num' => intval($message->badge),
                    'class' => 'com.mysoft.core.activity.LauncherActivity'
                ];
            }
        }
        $androidConfig = $this->mergeGatewayOptions($androidConfig, $message->gatewayOptions);
        $data = [
            'message' => [
                'token' => $this->formatTo($to),
                'android' => $androidConfig,
            ],
        ];

        $this->setHeader('Authorization', 'Bearer ' . $token);

        $result = $this->postJson($this->buildPushUrl(), $data, $this->getHeaders());
        if (!isset($result['code']) || $result['code'] != self::OK_CODE) {
            throw new GatewayErrorException(sprintf(
                '华为推送失败 > [%s] %s',
                isset($result['code']) ? $result['code'] : '-99',
                json_encode($result, JSON_UNESCAPED_UNICODE)
            ));
        }
        return $result['requestId'];
    }

    public function getAuthToken()
    {
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->config->get('clientId'),
            'client_secret' => $this->config->get('clientSecret')
        ];
        $result = $this->post(self::AUTH_URL, $data, $this->getHeaders());

        if (!isset($result['access_token'])) {
            throw new GatewayErrorException(sprintf(
                '获取华为推送token失败 > [%s] %s',
                isset($result['error']) ? $result['error'] : '-99',
                isset($result['error_description']) ? $result['error_description'] : '未知异常'
            ));
        }

        return [
            'token' => $result['access_token'],
            'expires' => $result['expires_in']
        ];
    }

    protected function getTimestamp()
    {
        return strval(time());
    }

    protected function buildPushUrl()
    {
        return sprintf(self::PUSH_URL, $this->config->get('clientId'));
    }

    /**
     * 设置回调URL
     *
     * @param array $androidConfig
     * @param AbstractMessage $message
     */
    protected function setCallbackUrl(array &$androidConfig, AbstractMessage $message)
    {
        // 优先使用消息中设置的回调地址
        if ($message->callback) {
            $androidConfig['receipt_id'] = $message->callback;
            if ($message->callbackParam) {
                $androidConfig['callback_param'] = $message->callbackParam;
            }
        } else {
            // 使用默认回调地址
            $androidConfig['receipt_id'] = 'https://open.example.com/push/callback/huawei';
        }
    }

    /**
     * 解析华为推送回调数据
     *
     * @param array $callbackData 回调数据
     * @return array 解析后的数据
     */
    public function parseCallback(array $callbackData)
    {
        $result = [
            'gateway' => 'huawei-v2',
            'message_id' => null,
            'registration_ids' => [],
            'event_type' => null,
            'timestamp' => null,
            'raw_data' => $callbackData
        ];

        // 解析消息ID
        if (isset($callbackData['requestId'])) {
            $result['message_id'] = $callbackData['requestId'];
        }

        // 解析设备token列表
        if (isset($callbackData['token'])) {
            $result['registration_ids'] = is_array($callbackData['token'])
                ? $callbackData['token']
                : [$callbackData['token']];
        }

        // 解析事件类型
        if (isset($callbackData['eventType'])) {
            switch ($callbackData['eventType']) {
                case 1:
                    $result['event_type'] = 'delivered'; // 送达
                    break;
                case 2:
                    $result['event_type'] = 'clicked'; // 点击
                    break;
                case 3:
                    $result['event_type'] = 'invalid_token'; // 无效token
                    break;
                case 10:
                    $result['event_type'] = 'sent'; // 已发送
                    break;
                default:
                    $result['event_type'] = 'unknown';
                    break;
            }
        }

        // 解析时间戳
        if (isset($callbackData['timestamp'])) {
            $result['timestamp'] = $callbackData['timestamp'];
        }

        return $result;
    }

    protected function formatTo($to)
    {
        if (!is_array($to)) {
            $to = [$to];
        } else {
            $this->checkMaxToken($to);
        }
        return $to;
    }
}