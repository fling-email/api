<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use Tests\TestCase;
use App\Traits\CompilesMjml;

/**
 * @covers App\Traits\CompilesMjml
 */
class CompilesMjmlTest extends TestCase
{
    private function compileMjmlString(string $input): string
    {
        $compiler = new class () {
            use CompilesMjml;

            public function compile(string $mjml): string
            {
                return $this->compileMjml($mjml);
            }
        };

        return $compiler->compile($input);
    }

    public function testCompile(): void
    {
        $mjml = <<<MJML
            <mjml>
                <mj-body>
                    <mj-section>
                        <mj-column>
                            <mj-text>
                                Valid MJML :)
                            </mj-text>
                        </mj-column>
                    </mj-section>
                </mj-body>
            </mjml>
            MJML;

        $result = $this->compileMjmlString($mjml);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);

        // Should have basic HTML tags
        $this->assertStringContainsString("<html ", $result);
        $this->assertStringContainsString("</html>", $result);
        $this->assertStringContainsString("<body ", $result);
        $this->assertStringContainsString("</body>", $result);
        $this->assertStringContainsString("<head>", $result);
        $this->assertStringContainsString("</head>", $result);

        // Should also have base mjml things
        $this->assertStringContainsString("<o:OfficeDocumentSettings>", $result);
        $this->assertStringContainsString("#outlook a {", $result);
        $this->assertStringContainsString(".mj-outlook-group-fix", $result);

        // Should also have the expected text
        $this->assertStringContainsString("Valid MJML :)", $result);
    }
}
