<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;
use App\Database\Query\Grammars\MySqlGrammar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $connection = DB::connection();

        if (!$connection instanceof Connection) {
            throw new \UnexpectedValueException(
                "DB::connection() did not return a database connection"
            );
        }

        $connection->setQueryGrammar(new MySqlGrammar());
    }
}
