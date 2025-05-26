<div data-v-7b003a97="" id="wikiContent" class="article-content"><h1 id="header-0"><a id="_0"></a><strong>消息回执</strong></h1>
<p>消息回执是OPPO PUSH提供给开发者获知消息送达状态的功能。由于推送消息请求是异步调用，调用请求接口成功仅表示消息成功开始下发，不代表消息最终的推送状态，因此OPPO PUSH在后续的消息推送流程中捕获重要事件，并通过HTTP请求的形式反馈给开发者，这个功能成为消息回执功能。</p>
<h2 id="header-1"><a id="_5"></a><strong>开启和配置消息回执</strong></h2>
<p>要使用消息回执功能，开发者在推送每条消息时需要在对应的请求字段中配置回执参数，目前回执参数主要有两个，一个是开发者接受OPPO PUSH回执HTTP请求的目标地址URL，以及这个回执请求携带的URL参数。<br><br>
回执参数是归属于消息体内容的参数，和其他消息体参数一样，按照不同推送方式，在创建广播消息体或在单点推送接口中正确配置配置参数即可获取消息的回执。<br><br>
以下是回执参数说明：</p>
<table>
<thead>
<tr>
<th>名称</th>
<th>类型</th>
<th>是否必填</th>
<th>默认</th>
<th>描述</th>
<th>是否支持单推</th>
</tr>
</thead>
<tbody>
<tr>
<td>call_back_url</td>
<td>String</td>
<td>否</td>
<td>无</td>
<td>* 仅支持registrationId推送方式 *<br>开发者接收消息送达的回执消息的URL地址。<br>OPPO PUSH提供消息回执的功能，消息回执的功能是指消息送达后，OPPO PUSH基于HTTP/HTTPS请求的方式告知开发者对应消息的送达情况。<br>要使用回执功能，开发者需要配置回执目标地址的URL参数，URL长度限制在限制200以内。<br>以下是一个使用回执的示例：<br>1.开发者配置本参数<br>2.消息到达设备后，OPPO PUSH根据本参数，向这个URL以<br>Content-Type为application/json的方式发送一个HTTP/HTTPS请求。请求内容的示例如下<br>JSON 数据示例：<br>[<br>{<br>“messageId”: “msgId1”, // 到达的消息ID<br>"appId": “appid”, // 对应的应用ID<br>"taskId": “taskId1”, // 如果是广播消息，对应taskID；如果是单推消息，该字段为消息ID<br>"registrationIds": “regId1, regid2”, // 消息的推送目标注册ID<br>"eventTime": “timestamp”, // 回执事件产生时间<br>"param": “call_back_parameter”, // 开发者指定的回执参数<br>"eventType": “push_arrive” // 到达事件，消息到达的事件被定义为push_arrive<br>},<br>// 对于完全一致的URL地址，OPPO PUSH可能会将相同URL地址回执信息在一个HTTP/HTTPS请求中发送,因此body里是一个长度大于等于1的JSON数组<br>{<br>“messageId”: “msgId1”,<br>“appId”: “appid”,<br>“taskId”: “taskId1”,<br>“registrationIds”: “regId1,regid2”,<br>“eventTime”: “timestamp”,<br>“param”: “call_back_parameter”,<br>“eventType”:<br>“push_arrive”/“regid_invalid”/“user_daily_limit”<br>}<br>]</td>
<td>是</td>
</tr>
<tr>
<td>call_back_parameter</td>
<td>String</td>
<td>否</td>
<td>无</td>
<td>开发者指定的自定义回执参数。<br>数字符串长度限制在100以内，OPPO PUSH将这个参数设置在回执请求体单个JSON结构的param字段中。</td>
<td>是</td>
</tr>
</tbody>
</table>
<h2 id="header-2"><a id="_16"></a><strong>回执事件</strong></h2>
<p>OPPO PUSH 有如下回执事件</p>
<table>
<thead>
<tr>
<th>回执事件</th>
<th>含义</th>
<th>触发条件</th>
<th>备注</th>
</tr>
</thead>
<tbody>
<tr>
<td>push_arrive</td>
<td>表明消息成功到达设备</td>
<td>消息成功到达设备,OPPO PUSH客户端向服务端反馈已经收到该条消息。</td>
<td>表示消息到达OPPO PUSH客户端，不包含通知栏的展示，点击等后续动作含义。</td>
</tr>
<tr>
<td>regid_invalid</td>
<td>无效RegistrationID</td>
<td>应用被卸载、应用自动注销、用户设备刷机、设备30天内未联网会导致RegistrationID失效</td>
<td>开发者接收到“无效RegistrationID”事件回执后，可以进行剔除或者过滤处理，减少无效推送</td>
</tr>
<tr>
<td>user_daily_limit</td>
<td>单应用单设备限量</td>
<td>当日应用对该设备推送条数超过单设备推送条数限制</td>
<td>开发者当日接收到“单应用单设备限量”事件回执后，可以优化推送策略，减少无效推送。</td>
</tr>
</tbody>
</table>
</div>