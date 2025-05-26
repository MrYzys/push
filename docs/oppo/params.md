# 调用地址，公共参数和返回码

## 请求地址

OPPO PUSH提供以下三个请求URL地址，分别提供国内的消息推送服务，海外消息推送服务，以及国内环境的其他非推送的反馈功能。

| 环境 | HTTPS请求地址 | 备注 |
|------|---------------|------|
| 国内环境 | https://api.push.oppomobile.com/ | 对国内设备推送消息 |
| 海外环境 | https://api-intl.push.oppomobile.com/ | 对海外设备推送消息 |
| 国内环境 | https://feedback.push.oppomobile.com/ | 反馈功能 |

**如何区分国内设备：**

RegistrationID 使用“_”符号分隔成数组。

- 数组大小为1：regId属于国内；如：`b6bbd94b59cdb5df8391642c1509b7fe`
- 数组大小为2：第一个值为“CN”，属于国内；如：`CN_b6bbd94b59cdb5df8391642c1509b7fe`
- 数组大小为3：第二个值为“CN”，属于国内；如：`OPPO_CN_b6bbd94b59cdb5df8391642c1509b7fe`
## 公共参数

公共参数是所有请求都需要携带的参数。

| 名称 | 类型 | 默认 | 描述 |
|------|------|------|------|
| auth_token | String | 必填 | 鉴权令牌<br>auth_token有效期为24小时，过期后无法使用，在HTTP请求体中携带该参数。鉴权令牌通过调用鉴权接口可以获得，详情请参考[鉴权](/new/developmentDoc/info?id=11234)章节。 |
## 返回码

返回码是携带在接口响应的code字段中，范围在（-1-100）的数字，通过返回码可以判断接口调用的结果。表示错误的返回码一般是由于用户的请求不符合调用规范引的。用户遇到这些错误的返回，建议先检查应用的接入权限，推送权限是否正常，以及是否按照参数各式和限制正确设置请求参数。
| Code | 英文描述 | 中文描述 |
|------|----------|----------|
| -2 | Service in Flow Control | 服务器流量控制 |
| -1 | Service Currently Unavailable | 服务不可用，此时请开发者稍候再试 |
| 0 | Success | 成功，表明接口调用成功 |
| 11 | Invalid AuthToken | 不合法的AuthToken |
| 12 | Http Action Not Allowed | HTTP 方法不正确 |
| 13 | App Call Limited | 应用调用次数超限，包含调用频率超限 |
| 14 | Invalid App Key | 无效的AppKey参数 |
| 15 | Missing App Key | 缺少AppKey参数 |
| 16 | Invalid Signature | sign校验不通过，无效签名 |
| 17 | Missing Signature | 缺少签名参数 |
| 18 | Missing Timestamp | 缺少时间戳参数 |
| 19 | Invalid Timestamp | 非法的时间戳参数 |
| 20 | Invalid Method | 不存在的方法名 |
| 21 | Missing Method | 缺少方法名参数 |
| 22 | Missing Version | 缺少版本参数 |
| 23 | Invalid Version | 非法的版本参数，用户传入的版本号格式错误，必需为数字格式 |
| 24 | Unsupported Version | 不支持的版本号，用户传入的版本号没有被提供 |
| 25 | Invalid Encoding | 编码错误，一般是用户做http请求的时候没有用UTF-8编码请求造成的 |
| 26 | IP Black List | IP黑名单 |
| 27 | Access Denied | 没有此功能的权限，拒绝访问 |
| 28 | App Disabled | 应用不可用 |
| 29 | Missing Auth Token | 缺少Auth Token参数 |
| 30 | Api Permission Denied | 该应用没有API推送的权限 |
| 31 | Data Not Exist | 数据不存在 |
| 32 | Data Duplicate | 数据重复 |
| 33 | The number of messages exceeds the daily limit | 消息条数超过日限额 |
| 34 | The number of upload pictures exceeds the daily limit | 上传图片超过日限额 |
| 40 | Missing Required Arguments | 缺少必选参数，API文档中设置为必选的参数是必传的，请仔细核对文档 |
| 41 | Invalid Arguments | 参数错误，一般是用户传入参数非法引起的，请仔细检查入参格式、范围是否一一对应 |
| 51 | Invalid Picture | 图片无效，一般是图片格式、图片分辨率、图片大小不符合格式及图片未上传等，请仔细检查图片格式及上传文件方式 |
| 55 | App Call Frequency Limit | 应用访问频率限制 |
| 59 | UseSecondaryAction permission denied | 无备用链接跳转权限，拒绝访问 |
| 67 | Category error/Private classify channel notify_level invalid | 分类错误（包含强提醒所有异常） |

**业务级错误问题：**

请求后端业务服务器出现的问题，返回的错误码在10000到20000之间，具体业务错误码可参见广播推送、单点推送等接口。每个接口最多10个返回码。