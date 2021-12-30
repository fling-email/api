<?php

declare(strict_types=1);

namespace Tests\Unit\Database\Query\Grammars;

use Tests\TestCase;
use App\Database\Query\Grammars\MySqlGrammar;

/**
 * @covers App\Database\Query\Grammars\MySqlGrammar
 */
class MySqlGrammarTest extends TestCase
{
    public function testGetDateFormat(): void
    {
        $format = (new MySqlGrammar())->getDateFormat();

        $date = new \DateTime();
        $result = $date->format($format);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}
