<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Email;
use App\Models\EmailRecipient;
use Illuminate\Support\Str;

class DeliverEmail extends Job
{
    /**
     * @param Email $email The email to try and deliver
     */
    public function __construct(private Email $email)
    {
        //
    }

    /**
     * Process the job
     *
     * @return void
     */
    public function handle(): void
    {
        $this->email->loadMissing("emailRecipients.recipient");
        // Only load the linking modes to avoid loading all data in to memory
        $this->email->loadMissing("emailAttachments");

        $grouped_recipients = $this->email->recipients->groupBy(
            fn (EmailRecipient $email_recipient): string => (
                Str::afterLast($email_recipient->recipient->mail_address, "@")
            )
        );

        foreach ($grouped_recipients as $domain_name => $email) {
            $this->deliverMessage($domain_name, $email);
        }
    }

    private function deliverMessage(string $domain_name, Email $email): void
    {
        $mail_servers = $this->getMailServers($domain_name);
    }

    private function getMailServers(string $domain_name): Collection
    {
        \dns_get_mx($domain_name, $hosts, $weights);

        dd($hosts);
    }
}
