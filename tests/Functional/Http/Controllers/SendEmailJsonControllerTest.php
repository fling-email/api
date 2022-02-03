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
        $this->actingAsTestUser()
            ->json("POST", "/emails/json", [
                "to" => [
                    "to@fling.email",
                ],
                "cc" => [
                    "cc@fling.email",
                ],
                "bcc" => [
                    "bcc@fling.email",
                ],
                "from_name" => "From Name",
                "from_email" => "from@fling.email",
                "subject" => "An Interesting Email",
                "message" => [
                    "mjml" => "<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>",
                ],
                "attachments" => [
                    [
                        "name" => "cool-file.txt",
                        "data" => \base64_encode("cool file data"),
                    ]
                ],
            ])
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(201);
    }
}
