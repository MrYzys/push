<?php


namespace BetterUs\Push\Gateways;


use BetterUs\Push\AbstractMessage;
use BetterUs\Push\Exceptions\GatewayErrorException;
use BetterUs\Push\Traits\HasHttpRequest;

class VivoGateway extends Gateway
{
    use HasHttpRequest;

    // https://swsdl.vivo.com.cn/appstore/developer/uploadfile/20191210/we5XL6/PUSH-UPS-API接口文档%20-%202.7.0版.pdf

    const BASE_URL = 'https://api-push.vivo.com.cn';

    const AUTH_METHOD = 'message/auth';

    const SINGLE_PUSH_METHOD = 'message/send';

    const SAVE_MESSAGE_METHOD = 'message/saveListPayload';

    const MULTI_PUSH_METHOD = 'message/pushToList';

    const OK_CODE = 0;

    const GATEWAY_NAME = 'vivo';

    protected $maxTokens = 1000;

    protected $headers = [
        'Content-Type' => 'application/json'
    ];

    public function getAuthToken()
    {
        $data = [
            'appId' => $this->config->get('appId'),
            'appKey' => $this->config->get('appKey'),
            'timestamp' => $this->getTimestamp()
        ];
        $data['sign'] = $this->generateSign($data);

        $result = $this->postJson(
            sprintf('%s/%s', self::BASE_URL, self::AUTH_METHOD),
            $data,
            $this->getHeaders()
        );

        $this->assertFailure($result, '获取Vivo推送token失败');

        return [
            'token' => $result['authToken'],
            'expires' => strtotime('+1day') - time()
        ];
    }

    public function pushNotice($to, AbstractMessage $message, array $options = [])
    {
        if (! empty($options['token'])) {
            $token = $options['token'];
            unset($options['token']);
        } else {
            $tokenInfo = $this->getAuthToken();
            $token = $tokenInfo['token'];
        }
        $this->setHeader('authToken', $token);

        $to = is_array($to) ? array_unique($to) : [$to];
        if (count($to) > 1) {
            return $this->pushMultiNotify($to, $message, $options);
        } else {
            $to = array_pop($to);
            return $this->pushSingleNotify($to, $message, $options);
        }
    }

    protected function pushSingleNotify($to, AbstractMessage $message, array $options = [])
    {
        $data = [
            'regId' => $to,
            'title' => $message->title,
            'content' => $message->content,
            'skipType' => 1,
            'skipContent' => $this->generateIntent($this->config->get('appPkgName'), $message->extra),
            'requestId' => $message->businessId,
            'notifyType' => 4,
            'category' => $message->extra['vivo']['category'] ?? 'IM',
            'addBadge' => true,
        ];
        // 设置回调地址
        $this->setCallbackUrl($data, $message);
        $data = $this->mergeGatewayOptions($data, $message->gatewayOptions);
        $result = $this->postJson(
            sprintf('%s/%s', self::BASE_URL, self::SINGLE_PUSH_METHOD),
            $data,
            $this->getHeaders()
        );
        $this->assertFailure($result, 'Vivo推送失败');

        return $result['taskId'];
    }

    protected function pushMultiNotify($to, AbstractMessage $message, array $options = [])
    {
        $data = [
            'regIds' => $this->formatTo($to),
            'taskId' => $this->saveMessageToCloud($message),
            'requestId' => $message->businessId
        ];
        $result = $this->postJson(
            sprintf('%s/%s', self::BASE_URL, self::MULTI_PUSH_METHOD),
            $data,
            $this->getHeaders()
        );
        $this->assertFailure($result, 'Vivo推送失败');

        return $data['taskId'];
    }

    protected function saveMessageToCloud(AbstractMessage $message, array $options = [])
    {
        $data = [
            'title' => $message->title,
            'content' => $message->content,
            'skipType' => 1,
            'skipContent' => $this->generateIntent($this->config->get('appPkgName'), $message->extra),
            'requestId' => $message->businessId,
            'notifyType' => 4,
            'category' => $message->extra['vivo']['category'] ?? 'IM',
            'addBadge' => true,
        ];
        // 设置回调地址
        $this->setCallbackUrl($data, $message);
        $data = $this->mergeGatewayOptions($data, $message->gatewayOptions);
        $result = $this->postJson(
            sprintf('%s/%s', self::BASE_URL, self::SAVE_MESSAGE_METHOD),
            $data,
            $this->getHeaders()
        );
        $this->assertFailure($result, '保存推送消息至Vivo服务器失败');
        return $result['taskId'];
    }

    protected function getTimestamp()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    protected function generateSign($data)
    {
        $strToSign = implode('',[
            $this->config->get('appId'),
            $this->config->get('appKey'),
            $data['timestamp'],
            $this->config->get('appSecret')
        ]);
        return bin2hex(hash('md5', $strToSign, true));
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

    /**
     * 设置回调URL
     *
     * @param array $data
     * @param AbstractMessage $message
     */
    protected function setCallbackUrl(array &$data, AbstractMessage $message)
    {
        // 优先使用消息中设置的回调地址
        if ($message->callback) {
            $data['extra'] = [
                'callback' => $message->callback
            ];
            if ($message->callbackParam) {
                $data['extra']['callback.param'] = $message->callbackParam;
            }
        } else {
            // 使用默认回调地址
            $data['extra'] = [
                'callback' => 'https://open.example.com/push/callback/vivo'
            ];
        }
    }

    /**
     * 解析vivo推送回调数据
     *
     * @param array $callbackData 回调数据
     * @return array 解析后的数据
     */
    public function parseCallback(array $callbackData)
    {
        $result = [
            'gateway' => 'vivo',
            'message_id' => null,
            'registration_ids' => [],
            'event_type' => null,
            'timestamp' => null,
            'raw_data' => $callbackData
        ];

        // 解析消息ID
        if (isset($callbackData['taskId'])) {
            $result['message_id'] = $callbackData['taskId'];
        }

        // 解析设备regId列表
        if (isset($callbackData['regId'])) {
            $result['registration_ids'] = is_array($callbackData['regId'])
                ? $callbackData['regId']
                : [$callbackData['regId']];
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
                    $result['event_type'] = 'invalid_regid'; // 无效regId
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

    protected function assertFailure($result, $message)
    {
        if (!isset($result['result']) || $result['result'] != self::OK_CODE) {
            throw new GatewayErrorException(sprintf(
                '%s > [%s] %s',
                $message,
                isset($result['result']) ? $result['result'] : '-99',
                json_encode($result, JSON_UNESCAPED_UNICODE)
            ));
        }
    }
}