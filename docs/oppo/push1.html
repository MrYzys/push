<div data-v-7b003a97="" id="wikiContent" class="article-content"><h1 id="header-0"><a id="_0"></a><strong>单点推送</strong></h1>
<h2 id="header-1"><a id="_3"></a><strong>单点推送</strong></h2>
<p>单点推送主要用于向一个特定的用户推送同一条消息的场景。一条单点推送消息对应一个推送目标，对应一个消息ID。针对批量的单点推送任务，OPPO PUSH提供批量的单点推送接口可供开发者一次性发起多条单点推送服务。<br>
和广播推送相比，单点推送无需预先保存消息体，而是在调用推送消息接口的同时传入消息内容。<br>
为方便表述，以下部分内容介绍会将单点推送简称为单推。</p>
<h2 id="header-2"><a id="_8"></a><strong>单点推送接口</strong></h2>
<h3 id="header-3"><a id="_9"></a><strong>接口详情</strong></h3>
<table>
<thead>
<tr>
<th>描述</th>
<th>内容</th>
</tr>
</thead>
<tbody>
<tr>
<td>接口功能</td>
<td>发送通知栏消息</td>
</tr>
<tr>
<td>请求方法</td>
<td>POST</td>
</tr>
<tr>
<td>Content-Type</td>
<td>application/x-www-form-urlencoded</td>
</tr>
<tr>
<td>请求编码</td>
<td>UTF-8</td>
</tr>
<tr>
<td>请求路径</td>
<td>/server/v1/message/notification/unicast</td>
</tr>
</tbody>
</table>
<h3 id="header-4"><a id="_19"></a><strong>请求参数</strong></h3>
<p>参数定义：</p>
<table>
<thead>
<tr>
<th>名称</th>
<th>类型</th>
<th>必须</th>
<th>默认</th>
<th>描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>auth_token</td>
<td>string</td>
<td>是</td>
<td>无</td>
<td>鉴权令牌，详见<a href="/new/developmentDoc/info?id=11234" target="_blank">鉴权</a>一章。</td>
</tr>
<tr>
<td>message</td>
<td>String</td>
<td>是</td>
<td>无</td>
<td>消息体参数，接受一个嵌套的JSON结构，推送目标和具体消息内容均在这个字段中配置。</td>
</tr>
</tbody>
</table>
<br>
<p>message字段是一个JSON结构参数，接受的字段及含义如下：</p>
<table>
<thead>
<tr>
<th>名称</th>
<th>类型</th>
<th>必须</th>
<th>默认</th>
<th>描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>target_type</td>
<td>Short</td>
<td>是</td>
<td>无</td>
<td>推送的目标类型  2: registration_id  5: 别名</td>
</tr>
<tr>
<td>target_value</td>
<td>String</td>
<td>是</td>
<td>无</td>
<td>推送目标，按taget_type对应填入，仅接受一个值。</td>
</tr>
<tr>
<td>notification</td>
<td>JSON</td>
<td>是</td>
<td>无</td>
<td>通知栏消息内容，参考<a href="/new/developmentDoc/info?id=11236" target="_blank">消息体内容定义</a>,按照JSON格式填入</td>
</tr>
<tr>
<td>verify_registration_id</td>
<td>Boolean</td>
<td>否</td>
<td>FALSE</td>
<td>消息到达客户端后是否校验registration_id。  true表示推送目标与客户端registration_id进行比较，如果一致则继续展示，不一致则就丢弃；false表示不校验</td>
</tr>
</tbody>
</table>
<p>message示范：</p>
<pre><code class="lang- hljs language-swift">{
		<span class="hljs-string">"target_type"</span>: <span class="hljs-number">2</span>,
		<span class="hljs-string">"target_value"</span>: <span class="hljs-string">"CN_078ad8b8a137cadb14ae5b70cf846312"</span>,
		<span class="hljs-string">"verify_registration_id"</span>: <span class="hljs-literal">true</span>,
        <span class="hljs-comment">// notification是一个嵌套在message的json，配置通知栏消息内容和参数</span>
		<span class="hljs-string">"notification"</span>: {
				<span class="hljs-string">"appMessageId"</span>: null,
				<span class="hljs-string">"style"</span>: <span class="hljs-number">1</span>,
				<span class="hljs-string">"bigPictureId"</span>: null,
				<span class="hljs-string">"smallPictureId"</span>: null,
				<span class="hljs-string">"title"</span>: <span class="hljs-string">"您的订单已取消"</span>,
				<span class="hljs-string">"subTitle"</span>: null,
				<span class="hljs-string">"content"</span>: <span class="hljs-string">"您的订单(realme X7…) 已取消"</span>,
				<span class="hljs-string">"clickActionType"</span>: <span class="hljs-number">4</span>,
				<span class="hljs-string">"clickActionActivity"</span>: <span class="hljs-string">"com.realme.store.home.view.MainActivity"</span>,
				<span class="hljs-string">"clickActionUrl"</span>: null,
				<span class="hljs-string">"actionParameters"</span>: <span class="hljs-string">"{<span class="hljs-subst">\"</span>messageNo<span class="hljs-subst">\"</span>:null,<span class="hljs-subst">\"</span>title<span class="hljs-subst">\"</span>:<span class="hljs-subst">\"</span>您的订单已取消<span class="hljs-subst">\"</span>,<span class="hljs-subst">\"</span>desc<span class="hljs-subst">\"</span>:<span class="hljs-subst">\"</span>您的订单(realme X7…) 				已取消<span class="hljs-subst">\"</span>,<span class="hljs-subst">\"</span>type<span class="hljs-subst">\"</span>:null,<span class="hljs-subst">\"</span>redirectType<span class="hljs-subst">\"</span>:2,<span class="hljs-subst">\"</span>resource<span class="hljs-subst">\"</span>:<span class="hljs-subst">\"</span>1<span class="hljs-subst">\"</span>,<span class="hljs-subst">\"</span>version<span class="hljs-subst">\"</span>:1}"</span>,
				<span class="hljs-string">"showTimeType"</span>: <span class="hljs-number">0</span>,
				<span class="hljs-string">"showStartTime"</span>: null,
				<span class="hljs-string">"showEndTime"</span>: null,
				<span class="hljs-string">"offLine"</span>: <span class="hljs-literal">true</span>,
				<span class="hljs-string">"offLineTtl"</span>: <span class="hljs-number">86400</span>,
				<span class="hljs-string">"pushTimeType"</span>: <span class="hljs-number">0</span>,
				<span class="hljs-string">"pushStartTime"</span>: null,
				<span class="hljs-string">"timeZone"</span>: null,
				<span class="hljs-string">"fixSpeed"</span>: null,
				<span class="hljs-string">"fixSpeedRate"</span>: null,
				<span class="hljs-string">"networkType"</span>: null,
				<span class="hljs-string">"callBackUrl"</span>: null,
				<span class="hljs-string">"callBackParameter"</span>: null,
				<span class="hljs-string">"channelId"</span>: <span class="hljs-string">"message"</span>,
				<span class="hljs-string">"showTtl"</span>: <span class="hljs-number">0</span>,
				<span class="hljs-string">"notifyId"</span>: <span class="hljs-number">1</span>
				}
}

</code><i class="code-copy el-icon-document-copy code-dark"></i><div class="theme el-icon-sunny code-dark"></div></pre>
<h3 id="header-5"><a id="JSON_76"></a><strong>响应参数（JSON）</strong></h3>
<table>
<thead>
<tr>
<th>名称</th>
<th>类型</th>
<th>必须</th>
<th>描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>code</td>
<td>Int</td>
<td>是</td>
<td>返回码,请参考公共返回码与接口返回码</td>
</tr>
<tr>
<td>message</td>
<td>String</td>
<td>否</td>
<td>请求响应信息</td>
</tr>
<tr>
<td>data</td>
<td>String</td>
<td>否</td>
<td>响应返回值，正确响应后包含消息ID</td>
</tr>
</tbody>
</table>
<p>推送请求调用成功响应示例：</p>
<pre><code class="lang- hljs language-css">{
    <span class="hljs-selector-tag">code</span>:<span class="hljs-number">0</span>,
    message: <span class="hljs-string">""</span>,
    data : {
       // 消息ID 格式为 AppID-<span class="hljs-number">1</span>-<span class="hljs-number">1</span>-全局唯一性ID
       messageId : ZngnvJIM7wQusNtbqYnpH6XX-<span class="hljs-number">1</span>-<span class="hljs-number">1</span>-<span class="hljs-number">622</span>ff7cfae27668d2ed3afec,
       } 
}
</code><i class="code-copy el-icon-document-copy code-dark"></i><div class="theme el-icon-sunny code-dark"></div></pre>
<p>推送目标存在问题响应示例：<br>
推送请求调用失败，但推送目标存在问题</p>
<pre><code class="lang- hljs language-markdown">{
<span class="hljs-code">    code:10000,
    message: "registration_id格式不正确
}
</span></code><i class="code-copy el-icon-document-copy code-dark"></i><div class="theme el-icon-sunny code-dark"></div></pre>
<h3 id="header-6"><a id="code_105"></a><strong>返回码（code）</strong></h3>
<table>
<thead>
<tr>
<th>Code</th>
<th>英文描述</th>
<th>中文描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>10000</td>
<td>Invalid Registration_id</td>
<td>registration_id格式不正确</td>
</tr>
</tbody>
</table>
<h2 id="header-7"><a id="_111"></a><strong>批量单推接口</strong></h2>
<h3 id="header-8"><a id="_112"></a><strong>接口</strong></h3>
<table>
<thead>
<tr>
<th>描述</th>
<th>内容</th>
</tr>
</thead>
<tbody>
<tr>
<td>接口功能</td>
<td>批量发送单推通知栏消息</td>
</tr>
<tr>
<td>请求方法</td>
<td>POST</td>
</tr>
<tr>
<td>Content-Type</td>
<td>application/x-www-form-urlencoded</td>
</tr>
<tr>
<td>请求编码</td>
<td>UTF-8</td>
</tr>
<tr>
<td>请求路径</td>
<td>/server/v1/message/notification/unicast_batch</td>
</tr>
</tbody>
</table>
<h3 id="header-9"><a id="_122"></a><strong>请求参数</strong></h3>
<p>和推送单条消息的单推接口相比，批量单推接口使用messages字段容纳不少于一条的消息推送参数。messages字段是一个JSON数组，里面的一个元素和单推接口参数中的message相同。<br>
以下是参数定义：</p>
<table>
<thead>
<tr>
<th>名称</th>
<th>类型</th>
<th>必须</th>
<th>默认</th>
<th>描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>auth_token</td>
<td>string</td>
<td>是</td>
<td>无</td>
<td>鉴权令牌，详见<a href="/new/developmentDoc/info?id=11234" target="_blank">鉴权</a>一章。</td>
</tr>
<tr>
<td>messages</td>
<td>String</td>
<td>是</td>
<td>无</td>
<td>通知栏消息JSON数组字符串,示例：[{message},{message}]【最多1000个】</td>
</tr>
</tbody>
</table>
<p>message：</p>
<table>
<thead>
<tr>
<th>名称</th>
<th>类型</th>
<th>必须</th>
<th>默认</th>
<th>描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>target_type</td>
<td>Short</td>
<td>是</td>
<td>无</td>
<td>目标类型  2: registration_id ，  5:别名</td>
</tr>
<tr>
<td>target_value</td>
<td>String</td>
<td>是</td>
<td>无</td>
<td>推送目标用户:  registration_id或alias</td>
</tr>
<tr>
<td>notification</td>
<td>JSON</td>
<td>是</td>
<td>无</td>
<td>通知栏消息内容，参考<a href="/new/developmentDoc/info?id=11236" target="_blank">消息体内容定义</a>,按照JSON格式填入</td>
</tr>
<tr>
<td>verify_registration_id</td>
<td>Boolean</td>
<td>否</td>
<td>FALSE</td>
<td>消息到达客户端后是否校验registration_id。  true表示推送目标与客户端registration_id进行比较，如果一致则继续展示，不一致则就丢弃；false表示不校验</td>
</tr>
</tbody>
</table>
<p>一个批量单推接口的messages参数范例</p>
<pre><code class="lang- hljs language-json"><span class="hljs-punctuation">[</span>
	<span class="hljs-comment">// 传入两个推送目标和推送内容，将会产生两条单推推送消息</span>
	<span class="hljs-punctuation">{</span>
		<span class="hljs-attr">"target_type"</span><span class="hljs-punctuation">:</span><span class="hljs-number">2</span><span class="hljs-punctuation">,</span>
		<span class="hljs-attr">"target_value"</span><span class="hljs-punctuation">:</span><span class="hljs-string">"CN_906bf7b0d76a5c0001668ddc410ab903"</span><span class="hljs-punctuation">,</span>
		<span class="hljs-attr">"notification"</span><span class="hljs-punctuation">:</span><span class="hljs-punctuation">{</span>
			<span class="hljs-attr">"title"</span><span class="hljs-punctuation">:</span><span class="hljs-string">"Hello World-1"</span><span class="hljs-punctuation">,</span>
			<span class="hljs-attr">"content"</span><span class="hljs-punctuation">:</span><span class="hljs-string">"Hello World message-1"</span>
		<span class="hljs-punctuation">}</span>
	<span class="hljs-punctuation">}</span><span class="hljs-punctuation">,</span>
	<span class="hljs-punctuation">{</span>
		<span class="hljs-attr">"target_type"</span><span class="hljs-punctuation">:</span><span class="hljs-number">2</span><span class="hljs-punctuation">,</span>
		<span class="hljs-attr">"target_value"</span><span class="hljs-punctuation">:</span><span class="hljs-string">"CN_b457331fd7874d0200c595ebb8d39de8"</span><span class="hljs-punctuation">,</span>
		<span class="hljs-attr">"notification"</span><span class="hljs-punctuation">:</span><span class="hljs-punctuation">{</span>
			<span class="hljs-attr">"title"</span><span class="hljs-punctuation">:</span><span class="hljs-string">"Hello World-2"</span><span class="hljs-punctuation">,</span>
            <span class="hljs-attr">"content"</span><span class="hljs-punctuation">:</span><span class="hljs-string">"Hello World message-2"</span>
        <span class="hljs-punctuation">}</span>
<span class="hljs-punctuation">]</span>
</code><i class="code-copy el-icon-document-copy code-dark"></i><div class="theme el-icon-sunny code-dark"></div></pre>
<h3 id="header-10"><a id="JSON_161"></a><strong>响应参数（JSON）</strong></h3>
<table>
<thead>
<tr>
<th>名称</th>
<th>类型</th>
<th>必须</th>
<th>描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>code</td>
<td>Int</td>
<td>是</td>
<td>返回码,请参考公共返回码与接口返回码</td>
</tr>
<tr>
<td>message</td>
<td>String</td>
<td>否</td>
<td>请求响应信息</td>
</tr>
<tr>
<td>data</td>
<td>String</td>
<td>否</td>
<td>返回值，JSON类型</td>
</tr>
</tbody>
</table>
<p>推送请求调用成功响应示例：</p>
<pre><code class="lang- hljs language-kotlin">{
     code: <span class="hljs-number">0</span>,
     message: <span class="hljs-string">""</span>,
     <span class="hljs-keyword">data</span>: [
         {
               messageId: xxxxxxxxx, <span class="hljs-comment">// 消息Id</span>
               registrationId： xxxxxxxxx
        }, {
               messageId: xxxxxxxxx, <span class="hljs-comment">// 消息Id</span>
               registrationId： xxxxxxxxx
       }, {
               messageId: xxxxxxxxx, <span class="hljs-comment">// 消息Id</span>
               registrationId： xxxxxxxxx, <span class="hljs-comment">// 消息Id</span>
               errorCode: <span class="hljs-number">10000</span>, <span class="hljs-comment">// 失败码</span>
               errorMessage: xxxx <span class="hljs-comment">// 失败说明</span>
       }
    ]
}
</code><i class="code-copy el-icon-document-copy code-dark"></i><div class="theme el-icon-sunny code-dark"></div></pre>
<h3 id="header-11"><a id="code_190"></a><strong>返回码（code）</strong></h3>
<table>
<thead>
<tr>
<th>Code</th>
<th>英文描述</th>
<th>中文描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>10000</td>
<td>Invalid Registration_id</td>
<td>registration_id格式不正确</td>
</tr>
</tbody>
</table>
</div>