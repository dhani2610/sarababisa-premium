<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleTelegramBot extends Component
{
    public $token_bot;
    public $chat_id;
    public $fonnte;

    public function mount()
    {
        $setting = StoreSetting::where('cabang_id',getCabangId())->first();
        $this->token_bot = $setting->token_bot;
        $this->chat_id = $setting->chat_id;
        $this->report_time = $setting->report_time;
        $this->fonnte = $setting->fonnte;
    }

    public function render()
    {
        return view('livewire.toggle-telegram-bot');
    }

    public function saveSetting()
    {
        $setting = StoreSetting::where('cabang_id',getCabangId())->first();
        $setting->update([
            'token_bot' => $this->token_bot,
            'chat_id' => $this->chat_id,
            'report_time' => $this->report_time ?: null,
            'fonnte' => $this->fonnte ?: null,
        ]);

        session()->flash('success', 'data berhasil diperbarui.');
        return redirect()->route('sistem');
    }
}
