#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""极媒数据 API 快速开始（Python 3.8+，仅标准库，零依赖）。

用法：
    Windows : set XMEDI_API_KEY=你的Key && python xmedi_quickstart.py
    macOS   : export XMEDI_API_KEY=你的Key && python3 xmedi_quickstart.py

接口文档：https://www.xmedi.cn/docs
"""
import json
import os
import sys
import urllib.error
import urllib.parse
import urllib.request

BASE_URL = "https://www.xmedi.cn/api/v1"
API_KEY = os.environ.get("XMEDI_API_KEY", "")


def _send(req: urllib.request.Request) -> dict:
    """发送请求并解析 JSON。业务失败时网关同样返回 JSON（code != 0），不抛异常。"""
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            return json.loads(resp.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        return json.loads(e.read().decode("utf-8"))


def xmedi_get(path: str, params: dict | None = None) -> dict:
    url = BASE_URL + path
    if params:
        url += "?" + urllib.parse.urlencode(params)
    req = urllib.request.Request(url, headers={"X-XMEDI-Key": API_KEY})
    return _send(req)


def xmedi_post(path: str, body: dict) -> dict:
    """调用数据接口。path 形如 /weibo/hot/search，见 openapi/openapi.json。"""
    req = urllib.request.Request(
        BASE_URL + path,
        data=json.dumps(body, ensure_ascii=False).encode("utf-8"),
        headers={"X-XMEDI-Key": API_KEY, "Content-Type": "application/json"},
        method="POST",
    )
    return _send(req)


def main() -> None:
    if not API_KEY:
        sys.exit("请先设置环境变量 XMEDI_API_KEY（控制台创建：https://www.xmedi.cn/console）")

    # 1) 查询余额：免费，先确认 Key 可用
    balance = xmedi_get("/account/balance")
    print("【余额查询】", json.dumps(balance, ensure_ascii=False))
    if balance.get("code") != 0:
        sys.exit("Key 无效，请到控制台检查：https://www.xmedi.cn/console")

    # 2) 调用数据接口：按次计费，code=0 才扣费，失败不扣费
    result = xmedi_post("/weibo/hot/search", {"page": 1})
    if result.get("code") == 0:
        meta = result.get("meta", {})
        lineage = " ".join(
            f"{key}={meta[key]}"
            for key in ("observation_id", "observed_at", "basis", "contract_rev", "origin")
            if meta.get(key) not in (None, "")
        )
        extra = f" {lineage}" if lineage else ""
        print(f"【微博热搜】成功 cost_fen={meta.get('cost_fen')} balance_fen={meta.get('balance_fen')}{extra}")
        print(json.dumps(result["data"], ensure_ascii=False, indent=2))
    else:
        print(f"【微博热搜】失败 error_code={result.get('error_code')} "
              f"message={result.get('message')}（本次未扣费）")


if __name__ == "__main__":
    main()
