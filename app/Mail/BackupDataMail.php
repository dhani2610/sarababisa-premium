<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BackupDataMail extends Mailable
{
    use Queueable, SerializesModels;

    public $moduleName;
    public $startDate;
    public $endDate;
    public $cabangName;
    public $recordCount;
    public $filePath;
    public $fileName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($moduleName, $startDate, $endDate, $cabangName, $recordCount, $filePath, $fileName)
    {
        $this->moduleName = $moduleName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->cabangName = $cabangName;
        $this->recordCount = $recordCount;
        $this->filePath = $filePath;
        $this->fileName = $fileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Backup Data: {$this->moduleName} ({$this->cabangName}) - {$this->startDate} s/d {$this->endDate}")
                    ->view('emails.backup-data')
                    ->attach($this->filePath, [
                        'as' => $this->fileName,
                        'mime' => 'application/sql',
                    ]);
    }
}
