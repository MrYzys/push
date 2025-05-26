<div data-v-7b003a97="" id="wikiContent" class="article-content"><h1 id="header-0"><a id="_0"></a><strong>调用地址，公共参数和返回码</strong></h1>
<h2 id="header-1"><a id="_2"></a><strong>请求地址</strong></h2>
<p>OPPO PUSH提供以下三个请求URL地址，分别提供国内的消息推送服务，海外消息推送服务，以及国内环境的其他非推送的反馈功能。</p>
<table>
<thead>
<tr>
<th>环境</th>
<th>HTTPS请求地址</th>
<th>备注</th>
</tr>
</thead>
<tbody>
<tr>
<td>国内环境</td>
<td>https://api.push.oppomobile.com/</td>
<td>对国内设备推送消息</td>
</tr>
<tr>
<td>海外环境</td>
<td>https://api-intl.push.oppomobile.com/</td>
<td>对海外设备推送消息</td>
</tr>
<tr>
<td>国内环境</td>
<td>https://feedback.push.oppomobile.com/</td>
<td>反馈功能</td>
</tr>
</tbody>
</table>
<p><strong>如何区分国内设备：</strong><br>
RegistrationID 使用“_”符号分隔成数组。<br>
数组大小为1：regId属于国内；如：b6bbd94b59cdb5df8391642c1509b7fe<br>
数组大小为2：第一个值为“CN”，属于国内；如：CN_b6bbd94b59cdb5df8391642c1509b7fe<br>
数组大小为3：第二个值为“CN”，属于国内；如：OPPO_CN_b6bbd94b59cdb5df8391642c1509b7fe</p>
<h2 id="header-2"><a id="_16"></a><strong>公共参数</strong></h2>
<p>公共参数是所有请求都需要携带的参数。</p>
<table>
<thead>
<tr>
<th>名称</th>
<th>类型</th>
<th>默认</th>
<th>描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>auth_token</td>
<td>String</td>
<td>必填</td>
<td>鉴权令牌 <br> auth_token有效期为24小时，过期后无法使用，在HTTP请求体中携带该参数。  鉴权令牌通过调用鉴权接口可以获得，详情请参考<a href="/new/developmentDoc/info?id=11234" target="_blank">鉴权</a>章节。</td>
</tr>
</tbody>
</table>
<h2 id="header-3"><a id="_23"></a><strong>返回码</strong></h2>
<p>返回码是携带在接口响应的code字段中，范围在（-1-100）的数字，通过返回码可以判断接口调用的结果。表示错误的返回码一般是由于用户的请求不符合调用规范引的。用户遇到这些错误的返回，建议先检查应用的接入权限，推送权限是否正常，以及是否按照参数各式和限制正确设置请求参数。</p>
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
<td>-2</td>
<td>Service in Flow Control</td>
<td>服务器流量控制</td>
</tr>
<tr>
<td>-1</td>
<td>Service Currently Unavailable</td>
<td>服务不可用，此时请开发者稍候再试</td>
</tr>
<tr>
<td>0</td>
<td>Success</td>
<td>成功，表明接口调用成功</td>
</tr>
<tr>
<td>11</td>
<td>Invalid AuthToken</td>
<td>不合法的AuthToken</td>
</tr>
<tr>
<td>12</td>
<td>Http Action Not Allowed</td>
<td>HTTP 方法不正确</td>
</tr>
<tr>
<td>13</td>
<td>App Call Limited</td>
<td>应用调用次数超限，包含调用频率超限</td>
</tr>
<tr>
<td>14</td>
<td>Invalid App Key</td>
<td>无效的AppKey参数</td>
</tr>
<tr>
<td>15</td>
<td>Missing App Key</td>
<td>缺少AppKey参数</td>
</tr>
<tr>
<td>16</td>
<td>Invalid Signature</td>
<td>sign校验不通过，无效签名</td>
</tr>
<tr>
<td>17</td>
<td>Missing Signature</td>
<td>缺少签名参数</td>
</tr>
<tr>
<td>18</td>
<td>Missing Timestamp</td>
<td>缺少时间戳参数</td>
</tr>
<tr>
<td>19</td>
<td>Invalid Timestamp</td>
<td>非法的时间戳参数</td>
</tr>
<tr>
<td>20</td>
<td>Invalid Method</td>
<td>不存在的方法名</td>
</tr>
<tr>
<td>21</td>
<td>Missing Method</td>
<td>缺少方法名参数</td>
</tr>
<tr>
<td>22</td>
<td>Missing Version</td>
<td>缺少版本参数</td>
</tr>
<tr>
<td>23</td>
<td>Invalid Version</td>
<td>非法的版本参数，用户传入的版本号格式错误，必需为数字格式</td>
</tr>
<tr>
<td>24</td>
<td>Unsupported Version</td>
<td>不支持的版本号，用户传入的版本号没有被提供</td>
</tr>
<tr>
<td>25</td>
<td>Invalid Encoding</td>
<td>编码错误，一般是用户做http请求的时候没有用UTF-8编码请求造成的</td>
</tr>
<tr>
<td>26</td>
<td>IP Black List</td>
<td>IP黑名单</td>
</tr>
<tr>
<td>27</td>
<td>Access Denied</td>
<td>没有此功能的权限，拒绝访问</td>
</tr>
<tr>
<td>28</td>
<td>App Disabled</td>
<td>应用不可用</td>
</tr>
<tr>
<td>29</td>
<td>Missing Auth Token</td>
<td>缺少Auth Token参数</td>
</tr>
<tr>
<td>30</td>
<td>Api Permission Denied</td>
<td>该应用没有API推送的权限</td>
</tr>
<tr>
<td>31</td>
<td>Data Not Exist</td>
<td>数据不存在</td>
</tr>
<tr>
<td>32</td>
<td>Data Duplicate</td>
<td>数据重复</td>
</tr>
<tr>
<td>33</td>
<td>The number of messages exceeds the daily limit</td>
<td>消息条数超过日限额</td>
</tr>
<tr>
<td>34</td>
<td>The number of upload pictures exceeds the daily limit</td>
<td>上传图片超过日限额</td>
</tr>
<tr>
<td>40</td>
<td>Missing Required Arguments</td>
<td>缺少必选参数，API文档中设置为必选的参数是必传的，请仔细核对文档</td>
</tr>
<tr>
<td>41</td>
<td>Invalid Arguments</td>
<td>参数错误，一般是用户传入参数非法引起的，请仔细检查入参格式、范围是否一一对应</td>
</tr>
<tr>
<td>51</td>
<td>Invalid Picture</td>
<td>图片无效，一般是图片格式、图片分辨率、图片大小不符合格式及图片未上传等，请仔细检查图片格式及上传文件方式</td>
</tr>
<tr>
<td>55</td>
<td>App Call Frequency Limit</td>
<td>应用访问频率限制</td>
</tr>
<tr>
<td>59</td>
<td>UseSecondaryAction permission denied</td>
<td>无备用链接跳转权限，拒绝访问</td>
</tr>
<tr>
<td>67</td>
<td>Category error/Private classify channel notify_level invalid</td>
<td>分类错误（包含强提醒所有异常）</td>
</tr>
</tbody>
</table>
<p><strong>业务级错误问题：</strong><br>
请求后端业务服务器出现的问题，返回的错误码在10000到20000之间，具体业务错误码可参见广播推送、单点推送等接口。 每个接口最多10个返回码</p>
</div>