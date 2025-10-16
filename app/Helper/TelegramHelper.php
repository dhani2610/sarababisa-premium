<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class TelegramHelper
{
    public static function sendMessage($message)
    {
        $storeSetting = \App\Models\StoreSetting::find(1);
        if ($storeSetting && $storeSetting->token_bot && $storeSetting->chat_id) {
            $botToken = $storeSetting->token_bot;
            $chatId   = $storeSetting->chat_id;
    
            if (!$botToken || !$chatId) {
                \Log::warning('Telegram bot token atau chat_id belum diset di pengaturan toko.');
                return;
            }
    
            try {
                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
            } catch (\Exception $e) {
                \Log::error('Gagal kirim pesan Telegram: ' . $e->getMessage());
            }
        } 
    }
}
