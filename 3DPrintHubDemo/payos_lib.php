<?php
/**
 * payOS REST helper for PHP without Composer (uses official payOS HTTP API).
 * Credentials stored outside Apache document root. Never expose keys in JS/Git.
 * One merchant channel receives funds for ALL vendor orders in this demo.
 */
function payos_secrets(): ?array {
    $path = getenv('THREEDPRINT_PAYOS_CONFIG') ?: dirname(__DIR__, 3).'/etc/3dprinthub-payos.php';
    if (!is_file($path)) return null;
    $cfg = require $path;
    if (!is_array($cfg)) return null;
    foreach (['client_id','api_key','checksum_key','site_url'] as $key) {
        if (empty($cfg[$key]) || str_contains((string)$cfg[$key], 'REPLACE_') || str_contains((string)$cfg[$key], 'YOUR-PUBLIC-DOMAIN')) return null;
    }
    if (!str_starts_with((string)$cfg['site_url'], 'https://')) return null;
    return $cfg;
}
function payos_enabled(): bool {return payos_secrets() !== null;}
function payos_create_payment(array $cfg, int $code, int $amount, string $description, string $returnUrl, string $cancelUrl): array {
    if (!function_exists('curl_init')) throw new RuntimeException('PHP cURL chưa được bật trong Laragon.');
    $sign = 'amount='.$amount.'&cancelUrl='.$cancelUrl.'&description='.$description.'&orderCode='.$code.'&returnUrl='.$returnUrl;
    $body = [
      'orderCode'=>$code,'amount'=>$amount,'description'=>$description,
      'cancelUrl'=>$cancelUrl,'returnUrl'=>$returnUrl,
      'signature'=>hash_hmac('sha256',$sign,$cfg['checksum_key']),
    ];
    $ch = curl_init('https://api-merchant.payos.vn/v2/payment-requests');
    curl_setopt_array($ch,[
      CURLOPT_POST=>true,
      CURLOPT_POSTFIELDS=>json_encode($body, JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE),
      CURLOPT_HTTPHEADER=>[
        'Content-Type: application/json',
        'x-client-id: '.$cfg['client_id'],
        'x-api-key: '.$cfg['api_key'],
      ],
      CURLOPT_RETURNTRANSFER=>true,
      CURLOPT_CONNECTTIMEOUT=>10,
      CURLOPT_TIMEOUT=>25,
      CURLOPT_SSL_VERIFYPEER=>true,
      CURLOPT_FOLLOWLOCATION=>false,
    ]);
    $raw = curl_exec($ch);$http = (int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);curl_close($ch);
    if ($raw===false) throw new RuntimeException('Không thể kết nối payOS: '.$curlErr);
    $res=json_decode($raw,true);
    if ($http!==200 || !is_array($res) || ($res['code']??null)!=='00' || !is_array($res['data']??null)) {
        throw new RuntimeException('payOS từ chối tạo thanh toán (HTTP '.$http.'): '.(string)($res['desc']??'Không rõ nguyên nhân'));
    }
    $link=(string)($res['data']['checkoutUrl']??'');
    $uri=parse_url($link);
    if (($uri['scheme']??'')!=='https' || ($uri['host']??'')!=='pay.payos.vn') {
        throw new RuntimeException('payOS trả về liên kết thanh toán không hợp lệ.');
    }
    return $res['data'];
}
function payos_signed_data(array $data): string {
    ksort($data,SORT_STRING);$pairs=[];
    foreach($data as $key=>$value){
       if ($value===null || $value==='undefined' || $value==='null') $value='';
       elseif(is_array($value)){
          $value=array_map(static function($e){if(is_array($e))ksort($e,SORT_STRING);return $e;},$value);
          $value=json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
       }
       elseif(is_bool($value))$value=$value?'true':'false';
       $pairs[]=$key.'='.$value;
    }
    return implode('&',$pairs);
}
function payos_verify_webhook(array $payload, string $key): bool {
    $data=$payload['data']??null;$sig=$payload['signature']??null;
    if(!is_array($data)||!is_string($sig)||!preg_match('/^[a-fA-F0-9]{64}$/',$sig)) return false;
    $expected=hash_hmac('sha256',payos_signed_data($data),$key);
    return hash_equals($expected,strtolower($sig));
}
