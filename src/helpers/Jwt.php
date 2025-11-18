<?php
namespace Src\Helpers;

class Jwt{
    public static function base64url($d){ 
        return rtrim(strtr(base64_encode($d), '+/', '-_'), '='); 
    }

    public static function sign(array $payload, string $secret, string $alg='HS256'){
        $header=['typ'=>'JWT','alg'=>$alg];
        $h = self::base64url(json_encode($header));
        $p = self::base64url(json_encode($payload));
        $sig = hash_hmac('sha256', implode('.', [$h,$p]), $secret, true);
        $s = self::base64url($sig);
        return implode('.', [$h,$p,$s]);
    }

    public static function verify(string $jwt, string $secret){
        $p = explode('.', $jwt);
        if(count($p)!=3) return null;
        [$h,$b,$s] = $p;
        $chk = self::base64url(hash_hmac('sha256', "$h.$b", $secret, true));
        if(!hash_equals($chk,$s)) return null;
        $pl = json_decode(base64_decode(strtr($b,'-_','+/')), true);
        if(isset($pl['exp']) && time()>$pl['exp']) return null;
        return $pl;
    }
}