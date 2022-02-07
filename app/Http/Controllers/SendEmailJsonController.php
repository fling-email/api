<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Models\Email;
use App\Models\Domain;
use App\Models\Attachment;
use App\Models\EmailAttachment;
use App\Traits\CompilesMjml;
use PHPMailer\PHPMailer\PHPMailer;

class SendEmailJsonController extends Controller
{
    use CompilesMjml;

    public static ?string $method = "post";
    public static ?string $path = "/emails/json";

    /**
     * Handles requests to queue new emails for delivery
     *
     * @return JsonResponse|Response
     */
    public function __invoke(): JsonResponse|Response
    {
        $from_domain_name = Str::after($this->request->json("from_email"), "@");

        $from_domain = Domain::query()
            ->where("name", $from_domain_name)
            ->first();

        $this->authorize("sendEmail", [Domain::class, $from_domain]);

        $email_html = $this->getEmailHtml();
        $email_plain = $this->request->json(
            "message.plain",
            $this->convertToPlain($email_html),
        );

        $to_addresses = \collect($this->request->json("to", []));
        $cc_addresses = \collect($this->request->json("cc", []));
        $bcc_addresses = \collect($this->request->json("bcc", []));

        $email = new Email();

        $email->from_name = $this->request->json("from_name");
        $email->from_email = $this->request->json("from_email");
        $email->subject = $this->request->json("subject");
        $email->message_plain = $email_plain;
        $email->message_html = $email_html;
        $email->message_mjml = $this->request->json("message.mjml", null);

        $email->save();
        $email->refresh();

        foreach ($this->request->json("attachments", []) as $attachment_input) {
            \settype($attachment_input, "object");

            $attachment = new Attachment();

            $raw_data = \base64_decode($attachment_input->data);

            $finfo = new \finfo(\FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file(
                "data:text/plain;base64," . \base64_encode($raw_data),
                \FILEINFO_MIME_TYPE,
            );

            $attachment->name = $attachment_input->name;
            $attachment->type = $mime_type ?? "application/octet-stream";
            $attachment->size = \strlen($raw_data);
            $attachment->md5 = \md5($raw_data);
            $attachment->sha1 = \sha1($raw_data);
            $attachment->data = $raw_data;

            $attachment->save();

            $email_attachment = new EmailAttachment();

            $email_attachment->email_id = $email->id;
            $email_attachment->attachment_id = $attachment->id;

            $email_attachment->save();
        }

        $email->sendTo($to_addresses, $cc_addresses, $bcc_addresses);

        return \response("", 201);
    }

    /**
     * Gets the HTML email from the input, either directly or by compiling mjml
     *
     * @return string
     */
    private function getEmailHtml(): string
    {
        $input_html = $this->request->json("message.html");
        $input_mjml = $this->request->json("message.mjml");

        return $input_html ?? $this->compileMjml($input_mjml);
    }

    /**
     * Converts a HTML email to plain text
     *
     * @param string $html The input HTML
     *
     * @return string
     */
    private function convertToPlain(string $html): string
    {
        return (new PHPMailer())->html2text($html);
    }
}
