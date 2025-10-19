<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;

class ToggleTelegramBot extends Component
{
    public $token_bot;
    public $chat_id;
    public $report_time;

    public function mount()
    {
        $setting = StoreSetting::find(1);
        $this->token_bot = $setting->token_bot;
        $this->chat_id = $setting->chat_id;
        $this->report_time = $setting->report_time;
    }

    public function render()
    {
        return view('livewire.toggle-telegram-bot');
    }

    public function saveSetting()
    {
        $setting = StoreSetting::find(1);
        $setting->update([
            'token_bot' => $this->token_bot,
            'chat_id' => $this->chat_id,
            'report_time' => $this->report_time ?: null,
        ]);

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Token Bot dan Chat ID berhasil diperbarui.'
        ]);
    }
}
