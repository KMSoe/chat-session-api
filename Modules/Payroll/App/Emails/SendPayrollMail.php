<?php
namespace Modules\Payroll\App\Emails;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Storage\App\Classes\ObjectStorage;
use Modules\Storage\App\Interfaces\StorageInterface;

class SendPayrollMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payroll_data;
    public $payroll;
    public $target_month;
    public $logo;
    public $company;
    public $company_name;
    public StorageInterface $storage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payroll_data, $logo, $company)
    {
        $this->storage      = new ObjectStorage;
        $this->payroll_data = $payroll_data;
        $this->payroll      = $payroll_data['payroll'];
        $this->logo         = $logo;
        $this->company      = $company;
        $this->company_name = $company->display_name;
        $this->target_month = Carbon::parse($payroll_data['payroll']->payroll_month)->format('F Y');
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: "Payroll Slip for " . $this->target_month
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'payroll.payroll-mail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        // $path = $this->payroll->slip->file->path ?? "";

        // if ($path && $this->storage->checkFileExists($path)) {
        //     return [
        //         Attachment::fromStorageDisk('s3', $path),
        //     ];
        // }

        $pdf_content = Pdf::loadView("payroll.pay-slip", $this->payroll_data)->output();
        $employee_name = str_replace(' ', '_', $this->payroll->employee?->name ?? '');

        return [
            Attachment::fromData(fn () => $pdf_content, $employee_name . '-pay-slip.pdf')
                ->withMime('application/pdf'), // Set the MIME type
        ];
    }
}
