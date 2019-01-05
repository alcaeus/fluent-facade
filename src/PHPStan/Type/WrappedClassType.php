<?php

declare(strict_types = 1);

namespace Alcaeus\Fluent\PHPStan\Type;

use Alcaeus\Fluent\ClassWrapper;
use function array_map;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\DeprecatableReflection;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MissingMethodFromReflectionException;
use PHPStan\Reflection\MissingPropertyFromReflectionException;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

class WrappedClassType extends ObjectType
{
    /**
     * @var TypeWithClassName
     */
    private $wrappedType;

    /**
     * @var TypeWithClassName|null
     */
    private $previousType;

    public function __construct(TypeWithClassName $wrappedType, ?TypeWithClassName $previousType = null)
    {
        parent::__construct(ClassWrapper::class);

        $this->wrappedType = $wrappedType;
        $this->previousType = $previousType;
    }

    public function hasMethod(string $methodName): bool
    {
        if (parent::hasMethod($methodName)) {
            return true;
        }

        return $this->getWrappedClassReflection()->hasMethod($methodName);
    }

    public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
    {
        if ($methodName === 'end' && $this->wrappedType) {
            // Handle ending stuff
        }

        try {
            return parent::getMethod($methodName, $scope);
        } catch (MissingMethodFromReflectionException $e) {
            $originalMethod = $this->getWrappedClassReflection()->getMethod($methodName, $scope);
//            $wrappedMethod = new PhpMethodReflection(
//                $originalMethod->getDeclaringClass(),
//                $originalMethod->getDeclaringTrait(),
//                $reflection,
//                $originalMethod->getBroker(),
//                $originalMethod->getParser(),
//                $originalMethod->getFunctionCallStatementFinder(),
//
//            )

            return $originalMethod;
        }
    }

    public function hasProperty(string $propertyName): bool
    {
        if (parent::hasProperty($propertyName)) {
            return true;
        }

        return $this->getWrappedClassReflection()->hasProperty($propertyName);
    }

    public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
    {
        try {
            return parent::getProperty($propertyName, $scope);
        } catch (MissingPropertyFromReflectionException $e) {
            $originalProperty = $this->getWrappedClassReflection()->getProperty($propertyName, $scope);
            $originalType = $originalProperty->getType();
            $wrappedType = $this->wrapType($originalType);

            $wrappedProperty = new PhpPropertyReflection(
                $originalProperty->getDeclaringClass(),
                $wrappedType,
                $originalProperty->getDeclaringClass()->getNativeReflection()->getProperty($propertyName),
                $originalProperty instanceof DeprecatableReflection ? $originalProperty->isDeprecated() : false,
                $originalProperty instanceof InternableReflection ? $originalProperty->isInternal() : false
            );

            if ($propertyName === 'objectProperty') {
//                var_dump($originalType, $wrappedType);
//                exit;
            }

            return $wrappedProperty;
        }
    }

    public function getWrappedType(): TypeWithClassName
    {
        return $this->wrappedType;
    }

    public function getWrappedClass(): string
    {
        return $this->wrappedType->getClassName();
    }

    public function getPreviousType(): ?TypeWithClassName
    {
        return $this->previousType;
    }

    public function describe(VerbosityLevel $level): string
    {
        return $this->previousType === null ?
            sprintf('%s<%s>', ClassWrapper::class, $this->wrappedType->describe($level)) :
            sprintf('%s<%s, %s>', ClassWrapper::class, $this->wrappedType->describe($level), $this->previousType->describe($level))
        ;
    }

    private function getWrappedClassReflection(): \PHPStan\Reflection\ClassReflection
    {
        return Broker::getInstance()->getClass($this->getWrappedClass());
    }

    private function wrapType(Type $type): Type
    {
        if ($type instanceof UnionType) {
            return new UnionType($this->wrapTypes(...$type->getTypes()));
        }

        if ($type instanceof IntersectionType) {
            return new IntersectionType($this->wrapTypes(...$type->getTypes()));
        }

        return $type instanceof TypeWithClassName ? new self($type, $this) : $type;
    }

    private function wrapTypes(Type ...$types): array
    {
        return array_map(
            function (Type $type) {
                return $this->wrapType($type);
            },
            $types
        );
    }
}
