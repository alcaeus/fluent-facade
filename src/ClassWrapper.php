<?php

declare(strict_types=1);

namespace Alcaeus\Fluent;

use Alcaeus\Fluent\Exception\InvalidReturnValue;
use Alcaeus\Fluent\Exception\NoPreviousObject;
use function get_class;
use function gettype;
use function is_object;

final class ClassWrapper
{
    /** @var object */
    private $object;

    /** @var ClassWrapper|null */
    private $previous;

    public static function wrap(object $object): self
    {
        return new self($object);
    }

    private function __construct(object $object, ?self $previous = null)
    {
        $this->object = $object;
        $this->previous = $previous;
    }

    /** @param mixed[] $arguments */
    public function __call(string $name, array $arguments): self
    {
        $result = $this->object->$name(...$arguments);
        if ($result instanceof self) {
            throw InvalidReturnValue::invalidMethodCallResult(get_class($this->object), $name, self::class);
        }

        return $this->wrapResult($result);
    }

    /** @param mixed $result */
    private function wrapResult($result): self
    {
        return is_object($result) && $result !== $this->object ? new self($result, $this) : $this;
    }

    public function __get(string $name): self
    {
        $result = $this->object->$name;
        if ($result instanceof self) {
            throw InvalidReturnValue::invalidPropertyResult(get_class($this->object), $name, self::class);
        }

        if (!is_object($result)) {
            throw InvalidReturnValue::invalidPropertyResult(get_class($this->object), $name, gettype($result));
        }

        return $this->wrapResult($result);
    }

    public function end(): self
    {
        if (!$this->previous) {
            throw NoPreviousObject::create(get_class($this->object));
        }

        return $this->previous;
    }
}
