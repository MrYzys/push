<?php


namespace BetterUs\Push\Gateways;


use BetterUs\Push\AbstractMessage;
use BetterUs\Push\Exceptions\GatewayErrorException;
use BetterUs\Push\Traits\HasHttpRequest;

class OppoGateway extends Gateway
{
    use HasHttpRequest;

    // http://storepic.oppomobile.com/openplat/resource/201904/03/OPPO推送平台服务端API-V1.6.pdf

    const BASE_URL = 'https://api.push.oppomobile.com/server/v1';

    const INTL_BASE_URL = 'https://api-intl.push.oppomobile.com/server/v1';

    const AUTH_METHOD = 'auth';

    const SAVE_MESSAGE_CONTENT_METHOD = 'message/notification/save_message_content';

    const BROADCAST_METHOD = 'message/notification/broadcast';

    const PUSH_METHOD = 'message/notification/unicast';

    const OK_CODE = 0;

    const GATEWAY_NAME = 'oppo';

    protected $maxTokens = 1000;

    protected $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded',
    ];


    public function getAuthToken()
    {
        // 默认获取国内环境的token，保持向后兼容性
        return $this->getAuthTokenForRegion(self::BASE_URL);
    }

    /**
     * 根据指定的API基础URL获取认证token
     *
     * @param string $baseUrl
     * @return array
     */
    protected function getAuthTokenForRegion($baseUrl)
    {
        $data = [
            'app_key' => $this->config->get('appKey'),
            'timestamp' => $this->getTimestamp()
        ];
        $data['sign'] = $this->generateSign($data);

        $result = $this->post(
            sprintf('%s/%s', $baseUrl, self::AUTH_METHOD),
            $data,
            $this->getHeaders()
        );
        $this->assertFailure($result, '获取Oppo推送token失败');

        $createdTime = (int) ($result['data']['create_time'] / 1000);
        return [
            'token' => $result['data']['auth_token'],
            'expires' => strtotime('+1day', $createdTime) - time()
        ];
    }

    public function pushNotice($to, AbstractMessage $message, array $options = [])
    {
        $messageData = [
            'title' => $message->title,
            'sub_title' => $message->subTitle ? $message->subTitle : '',
            'content' => $message->content,
            'click_action_type' => 5,
            'click_action_url' => $this->generateIntent($this->config->get('appPkgName'), $message->extra),
            'channel_id' => $message->extra['oppo']['channelId'] ?? 'IM',
            'off_line' => true,
            'off_line_ttl' => 86400,
            'show_start_time' => time(),
            'show_expire_time' => time() + 86400*2,
            'notify_level' => 16,
            'category' => $message->extra['oppo']['category'] ?? 'IM'
        ];
        $message->businessId && $messageData['app_message_id'] = $message->businessId;
        if ($message->callback) {
            $messageData['call_back_url'] = $message->callback;
            if ($message->callbackParam) {
                $messageData['call_back_parameter'] = $message->callbackParam;
            }
        }
        $messageData = $this->mergeGatewayOptions($messageData, $message->gatewayOptions);

        $to = is_array($to) ? array_unique($to) : [$to];

        if (count($to) == 1) {
            // 单设备推送
            $registrationId = array_pop($to);
            return $this->pushSingleWithRegion($registrationId, $messageData, $options);
        } else {
            // 多设备推送，需要按区域分组
            return $this->pushMultipleWithRegion($to, $messageData, $options);
        }
    }

    /**
     * 根据设备区域进行单设备推送
     *
     * @param string $registrationId
     * @param array $messageData
     * @param array $options
     * @return string
     */
    protected function pushSingleWithRegion($registrationId, $messageData, array $options = [])
    {
        $baseUrl = $this->getApiBaseUrl($registrationId);

        if (! empty($options['token'])) {
            $token = $options['token'];
        } else {
            $tokenInfo = $this->getAuthTokenForRegion($baseUrl);
            $token = $tokenInfo['token'];
        }

        return $this->pushSingle($token, $registrationId, $messageData, $baseUrl);
    }

    /**
     * 处理多设备推送，按区域分组
     *
     * @param array $registrationIds
     * @param array $messageData
     * @param array $options
     * @return array
     */
    protected function pushMultipleWithRegion(array $registrationIds, $messageData, array $options = [])
    {
        $categorized = $this->categorizeDevices($registrationIds);
        $results = [];

        // 推送国内设备
        if (!empty($categorized['domestic'])) {
            if (! empty($options['token'])) {
                $token = $options['token'];
            } else {
                $tokenInfo = $this->getAuthTokenForRegion(self::BASE_URL);
                $token = $tokenInfo['token'];
            }
            $results['domestic'] = $this->pushBroadcast($token, $categorized['domestic'], $messageData, self::BASE_URL);
        }

        // 推送海外设备
        if (!empty($categorized['international'])) {
            if (! empty($options['token'])) {
                $token = $options['token'];
            } else {
                $tokenInfo = $this->getAuthTokenForRegion(self::INTL_BASE_URL);
                $token = $tokenInfo['token'];
            }
            $results['international'] = $this->pushBroadcast($token, $categorized['international'], $messageData, self::INTL_BASE_URL);
        }

        return $results;
    }

    protected function pushSingle($authToken, $to, $message, $baseUrl = null)
    {
        if ($baseUrl === null) {
            $baseUrl = self::BASE_URL; // 向后兼容
        }

        $data = [
            'message' => json_encode([
                'target_type' => 2,
                'target_value' => $to,
                'notification' => $message
            ]),
            'auth_token' => $authToken
        ];
        $result = $this->post(
            sprintf('%s/%s', $baseUrl, self::PUSH_METHOD),
            $data,
            $this->getHeaders()
        );
        $this->assertFailure($result, 'Oppo推送失败');
        return $result['data']['messageId'];
    }

    protected function saveMessageToCloud($authToken, array $message, $baseUrl = null)
    {
        if ($baseUrl === null) {
            $baseUrl = self::BASE_URL; // 向后兼容
        }

        $message['auth_token'] = $authToken;
        $result = $this->post(
            sprintf('%s/%s', $baseUrl, self::SAVE_MESSAGE_CONTENT_METHOD),
            $message,
            $this->getHeaders()
        );
        $this->assertFailure($result, 'Oppo多推时保存消息至Oppo服务器失败');
        return $result['data']['message_id'];
    }

    protected function pushBroadcast($authToken, $to, $message, $baseUrl = null)
    {
        if ($baseUrl === null) {
            $baseUrl = self::BASE_URL; // 向后兼容
        }

        $messageId = $this->saveMessageToCloud($authToken, $message, $baseUrl);
        $data = [
            'message_id' => $messageId,
            'target_type' => 2,
            'target_value' => $this->formatTo($to),
            'auth_token' => $authToken
        ];
        $result = $this->post(
            sprintf('%s/%s', $baseUrl, self::BROADCAST_METHOD),
            $data,
            $this->getHeaders()
        );
        $this->assertFailure($result, 'Oppo多推失败');
        return $this->parseBroadcastResult($result);
    }

    protected function parseBroadcastResult(array $result)
    {
        $data = $result['data'];
        $messageId = $data['message_id'];
        unset($data['message_id'], $data['task_id'], $data['status']);
        if (count($data) > 0) {
            throw new GatewayErrorException(sprintf(
                'Oppo多推时部分设备推送失败 > %s',
                json_encode($data)
            ));
        }
        return $messageId;
    }

    protected function getTimestamp()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    protected function generateSign($data)
    {
        $strToSign = implode('',[
            $this->config->get('appKey'),
            $data['timestamp'],
            $this->config->get('masterSecret')
        ]);
        return bin2hex(hash('sha256', $strToSign, true));
    }

    protected function formatTo($to)
    {
        if (!is_array($to)) {
            $to = [$to];
        } else {
            $this->checkMaxToken($to);
        }
        return implode(';', $to);
    }

    protected function assertFailure($result, $message)
    {
        if (!isset($result['code']) || $result['code'] != self::OK_CODE) {
            throw new GatewayErrorException(sprintf(
                '%s > [%s] %s',
                $message,
                isset($result['code']) ? $result['code'] : '-99',
                json_encode($result, JSON_UNESCAPED_UNICODE)
            ));
        }
    }

    /**
     * 判断设备是否为国内设备
     * 根据OPPO文档规则：
     * - 数组大小为1：regId属于国内；如：b6bbd94b59cdb5df8391642c1509b7fe
     * - 数组大小为2：第一个值为"CN"，属于国内；如：CN_b6bbd94b59cdb5df8391642c1509b7fe
     * - 数组大小为3：第二个值为"CN"，属于国内；如：OPPO_CN_b6bbd94b59cdb5df8391642c1509b7fe
     *
     * @param string $registrationId
     * @return bool
     */
    protected function isDomesticDevice($registrationId)
    {
        $parts = explode('_', $registrationId);
        $partCount = count($parts);

        if ($partCount == 1) {
            // 数组大小为1：regId属于国内
            return true;
        } elseif ($partCount == 2) {
            // 数组大小为2：第一个值为"CN"，属于国内
            return $parts[0] === 'CN';
        } elseif ($partCount == 3) {
            // 数组大小为3：第二个值为"CN"，属于国内
            return $parts[1] === 'CN';
        }

        // 其他情况默认为海外设备
        return false;
    }

    /**
     * 根据设备类型获取对应的API基础URL
     *
     * @param string $registrationId
     * @return string
     */
    protected function getApiBaseUrl($registrationId)
    {
        return $this->isDomesticDevice($registrationId) ? self::BASE_URL : self::INTL_BASE_URL;
    }

    /**
     * 根据设备列表判断是否为混合推送（包含国内和海外设备）
     *
     * @param array $registrationIds
     * @return array ['domestic' => [], 'international' => []]
     */
    protected function categorizeDevices(array $registrationIds)
    {
        $domestic = [];
        $international = [];

        foreach ($registrationIds as $regId) {
            if ($this->isDomesticDevice($regId)) {
                $domestic[] = $regId;
            } else {
                $international[] = $regId;
            }
        }

        return [
            'domestic' => $domestic,
            'international' => $international
        ];
    }
}