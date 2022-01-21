<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Email;

class DeliverEmail extends Job
{
    /**
     * @param Email $email The email to try and deliver
     */
    public function __construct(private Email $email)
    {
        //
    }

    /**
     * Process the job
     *
     * @return void
     */
    public function handle(): void
    {
        //
    }
}
