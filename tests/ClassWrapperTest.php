<?php

declare(strict_types=1);

namespace Alcaeus\Fluent\Test;

use Alcaeus\Fluent\ClassWrapper;
use Alcaeus\Fluent\Exception\InvalidReturnValue;
use Alcaeus\Fluent\Exception\NoPreviousObject;
use Alcaeus\Fluent\Test\Object\CallInspectingObject;
use Alcaeus\Fluent\Test\Object\MethodCall;
use PHPUnit\Framework\TestCase;
use function sprintf;

class ClassWrapperTest extends TestCase
{
    public function testWrappingObject(): void
    {
        $object = $this->getObject();

        $fluentObject = ClassWrapper::wrap($object);

        self::assertInstanceOf(ClassWrapper::class, $fluentObject);

        self::assertSame($fluentObject, $fluentObject->voidMethod());

        $fluentObject
            ->voidMethod()
            ->someOtherReturnValueMethod()
            ->voidMethod('argument', 'nextArgument');

        self::assertEquals([
            new MethodCall('voidMethod', []),
            new MethodCall('voidMethod', []),
            new MethodCall('someOtherReturnValueMethod', []),
            new MethodCall('voidMethod', ['argument', 'nextArgument']),
        ], $object->getCalls());
    }

    public function testEndingWrappedRootObjectThrowsException(): void
    {
        $object = $this->getObject();

        $fluentObject = ClassWrapper::wrap($object);

        $this->expectException(NoPreviousObject::class);
        $this->expectExceptionMessage(sprintf('Tried ending nested fluent call, but no previous object was found for object of class %s.', CallInspectingObject::class));

        $fluentObject->end();
    }

    public function testMethodReturningObjectReturnsWrappedClass(): void
    {
        $object = $this->getObject();

        $fluentObject = ClassWrapper::wrap($object);

        /** @var ClassWrapper|CallInspectingObject $nestedFluentObject */
        $nestedFluentObject = $fluentObject->fluentObjectMethod();

        self::assertInstanceOf(ClassWrapper::class, $nestedFluentObject);
        self::assertSame($nestedFluentObject, $nestedFluentObject->voidMethod());

        self::assertEquals([new MethodCall('voidMethod', [])], $object->fluentObjectMethod()->getCalls());

        self::assertSame($fluentObject, $nestedFluentObject->end());
    }

    public function testMethodReturningWrappedClassCausesException(): void
    {
        $object = $this->getObject();

        /** @var ClassWrapper|CallInspectingObject $nestedFluentObject */
        $fluentObject = ClassWrapper::wrap($object);

        $this->expectException(InvalidReturnValue::class);
        $this->expectExceptionMessage(sprintf(
            'Method %s::%s returned a value of type %s which cannot be used in a fluent expression.',
            CallInspectingObject::class,
            'methodReturningWrappedObject',
            ClassWrapper::class
        ));

        $fluentObject->methodReturningWrappedObject();
    }

    public function testAccessingPropertyWithObjectValueReturnsWrappedClass(): void
    {
        $object = $this->getObject();

        $fluentObject = ClassWrapper::wrap($object);

        /** @var ClassWrapper|CallInspectingObject $nestedFluentObject */
        $nestedFluentObject = $fluentObject->objectProperty;

        self::assertInstanceOf(ClassWrapper::class, $nestedFluentObject);
        self::assertSame($nestedFluentObject, $nestedFluentObject->voidMethod());

        self::assertEquals([new MethodCall('voidMethod', [])], $object->objectProperty->getCalls());

        self::assertSame($fluentObject, $nestedFluentObject->end());
    }

    public function testAccessingPropertyWithWrappedClassCausesException(): void
    {
        $object = $this->getObject();

        /** @var ClassWrapper|CallInspectingObject $nestedFluentObject */
        $fluentObject = ClassWrapper::wrap($object);

        $this->expectException(InvalidReturnValue::class);
        $this->expectExceptionMessage(sprintf(
            'Property %s::%s returned a value of type %s which cannot be used in a fluent expression.',
            CallInspectingObject::class,
            'propertyContainingWrappedObject',
            ClassWrapper::class
        ));

        $fluentObject->propertyContainingWrappedObject;
    }

    public function testAccessingPropertyWithNonObjectValueCausesException(): void
    {
        $object = $this->getObject();

        $fluentObject = ClassWrapper::wrap($object);

        $this->expectException(InvalidReturnValue::class);
        $this->expectExceptionMessage(sprintf(
            'Property %s::%s returned a value of type %s which cannot be used in a fluent expression.',
            CallInspectingObject::class,
            'scalarProperty',
            'integer'
        ));

        $fluentObject->scalarProperty;
    }

    private function getObject(): CallInspectingObject
    {
        return new CallInspectingObject(true);
    }
}
