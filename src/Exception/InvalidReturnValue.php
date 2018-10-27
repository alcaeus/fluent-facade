<?php

declare(strict_types=1);

namespace Alcaeus\Fluent\Exception;

use UnexpectedValueException;
use function sprintf;

final class InvalidReturnValue extends UnexpectedValueException implements FluentException
{
    public static function invalidMethodCallResult(string $className, string $methodName, string $type): self
    {
        return new self(sprintf('Method %s::%s returned a value of type %s which cannot be used in a fluent expression.', $className, $methodName, $type));
    }

    public static function invalidPropertyResult(string $className, string $propertyName, string $type): self
    {
        return new self(sprintf('Property %s::%s returned a value of type %s which cannot be used in a fluent expression.', $className, $propertyName, $type));
    }
}
