<?php

namespace App\Entity;

enum FileConversionStatus: string
{
    case PENDING = 'PENDING';
    case PROCESSING = 'PROCESSING';
    case DONE = 'DONE';
    case FAILED = 'FAILED';
}
