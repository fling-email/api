<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use App\Jobs\DeliverEmail;

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

        $this->createEmailRecipients($to_recipients, "to");
        $this->createEmailRecipients($cc_recipients, "cc");
        $this->createEmailRecipients($bcc_recipients, "bcc");

        \dispatch(new DeliverEmail($this));
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

        Recipient::insert(
            $missing_addresses->map(
                /**
                 * @phan-return array<string, mixed>
                 */
                fn (string $address): array => [
                    "email_address" => $address,
                ]
            )->toArray()
        );

        $new_recipients = Recipient::query()
            ->whereIn("email_address", $missing_addresses)
            ->get();

        return \collect($existing_recipients)
            ->merge(\collect($new_recipients));
    }

    /**
     * Links a list of recipients to this email
     *
     * @param Collection $recipients The list of recipients
     * @param string $type The type of recipient for this message; to, cc or bcc
     *
     * @return void
     */
    private function createEmailRecipients(Collection $recipients, string $type): void
    {
        EmailRecipient::insert(
            $recipients->map(
                /**
                 * @phan-return array<string, mixed>
                 */
                fn (Recipient $recipient): array => [
                    "recipient_id" => $recipient->id,
                    "email_id" => $this->id,
                    "type" => $type,
                ]
            )->toArray()
        );
    }
}
