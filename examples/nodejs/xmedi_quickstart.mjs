// 极媒数据 API 快速开始（Node.js 18+，内置 fetch，零依赖）。
//
// 用法：
//   Windows : set XMEDI_API_KEY=你的Key && node xmedi_quickstart.mjs
//   macOS   : export XMEDI_API_KEY=你的Key && node xmedi_quickstart.mjs
//
// 接口文档：https://www.xmedi.cn/docs

const BASE_URL = "https://www.xmedi.cn/api/v1";
const API_KEY = process.env.XMEDI_API_KEY ?? "";

async function xmediGet(path) {
  const resp = await fetch(BASE_URL + path, {
    headers: { "X-XMEDI-Key": API_KEY },
  });
  // 业务失败时网关同样返回 JSON（code !== 0），失败不扣费
  return resp.json();
}

// 调用数据接口。path 形如 /weibo/hot/search，见 openapi/openapi.json
async function xmediPost(path, body) {
  const resp = await fetch(BASE_URL + path, {
    method: "POST",
    headers: {
      "X-XMEDI-Key": API_KEY,
      "Content-Type": "application/json",
    },
    body: JSON.stringify(body),
  });
  return resp.json();
}

async function main() {
  if (!API_KEY) {
    console.error("请先设置环境变量 XMEDI_API_KEY（控制台创建：https://www.xmedi.cn/console）");
    process.exit(1);
  }

  // 1) 查询余额：免费，先确认 Key 可用
  const balance = await xmediGet("/account/balance");
  console.log("【余额查询】", JSON.stringify(balance));
  if (balance.code !== 0) {
    console.error("Key 无效，请到控制台检查：https://www.xmedi.cn/console");
    process.exit(1);
  }

  // 2) 调用数据接口：按次计费，code === 0 才扣费
  const result = await xmediPost("/weibo/hot/search", { page: 1 });
  if (result.code === 0) {
    const meta = result.meta ?? {};
    console.log(`【微博热搜】成功 cost_fen=${meta.cost_fen} balance_fen=${meta.balance_fen}`);
    console.log(JSON.stringify(result.data, null, 2));
  } else {
    console.log(
      `【微博热搜】失败 error_code=${result.error_code} message=${result.message}（本次未扣费）`
    );
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
