<?php

declare(strict_types=1);

namespace Alcaeus\Fluent\Exception;

use BadMethodCallException;
use function sprintf;

final class NoPreviousObject extends BadMethodCallException implements FluentException
{
    public static function create(string $className): self
    {
        return new self(sprintf('Tried ending nested fluent call, but no previous object was found for object of class %s.', $className));
    }
}
