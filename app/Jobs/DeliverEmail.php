<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Email;
use App\Models\EmailRecipient;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

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

        $delivery_exceptions = [];

        foreach ($mail_servers as $mail_server_hostname) {
            try {
                // Try each mail server in order
                $this->deliverMessageToServer(
                    $mail_server_hostname,
                    $email_recipients,
                );

                // Stop once we deliver the message
                return;
            } catch (DeliveryException $exception) {
                $delivery_exceptions[] = $exception;
            }
        }

        // If none of the attempts worked throw an exception
        // throw new SomethingException();
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

    /**
     * Attempts to deliver the message to a mail server
     *
     * @param string $mail_server_hostname The server to connect to
     * @param Collection $email_recipients All recipients @ a domain that uses this server
     *
     * @return void
     */
    private function deliverMessageToServer(string $mail_server_hostname, Collection $email_recipients): void
    {
        $mailer = new PHPMailer(exceptions: true);

        $mailer->SMTPDebug = SMTP::DEBUG_SERVER;
        $mailer->isSMTP();
        $mailer->Host = $this->getPhpMailerHostString($mail_server_hostname);
        $mailer->SMTPAutoTLS = true;
        $mailer->isHTML(true);

        $mailer->Subject = $this->email->subject;
        $mailer->Body = $this->email->message_html;
        $mailer->AltBody = $this->email->message_plain;

        foreach ($email_recipients as $email_recipient) {
            $type = $email_recipient->type;
            $email_address = $email_recipient->recipient->email_address;

            if ($type === "to") {
                $mailer->addAddress($email_address);
            } else if ($type === "cc") {
                $mailer->addCC($email_address);
            } else if ($type === "bcc") {
                $mailer->addBCC($email_address);
            } else {
                // TODO throw new InvalidType???
            }
        }

        foreach ($this->email->emailAttachments as $email_attachment) {
            $attachment = $email_attachment->attachment;

            // TODO 2 \/
        }

        // TODO:
        //  1. Get correct message parts
        //  2. Add attachments, including cid: images inline
        //  3. DKIM sign if we can
        //  4. Store SMTP logs somewhere

        $mailer->send();
    }

    /**
     * Gets a string suitable for the PHPMailer::$Host property.
     *
     * We try all of the common SMTP ports in order to try and improve delivery
     * rates to old/weird server.
     *
     * @param string $mail_server_hostname The hostname to prepare the string for
     *
     * @return string
     */
    private function getPhpMailerHostString(string $mail_server_hostname): string
    {
        return \collect([587, 25, 465, 2525])
            ->map(fn (int $port): string => "{$mail_server_hostname}:{$port}")
            ->join(";");
    }
}
