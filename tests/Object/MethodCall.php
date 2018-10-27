<?php

declare(strict_types=1);

namespace Alcaeus\Fluent\Test\Object;

final class MethodCall
{
    /** @var string */
    private $name;

    /** @var mixed[] */
    private $arguments;

    /**
     * @param mixed[] $arguments
     */
    public function __construct(string $name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
