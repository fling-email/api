<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tests\TestCase;
use App\Utils\GeneratesDkimKeys;
use betterphp\utils\reflection;

/**
 * @covers App\Utils\GeneratesDkimKeys
 */
class GeneratesDkimKeysTest extends TestCase
{
    public function testGenerate(): void
    {
        $generator = new class {
            use GeneratesDkimKeys;
        };

        $keys = reflection::call_method($generator, "generateDkimKeys");

        $this->assertIsArray($keys);
        $this->assertCount(2, $keys);

        $this->assertStringContainsString("-----BEGIN PRIVATE KEY-----", $keys[0]);
        $this->assertStringContainsString("-----BEGIN PUBLIC KEY-----", $keys[1]);
    }
}
