<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Collection;

class Email extends Model
{
    /**
     * Queues this email for delivery to the specified addresses
     *
     * @param Collection $to_addresses List of addresses for the To: header
     * @param Collection $cc_addresses List of addresses for the CC: header
     * @param Collection $bcc_addresses List of addresses for the BCC: header
     *
     * @return void
     */
    public function sendTo(
        Collection $to_addresses,
        Collection $cc_addresses,
        Collection $bcc_addresses,
    ): void {
        $to_recipients = $this->getOrCreateMissingRecipients($to_addresses);
        $cc_recipients = $this->getOrCreateMissingRecipients($cc_addresses);
        $bcc_recipients = $this->getOrCreateMissingRecipients($bcc_addresses);

        $this->createEmailRecipients($to_addresses, "to");
        $this->createEmailRecipients($cc_addresses, "cc");
        $this->createEmailRecipients($bcc_addresses, "bcc");

        // TODO Attachments + queue sending
    }

    /**
     * Gets a list of recipients from a list of addresses, creating new ones if
     * required.
     *
     * @param Collection $addresses The list of email addresses
     *
     * @return Collection
     */
    private function getOrCreateMissingRecipients(Collection $addresses): Collection
    {
        $existing_recipients = Recipient::query()
            ->whereIn("email_address", $addresses)
            ->get();

        $existing_addresses = $existing_recipients->map(
            fn (Recipient $recipient): string => $recipient->email_address
        );

        $missing_addresses = $addresses->diff($existing_addresses);

        $new_recipients = Recipient::create(
            $missing_addresses->map(
                fn (string $address): array => [
                    "email_address" => $address,
                ]
            )
        );

        return \collect($existing_recipients)
            ->merge(\collect($new_recipients));
    }

    /**
     * Links a list of recipients to this email
     *
     * @param Collection $recipients The list of recipients
     * @param string The type of recipient for this message; to, cc or bcc
     *
     * @return void
     */
    private function createEmailRecipients(Collection $recipients, string $type): void
    {
        EmailRecipient::create(
            $to_addresses->map(
                fn (Recipient $recipient): array => [
                    "recipient_id" => $recipient->id,
                    "email_id" => $this->id,
                    "type" => $type,
                ]
            )
        );
    }
}
