<?php
namespace Xpmock;

use PHPUnit\Framework\MockObject\Verifiable as PHPUnitVerifiable;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use PHPUnit\Framework\MockObject\Stub\Stub as PHPUnitMockObjectStub;
use PHPUnit\Framework\MockObject\MockObject as PHPUnitMockObject;

class Base
{
    /** @return array */
    protected function parseMockMethodArgs($method, array $args)
    {
        $expects = TestCase::any();
        $with = null;
        $will = TestCase::returnValue(null);

        if (count($args) == 1) {
            if ($args[0] instanceof PHPUnitVerifiable) {
                $expects = $args[0];
            } else {
                $will = $args[0];
            }
        } elseif (count($args) == 2) {
            if ($args[1] instanceof PHPUnitVerifiable) {
                if (is_array($args[0])) {
                    list($with, $expects) = $args;
                } else {
                    list($will, $expects) = $args;
                }
            } elseif (is_array($args[0])) {
                list($with, $will) = $args;
            } else {
                throw new \InvalidArgumentException();
            }
        } elseif (count($args) == 3) {
            if (is_array($args[0]) && ($args[2] instanceof PHPUnitVerifiable)) {
                list($with, $will, $expects) = $args;
            } else {
                throw new \InvalidArgumentException();
            }
        }

        if ($will instanceof \Exception) {
            $will = PhpUnitTestCase::throwException($will);
        } elseif (!$will instanceof PHPUnitMockObjectStub && !$will instanceof \Closure && !is_null($will)) {
            $will = PhpUnitTestCase::returnValue($will);
        }

        return array(
            'method' => $method,
            'expects' => $expects,
            'with' => $with,
            'will' => $will,
        );
    }

    protected function addMethodExpectation(\ReflectionClass $reflection, PHPUnitMockObject $mock, array $expectation)
    {
        if (is_null($expectation['will'])) {
            return;
        }
        $expect = $mock
            ->expects($expectation['expects'])
            ->method($expectation['method'])
            ->will(
                $expectation['will'] instanceof \Closure
                    ? PhpUnitTestCase::returnCallback(
                    version_compare(PHP_VERSION, '5.4.0', '>=') ? $expectation['will']->bindTo($mock, $reflection->getName()) : $expectation['will']
                ) : $expectation['will']
            );
        if (!is_null($expectation['with'])) {
            call_user_func_array(array($expect, 'with'), $expectation['with']);
        }
    }
}
