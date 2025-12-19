<?php
namespace Modules\CRM\App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Storage\App\Classes\ObjectStorage;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    private $storage;
    private $quotation;
    private $mailInfo;

    /**
     * Create a new message instance.
     */
    public function __construct($quotation, $mailInfo)
    {
        $this->storage   = new ObjectStorage();
        $this->quotation = $quotation;
        $this->mailInfo  = $mailInfo;
    }

    public function build(): self
    {
        return $this->subject($this->mailInfo['subject'])
            ->cc($this->mailInfo['cc'] ?? [])
            ->bcc($this->mailInfo['bcc'] ?? [])
            ->html($this->mailInfo['body']);
    }

    public function attachments()
    {
        $attachments = [];

        foreach ($this->quotation->files as $key => $file) {
            $path = $file->path ?? "";

            if ($path && $this->storage->checkFileExists($path)) {
                $attachments[] = [
                    Attachment::fromStorageDisk('s3', $path),
                ];
            }
        }

        return $attachments;

    }
}
