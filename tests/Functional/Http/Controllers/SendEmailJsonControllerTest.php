<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;

/**
 * @covers App\Http\Controllers\SendEmailJsonController
 */
class SendEmailJsonControllerTest extends TestCase
{
    public function testSend(): void
    {
        $domain = $this->getTestUser()
                       ->organisation
                       ->domains
                       ->first();

        $mjml = <<<MJML
            <mjml>
                <mj-body>
                    <mj-section>
                        <mj-column>
                            <mj-text>Hello World</mj-text>
                        </mj-column>
                    </mj-section>
                </mj-body>
            </mjml>
            MJML;

        $file_name = "cool-file.txt";
        $file_data = "cool file data";

        $this->actingAsTestUser()
            ->json("POST", "/emails/json", [
                "to" => [
                    "to@{$domain->name}",
                ],
                "cc" => [
                    "cc@{$domain->name}",
                ],
                "bcc" => [
                    "bcc@{$domain->name}",
                ],
                "from_name" => "From Name",
                "from_email" => "from@{$domain->name}",
                "subject" => "An Interesting Email",
                "message" => [
                    "mjml" => $mjml,
                ],
                "attachments" => [
                    [
                        "name" => $file_name,
                        "data" => \base64_encode($file_data),
                    ]
                ],
            ])
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(201)
            ->seeInDatabase("emails", [
                "from_name" => "From Name",
                "from_email" => "from@{$domain->name}",
                "subject" => "An Interesting Email",
                "message_mjml" => $mjml,
                "message_plain" => "Hello World",
            ])
            ->seeInDatabase("attachments", [
                "name" => $file_name,
                "type" => "text/plain",
                "size" => \strlen($file_data),
                "md5" => \md5($file_data),
                "sha1" => \sha1($file_data),
                "data" => $file_data,
            ])
            ->seeInDatabase("recipients", [
                "email_address" => "to@{$domain->name}",
            ])
            ->seeInDatabase("recipients", [
                "email_address" => "cc@{$domain->name}",
            ])
            ->seeInDatabase("recipients", [
                "email_address" => "bcc@{$domain->name}",
            ]);
    }
}
