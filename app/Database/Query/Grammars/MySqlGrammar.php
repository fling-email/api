<?php

declare(strict_types=1);

namespace App\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\MySqlGrammar as BaseMySqlGrammar;

class MySqlGrammar extends BaseMySqlGrammar
{
    /**
     * Gets the string format to store dates
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }
}
