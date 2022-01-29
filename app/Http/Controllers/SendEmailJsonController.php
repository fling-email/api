<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Models\Email;
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
        $email_html = $this->getEmailHtml();
        $email_plain = $this->request->json(
            "message.plain",
            $this->convertToPlain($email_html),
        );

        return \response("", 204);
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
