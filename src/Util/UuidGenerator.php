<?php

namespace App\Util;

use Symfony\Component\Uid\Uuid;

class UuidGenerator
{
    public function generate(): Uuid
    {
        return Uuid::v4();
    }
}
