<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Email;
use App\Models\EmailRecipient;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

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
        $this->email->load("emailRecipients.recipient");
        // Only load the linking modes to avoid loading all data in to memory
        $this->email->load("emailAttachments");

        $grouped_recipients = $this->email->emailRecipients->groupBy(
            fn (EmailRecipient $email_recipient): string => (
                Str::afterLast($email_recipient->recipient->email_address, "@")
            )
        );

        foreach ($grouped_recipients as $domain_name => $email_recipients) {
            $this->deliverMessage($domain_name, $email_recipients);
        }
    }

    /**
     * Delivers the message to a list of recipients at a single domain
     *
     * @param string $domain_name The domain name that the recipients are at
     * @param Collection $email_recipients the list of recipient
     *
     * @return void
     */
    private function deliverMessage(string $domain_name, Collection $email_recipients): void
    {
        $mail_servers = $this->getMailServers($domain_name);

        if ($mail_servers->isEmpty()) {
            // TODO Throw something
        }

        foreach ($mail_servers as $mail_server_hostname) {
            // TODO Deliver message
        }
    }

    /**
     * Gets a list of mail servers for a domain name
     *
     * @param string $domain_name The domain name to lookup servers for
     *
     * @return Collection
     */
    private function getMailServers(string $domain_name): Collection
    {
        \dns_get_mx($domain_name, $hosts, $weights);

        // Not really something people actually do but the spec says we should
        // use the domains A / AAAA record if there are no MX servers defined.
        if (\count($hosts) === 0) {
            $weights[] = 0;
            $hosts[] = $domain_name;
        }

        return \collect(\array_combine($weights, $hosts))
            ->sortKeys()
            ->values();
    }
}
