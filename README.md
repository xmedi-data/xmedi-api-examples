# 极媒数据 API 调用示例

[极媒数据（Xmedi Data）](https://www.xmedi.cn/)是面向开发者与企业的新媒体数据服务平台，提供微信公众号、视频号、抖音、小红书、快手、微博、B 站等主流平台的数据 API，以及 TikTok、YouTube 数据接口、账号动态推送和企业级数据解决方案。

平台支持统一 API 接入、标准 JSON 响应、按次计费，调用成功才扣费。适用于新媒体舆情监测、竞品分析、矩阵账号管理、企业数据看板、BI、知识库和智能体。同时提供企业级数据定制、数据湖和私有化对接。

本仓库提供可直接运行的调用示例与接口描述文件。

- 官网：<https://www.xmedi.cn/>
- 接口文档：<https://www.xmedi.cn/docs>
- 接口目录与在线调试：<https://www.xmedi.cn/docs/apis>
- OpenAPI（在线版，以官网为准）：<https://www.xmedi.cn/api/v1/openapi.json>
- Apifox 文档站：<https://xmedi.apifox.cn/>

## 仓库结构

| 路径 | 内容 |
|------|------|
| `examples/python/` | Python 调用示例（仅标准库，零依赖） |
| `examples/nodejs/` | Node.js 示例（Node 18+，使用内置 fetch，零依赖） |
| `examples/php/` | PHP 示例（cURL 扩展） |
| `postman/` | Postman Collection，导入后填入 Key 即可调试全部接口 |
| `openapi/` | OpenAPI 3.0 快照；线上实时版以官网 `openapi.json` 为准 |

## 快速开始

1. 注册账号：<https://www.xmedi.cn/register>
2. 在控制台创建 API Key：<https://www.xmedi.cn/console>
3. 把 Key 写入环境变量：

```bash
# Windows (cmd / PowerShell)
set XMEDI_API_KEY=你的Key

# macOS / Linux
export XMEDI_API_KEY=你的Key
```

4. 任选一语言运行示例：

```bash
python examples/python/xmedi_quickstart.py   # Python 3.8+
node   examples/nodejs/xmedi_quickstart.mjs  # Node.js 18+
php    examples/php/xmedi_quickstart.php     # PHP 8+（cURL）
```

## 调用约定

| 项 | 值 |
|----|----|
| Base URL | `https://www.xmedi.cn/api/v1` |
| 鉴权 | 请求头 `X-XMEDI-Key: <你的 Key>`，同时兼容 `Authorization: Bearer <Key>` |
| 数据接口 | 一律 `POST`，请求体为 JSON 对象 |
| 计费 | 按次计费，只有 `code = 0`（调用成功）才扣费；参数错误、余额不足、限流、上游失败均不扣费 |
| 余额查询 | `GET /api/v1/account/balance`，免费 |
| 产品目录 | `GET /api/v1/catalog`，免费，返回全部在售接口、单价与限流 |

### 响应结构

成功：

```json
{
  "code": 0,
  "message": "ok",
  "data": {},
  "meta": { "request_id": "req_xxx", "cost_fen": 15, "balance_fen": 985 }
}
```

失败（不扣费）：

```json
{
  "code": 40100,
  "error_code": "UNAUTHORIZED",
  "message": "API Key 无效",
  "data": {},
  "meta": { "request_id": "req_xxx", "cost_fen": 0, "balance_fen": 0 }
}
```

### 错误码

| error_code | HTTP | code | 说明 |
|-----------|------|------|------|
| `VALIDATION_ERROR` | 400 | 40001 | 参数校验失败 |
| `UNAUTHORIZED` | 401 | 40100 | API Key 无效 |
| `INSUFFICIENT_BALANCE` | 402 | 40200 | 余额不足，请充值 |
| `NOT_FOUND` | 404 | 40400 | 接口不存在 |
| `RATE_LIMITED` | 429 | 42900 | 请求过于频繁 |
| `INTERNAL_ERROR` | 500 | 50000 | 接口异常 |
| `NOT_AVAILABLE` | 503 | 50300 | 接口暂不可用 |

## curl 示例

```bash
# 查询余额（免费）
curl "https://www.xmedi.cn/api/v1/account/balance" \
  -H "X-XMEDI-Key: $XMEDI_API_KEY"

# 微博热搜（按次计费，成功才扣费）
curl -X POST "https://www.xmedi.cn/api/v1/weibo/hot/search" \
  -H "X-XMEDI-Key: $XMEDI_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"page":1}'

# 公众号文章详情（传文章 URL）
curl -X POST "https://www.xmedi.cn/api/v1/wechat/articles/detail" \
  -H "X-XMEDI-Key: $XMEDI_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"url":"http://mp.weixin.qq.com/s?__biz=MjM5MjAxNDM4MA==&mid=2667026902&idx=1&sn=625cd9b1604b0968a3dcb98380a931ff"}'
```

## Postman

1. 导入 `postman/xmedi-data-api.postman_collection.json`
2. 打开集合变量，把 `api_key` 改成你在控制台创建的 Key
3. 各分组下的请求均已按 OpenAPI 填好示例参数，直接发送即可

## License

[MIT](./LICENSE)
