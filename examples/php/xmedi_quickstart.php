<?php
// 极媒数据 API 快速开始（PHP 8+，需要 cURL 扩展）。
//
// 用法：
//   Windows : set XMEDI_API_KEY=你的Key && php xmedi_quickstart.php
//   macOS   : export XMEDI_API_KEY=你的Key && php xmedi_quickstart.php
//
// 接口文档：https://www.xmedi.cn/docs

declare(strict_types=1);

const BASE_URL = 'https://www.xmedi.cn/api/v1';

function xmedi_request(string $method, string $path, ?array $body, string $apiKey): array
{
    $ch = curl_init(BASE_URL . $path);
    $headers = ['X-XMEDI-Key: ' . $apiKey];
    if ($method === 'POST') {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body ?? new stdClass(), JSON_UNESCAPED_UNICODE));
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $raw = curl_exec($ch);
    if ($raw === false) {
        throw new RuntimeException('curl 请求失败: ' . curl_error($ch));
    }
    curl_close($ch);
    // 业务失败时网关同样返回 JSON（code != 0），失败不扣费
    return json_decode($raw, true) ?? [];
}

function xmedi_get(string $path, string $apiKey): array
{
    return xmedi_request('GET', $path, null, $apiKey);
}

// 调用数据接口。path 形如 /weibo/hot/search，见 openapi/openapi.json
function xmedi_post(string $path, array $body, string $apiKey): array
{
    return xmedi_request('POST', $path, $body, $apiKey);
}

$apiKey = getenv('XMEDI_API_KEY') ?: '';
if ($apiKey === '') {
    fwrite(STDERR, "请先设置环境变量 XMEDI_API_KEY（控制台创建：https://www.xmedi.cn/console）\n");
    exit(1);
}

// 1) 查询余额：免费，先确认 Key 可用
$balance = xmedi_get('/account/balance', $apiKey);
echo "【余额查询】" . json_encode($balance, JSON_UNESCAPED_UNICODE) . PHP_EOL;
if (($balance['code'] ?? -1) !== 0) {
    fwrite(STDERR, "Key 无效，请到控制台检查：https://www.xmedi.cn/console\n");
    exit(1);
}

// 2) 调用数据接口：按次计费，code === 0 才扣费
$result = xmedi_post('/weibo/hot/search', ['page' => 1], $apiKey);
if (($result['code'] ?? -1) === 0) {
    $meta = $result['meta'] ?? [];
    $lineage = '';
    foreach (['observation_id', 'observed_at', 'basis', 'contract_rev', 'origin'] as $key) {
        if (isset($meta[$key]) && $meta[$key] !== '') {
            $lineage .= ' ' . $key . '=' . $meta[$key];
        }
    }
    echo "【微博热搜】成功 cost_fen=" . ($meta['cost_fen'] ?? 0)
        . " balance_fen=" . ($meta['balance_fen'] ?? 0) . $lineage . PHP_EOL;
    echo json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
} else {
    echo "【微博热搜】失败 error_code=" . ($result['error_code'] ?? '')
        . " message=" . ($result['message'] ?? '') . "（本次未扣费）" . PHP_EOL;
}
