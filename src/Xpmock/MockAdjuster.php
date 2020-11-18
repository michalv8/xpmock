<?php
namespace Xpmock;

use PHPUnit\Framework\MockObject\MockObject as PHPUnitMockObject;

class MockAdjuster extends Base
{
    /** @var PHPUnitMockObject */
    private $mock;
    /** @var \ReflectionClass */
    private $reflection;

    /**
     * @param PHPUnitMockObject $mock
     */
    public function __construct(PHPUnitMockObject $mock, \ReflectionClass $reflection)
    {
        $this->mock = $mock;
        $this->reflection = $reflection;
    }

    /** @return self */
    public function __call($method, array $args)
    {
        $this->addMethodExpectation($this->reflection, $this->mock, $this->parseMockMethodArgs($method, $args));

        return $this;
    }
}
