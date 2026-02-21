<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidTokenException extends RuntimeException
{
    public static function notFound(): self
    {
        return new self('Token de acceso no encontrado.', 404);
    }

    public static function expired(): self
    {
        return new self('El enlace de acceso ha expirado. Solicita un nuevo enlace a tu psicóloga.', 410);
    }

    public static function alreadyUsed(): self
    {
        return new self('Este enlace ya fue utilizado y no puede volver a abrirse.', 410);
    }

    public static function invalidated(): self
    {
        return new self('Este enlace ha sido cancelado. Contacta a tu psicóloga.', 410);
    }
}
