<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidTokenException extends RuntimeException
{
    public static function notFound(): self
    {
        return new self(__('Access token not found.'), 404);
    }

    public static function expired(): self
    {
        return new self(__('The access link has expired. Request a new link from your psychologist.'), 410);
    }

    public static function alreadyUsed(): self
    {
        return new self(__('This link has already been used and cannot be opened again.'), 410);
    }

    public static function invalidated(): self
    {
        return new self(__('This link has been cancelled. Contact your psychologist.'), 410);
    }
}
