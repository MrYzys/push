<div data-v-7b003a97="" id="wikiContent" class="article-content"><h1 id="header-0"><a id="_0"></a><strong>鉴权</strong></h1>
<p>开发者通过OPPO PUSH服务端的鉴权接口验证合法身份，并获得权限令牌。其他所有的API请求都需要在HTTP body中携带auth_token字段以进行合法的服务调用。为保证安全性，令牌具有一定的时效性，因此调用者也需要定时更新自己的权限令牌。本文后续接口默认都认为已携带该参数。</p>
<h2 id="header-1"><a id="_4"></a><strong>鉴权接口</strong></h2>
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
<td>开发者身份鉴权，获得令牌。</td>
</tr>
<tr>
<td>请求方法</td>
<td>POST</td>
</tr>
<tr>
<td>请求编码</td>
<td>UTF-8</td>
</tr>
<tr>
<td>Content-Type</td>
<td>application/x-www-form-urlencoded</td>
</tr>
<tr>
<td>请求路径</td>
<td>/server/v1/auth</td>
</tr>
</tbody>
</table>
<h2 id="header-2"><a id="_14"></a><strong>请求参数</strong></h2>
<p>以下参数均在HTTP body中携带</p>
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
<td>app_key</td>
<td>String</td>
<td>必填</td>
<td>OPPO PUSH发放给合法应用的AppKey。</td>
</tr>
<tr>
<td>sign</td>
<td>String</td>
<td>必填</td>
<td>加密签名。<br>  是用AppKey、当前时间戳毫秒数、MasterSecret拼接而成的字符串并用SHA256加密而成的字符串。 <br> MasterSecret是注册应用时OPPO PUSH发放的服务端密钥，与AppKey对应</td>
</tr>
<tr>
<td>timestamp</td>
<td>Long</td>
<td>必填</td>
<td>当前时间的unix时间戳。<br>  格式为13位时间毫秒数，时区采用GMT+8。<br>  需要使用最近10分钟内的时间戳，否则会导致鉴权失败</td>
</tr>
</tbody>
</table>
<h2 id="header-3"><a id="_23"></a><strong>请求响应</strong></h2>
<p>返回结果携带在HTTP响应的body中，整个body内容是JSON格式。</p>
<table>
<thead>
<tr>
<th>名称</th>
<th>类型</th>
<th>描述</th>
</tr>
</thead>
<tbody>
<tr>
<td>code</td>
<td>Int</td>
<td>返回码,具体含义请参考返回码描述（增加超链接）</td>
</tr>
<tr>
<td>message</td>
<td>String</td>
<td>请求响应结果的文字描述</td>
</tr>
<tr>
<td>data</td>
<td>JSON</td>
<td>返回值，JSON类型，包含了具体的鉴权结果</td>
</tr>
</tbody>
</table>
<p>响应示例：</p>
<pre><code class="lang- hljs language-json"><span class="hljs-punctuation">{</span>
    <span class="hljs-attr">"code"</span><span class="hljs-punctuation">:</span> <span class="hljs-number">0</span><span class="hljs-punctuation">,</span>
    <span class="hljs-attr">"message"</span><span class="hljs-punctuation">:</span> <span class="hljs-string">"success"</span><span class="hljs-punctuation">,</span>
    <span class="hljs-attr">"data"</span><span class="hljs-punctuation">:</span> <span class="hljs-punctuation">{</span>
        <span class="hljs-comment">//权限令牌，推送消息时，需要提供auth_token，有效期默认为24小时，过期后无法使用</span>
        <span class="hljs-attr">"auth_token"</span><span class="hljs-punctuation">:</span> <span class="hljs-string">"58ad47319e8d725350a5afd5"</span> 
         <span class="hljs-attr">"create_time"</span><span class="hljs-punctuation">:</span> <span class="hljs-string">"时间毫秒数"</span>
      <span class="hljs-punctuation">}</span>
<span class="hljs-punctuation">}</span>
</code><i class="code-copy el-icon-document-copy code-dark"></i><div class="theme el-icon-sunny code-dark"></div></pre>
</div>