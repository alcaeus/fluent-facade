<?php

declare(strict_types=1);

namespace Alcaeus\Fluent\Test\Object;

use Alcaeus\Fluent\ClassWrapper;
use function func_get_args;

class CallInspectingObject
{
    /** @var int */
    public $scalarProperty = 5;

    /** @var CallInspectingObject|null */
    public $objectProperty;

    /** @var ClassWrapper|null */
    public $propertyContainingWrappedObject;

    /** @var CallInspectingObject|null */
    private $nestedObject;

    /** @var MethodCall[] */
    private $calls = [];

    public function __construct(bool $createNested = false)
    {
        if (!$createNested) {
            return;
        }

        $this->objectProperty = new self();
        $this->propertyContainingWrappedObject = ClassWrapper::wrap(new self());
    }

    /**
     * @param mixed[] $arguments
     */
    private function logCall(string $method, array $arguments): void
    {
        $this->calls[] = new MethodCall($method, $arguments);
    }

    public function voidMethod(): void
    {
        $this->logCall(__FUNCTION__, func_get_args());
    }

    public function fluentMethod(): self
    {
        $this->logCall(__FUNCTION__, func_get_args());

        return $this;
    }

    public function someOtherReturnValueMethod(): int
    {
        $this->logCall(__FUNCTION__, func_get_args());

        return 5;
    }

    public function fluentObjectMethod(): self
    {
        if ($this->nestedObject === null) {
            $this->nestedObject = new self();
        }

        return $this->nestedObject;
    }

    public function methodReturningWrappedObject(): ClassWrapper
    {
        return ClassWrapper::wrap(new self());
    }

    /**
     * @return MethodCall[]
     */
    public function getCalls(): array
    {
        return $this->calls;
    }
}
