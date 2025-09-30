<?php
namespace App;

class TOTP {
    private const PERIOD = 30;
    private const DIGITS = 6;
    
    public static function generateSecret(int $length = 16): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }
    
    public static function getCode(string $secret, ?int $time = null): string {
        if ($time === null) {
            $time = time();
        }
        
        $time = floor($time / self::PERIOD);
        $secret = self::base32Decode($secret);
        
        $time = pack('N*', 0) . pack('N*', $time);
        $hash = hash_hmac('sha1', $time, $secret, true);
        
        $offset = ord($hash[strlen($hash) - 1]) & 0xf;
        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, self::DIGITS);
        
        return str_pad((string)$code, self::DIGITS, '0', STR_PAD_LEFT);
    }
    
    public static function verify(string $secret, string $code, int $window = 1): bool {
        $time = time();
        
        for ($i = -$window; $i <= $window; $i++) {
            $testTime = $time + ($i * self::PERIOD);
            if (self::getCode($secret, $testTime) === $code) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function getQRCodeUrl(string $secret, string $issuer, string $accountName): string {
        $otpauth = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($issuer),
            rawurlencode($accountName),
            $secret,
            rawurlencode($issuer)
        );
        
        return sprintf(
            'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=%s',
            urlencode($otpauth)
        );
    }
    
    private static function base32Decode(string $secret): string {
        $secret = strtoupper($secret);
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        
        for ($i = 0; $i < strlen($secret); $i++) {
            $char = $secret[$i];
            if ($char === '=') {
                break;
            }
            $pos = strpos($chars, $char);
            if ($pos === false) {
                continue;
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        
        $decoded = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $byte = substr($binary, $i, 8);
            if (strlen($byte) === 8) {
                $decoded .= chr(bindec($byte));
            }
        }
        
        return $decoded;
    }
}
