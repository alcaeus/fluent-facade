<?php

declare(strict_types = 1);

namespace Alcaeus\Fluent\PHPStan\Type;

use Alcaeus\Fluent\ClassWrapper;
use Alcaeus\Fluent\PHPStan\Type\WrappedClassType;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;

class ClassWrapperReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension, DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return ClassWrapper::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'wrap';
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): Type
    {
        $arg = $methodCall->args[0]->value;

        $type = $scope->getType($arg);
        if (!$type instanceof TypeWithClassName) {
            throw new ShouldNotHappenException();
        }

        return new WrappedClassType($type);
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return false;
        return $methodReflection->getName() === 'wrapResult';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $arg = $methodCall->args[0]->value;

        $type = $scope->getType($arg);
        if (!$type instanceof TypeWithClassName) {
            var_dump($type);
//            var_dump($arg);
            exit;
            throw new ShouldNotHappenException();
        }

        return new WrappedClassType($type);

//
//        $calledOnType = $scope->getType($methodCall->var);
//        if (!$calledOnType instanceof WrappedClassType) {
//            return new MixedType();
//        }
//
//        if ($methodReflection->getName() === 'end') {
//            $previousType = $calledOnType->getPreviousType();
//            if ($previousType === null) {
//                throw new \Exception('You done goofed');
//            }
//
//            return $previousType;
//        }
//
//        $originalReturnType = ParametersAcceptorSelector::selectSingle(
//            $methodReflection->getVariants()
//        )->getReturnType();
//
//        if (!$originalReturnType instanceof TypeWithClassName) {
//            return $calledOnType;
//        }
//
//        return $originalReturnType->equals($calledOnType) ? $calledOnType : new WrappedClassType($originalReturnType, $calledOnType->getWrappedType());
    }
}
