<?php


namespace BetterUs\Push\Gateways;


use BetterUs\Push\AbstractMessage;
use BetterUs\Push\Exceptions\GatewayErrorException;
use BetterUs\Push\Traits\HasHttpRequest;

class XiaomiGateway extends Gateway
{
    use HasHttpRequest;

    const PUSH_URL = 'https://api.xmpush.xiaomi.com/v3/message/regid';

    const OK_CODE = 0;

    const GATEWAY_NAME = 'xiaomi';

    protected $maxTokens = 100;


    public function getAuthToken()
    {
        return null;
    }

    public function pushNotice($to, AbstractMessage $message, array $options = [])
    {
        $this->setHeader('Authorization', sprintf('key=%s', $this->config->get('appSecret')));

        // 构建推送数据
        $data = [
            'payload' => urlencode($message->content),
            'restricted_package_name' => $this->config->get('appPkgName'),
            'pass_through' => 0,
            'title' => $message->title,
            'description' => $message->content,
            'extra.notify_effect' => '2',
            'extra.intent_uri' => $this->generateIntent($this->config->get('appPkgName'), $message->extra),
            'registration_id' => $this->formatTo($to),
        ];

        // 设置通知ID和业务ID
        $message->notifyId && $data['extra.jobkey'] = $message->notifyId;
        $message->businessId && $data['notify_id'] = $message->businessId;

        // 设置回调地址
        $this->setCallbackUrl($data, $message);

        // 合并网关选项
        $data = $this->mergeGatewayOptions($data, $message->gatewayOptions);

        $result = $this->post(self::PUSH_URL, $data, $this->getHeaders());
        $this->assertFailure($result, '小米推送失败');

        $returnData = $result['data'];
        return $returnData['id'];
    }

    protected function formatTo($to)
    {
        if (!is_array($to)) {
            $to = [$to];
        } else {
            $this->checkMaxToken($to);
        }
        return implode(',', $to);
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
            $data['extra.callback'] = $message->callback;
            if ($message->callbackParam) {
                $data['extra.callback.param'] = $message->callbackParam;
            }
        } else {
            // 使用默认回调地址
            $data['extra.callback'] = 'https://open.example.com/push/callback/xiaomi';
            // 设置回调类型，包含送达和点击回执
            $data['extra.callback.type'] = 3; // 1=送达回执, 2=点击回执, 3=送达+点击回执
        }
    }

    /**
     * 解析小米推送回调数据
     *
     * @param array $callbackData 回调数据
     * @return array 解析后的数据
     */
    public function parseCallback(array $callbackData)
    {
        $result = [
            'gateway' => 'xiaomi',
            'message_id' => null,
            'registration_ids' => [],
            'event_type' => null,
            'timestamp' => null,
            'raw_data' => $callbackData
        ];

        // 解析消息ID
        if (isset($callbackData['id'])) {
            $result['message_id'] = $callbackData['id'];
        }

        // 解析设备ID列表
        if (isset($callbackData['targets'])) {
            $result['registration_ids'] = is_array($callbackData['targets'])
                ? $callbackData['targets']
                : [$callbackData['targets']];
        }

        // 解析事件类型
        if (isset($callbackData['type'])) {
            switch ($callbackData['type']) {
                case 1:
                    $result['event_type'] = 'delivered'; // 送达
                    break;
                case 2:
                    $result['event_type'] = 'clicked'; // 点击
                    break;
                case 16:
                    $result['event_type'] = 'invalid_target'; // 无效目标
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
        if (!isset($result['code']) || $result['code'] != self::OK_CODE) {
            throw new GatewayErrorException(sprintf(
                '%s > [%s] %s',
                $message,
                isset($result['code']) ? $result['code'] : '-99',
                json_encode($result, JSON_UNESCAPED_UNICODE)
            ));
        }
    }
}