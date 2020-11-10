<?php
namespace Xpmock;

require_once(dirname(__DIR__) . '/stubs/Usage.php');
require_once(dirname(__DIR__) . '/stubs/AbstractClass.php');

class UsageTest extends TestCase
{
    private function cleanupMock($mock)
    {
        $mock->this()->__phpunit_invocationMocker = null;
    }

    /** @return \Stubs\Usage */
    private function mockUsage()
    {
        return $this->mock('Stubs\Usage');
    }

    public function testMock()
    {
        $mock = $this->mockUsage();

        $this->assertInstanceOf('Xpmock\MockWriter', $mock);
    }

    public function testReturnValue()
    {
        $mock = $this->mockUsage()
            ->getNumber(123)
            ->new();

        $this->assertEquals(123, $mock->getNumber());
    }

    public function testReturnNativeValue()
    {
        $mock = $this->mockUsage()
            ->getNumber($this->returnValue(112))
            ->new();

        $this->assertEquals(112, $mock->getNumber());
    }

    public function testReturnCallback()
    {
        $mock = $this->mockUsage()
            ->getNumber(
                function () {
                    return 156;
                }
            )
            ->new();

        $this->assertSame(156, $mock->getNumber());
    }

    public function testReturnThrowException()
    {
        $this->expectException(\LogicException::class);
        $mock = $this->mockUsage()
            ->getNumber(new \LogicException())
            ->new();

        $mock->getNumber();
    }

    public function testExpectsOnce()
    {
        $mock = $this->mockUsage()
            ->getNumber($this->once())
            ->new();

        try {
            $mock->getNumber();
            $mock->getNumber();
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertInstanceof(\PHPUnit\Framework\ExpectationFailedException::class, $ex);
        }

        $this->cleanupMock($mock);
    }

    public function testExpectsTwice()
    {
        $mock = $this->mockUsage()
            ->getNumber($this->exactly(2))
            ->new();

        try {
            $mock->getNumber();
            $mock->__phpunit_verify();
            $this->fail();
        } catch (\PHPUnit\Framework\ExpectationFailedException $ex) {
            $this->assertTrue(true);
        }

        $this->cleanupMock($mock);
    }

    public function testWillExpects()
    {
        $mock = $this->mockUsage()
            ->getNumber(142, $this->once())
            ->new();

        $this->assertEquals(142, $mock->getNumber());

        try {
            $mock->getNumber();
            $this->fail();
        } catch (\PHPUnit\Framework\ExpectationFailedException $ex) {
        }

        $this->cleanupMock($mock);
    }

    public function testWithWill()
    {
        $mock = $this->mockUsage()
            ->getNumber(array(1, 2, 3), 152)
            ->new();

        $this->assertEquals(152, $mock->getNumber(1, 2, 3));

        try {
            $mock->getNumber();
            $this->fail();
        } catch (\PHPUnit\Framework\ExpectationFailedException $ex) {
        }

        $this->cleanupMock($mock);
    }

    public function testWithExpects()
    {
        $mock = $this->mockUsage()
            ->getNumber(array(1, 2, 3), $this->once())
            ->new();

        $this->assertEquals(null, $mock->getNumber(1, 2, 3));

        try {
            $mock->getNumber(1, 2, 3);
            $this->fail();
        } catch (\PHPUnit\Framework\ExpectationFailedException $ex) {
        }

        $this->cleanupMock($mock);
    }

    public function testWithWillInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->mockUsage()
            ->getNumber('not_array', 1);
    }

    public function testWithWillExpects()
    {
        $mock = $this->mockUsage()
            ->getNumber(array(1, 2, 3), 1, $this->once())
            ->new();

        $this->assertEquals(1, $mock->getNumber(1, 2, 3));

        try {
            $mock->getNumber();
            $this->fail();
        } catch (\PHPUnit\Framework\ExpectationFailedException $ex) {
        }

        try {
            $mock->getNumber(1, 2, 3);
            $this->fail();
        } catch (\PHPUnit\Framework\ExpectationFailedException $ex) {
        }

        $this->cleanupMock($mock);
    }

    public function testWithWillExpectsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->mockUsage()
            ->getNumber('not_array', 1, 1);
    }

    public function testNew()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Value 900 from constructor');
        $this->mockUsage()
            ->new(900);
    }

    public function testMockStdClass()
    {
        $mock = $this->mock()
            ->method1(1)
            ->method2(2)
            ->new();

        $this->assertInstanceOf('stdClass', $mock);
        $this->assertSame(1, $mock->method1());
        $this->assertSame(2, $mock->method2());
    }

    public function testMockAbstractMethod()
    {
        $mock = $this->mock('Stubs\AbstractClass')
            ->getString('fake string')
            ->new();

        $this->assertSame('fake string', $mock->getString());
        $this->assertSame(2, $mock->getNumber());
    }

    public function testBriefSyntax()
    {
        $mock = $this->mock(
            'Stubs\Usage',
            array(
                'getNumber' => 2,
                'property' => 'fake property',
                'getPropertyTwice' => function () {
                    return $this->property . $this->property;
                },
            )
        );

        $this->assertInstanceOf('Stubs\Usage', $mock);
        $this->assertSame(2, $mock->getNumber());
        $this->assertSame('real string', $mock->getString());
        $this->assertSame('fake property', $mock->getProperty());
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $this->assertSame('fake propertyfake property', $mock->getPropertyTwice());
        }
    }

    public function testMethodThis()
    {
        $mock = $this->mockUsage()
            ->getNumber(2)
            ->new();

        $this->assertSame('real string', $mock->getString());
        $this->assertSame(2, $mock->getNumber());
        $this->assertInstanceOf('Xpmock\Reflection', $mock->this());
        $this->assertSame('real property', $mock->getProperty());
        $mock->this()->property = 'fake property';
        $this->assertSame('fake property', $mock->getProperty());
    }

    public function testMockWhereAllMethodsReturnNull()
    {
        $mock = $this->mock('Stubs\Usage', null);

        $this->assertInstanceOf('Stubs\Usage', $mock);

        $this->assertNull($mock->getNumber());
        $this->assertNull($mock->getString());
        $this->assertNull($mock->getProperty());
        $this->assertInstanceOf('Xpmock\Reflection', $mock->this());
        $this->assertInstanceOf('Xpmock\MockAdjuster', $mock->mock());
    }

    public function testAdjustAfterCreation()
    {
        $mock = $this->mock('Stubs\Usage', null);

        $this->assertNull($mock->getNumber());
        $this->assertInstanceOf('Xpmock\MockAdjuster', $mock->mock());

        $mock->mock()
            ->getNumber(3);

        $this->assertSame(3, $mock->getNumber());
    }
}
