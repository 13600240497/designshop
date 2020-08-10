<?php

namespace Clothing\Tools\Tests;

use Clothing\Tools\ServiceTracing\ActionTrait;

/**
 * @internal
 */
class ActionTraitTest extends TestCase
{
    protected $mock;

    protected function setUp()
    {
        parent::setUp();
        $this->mock = $this->getMockForTrait(ActionTrait::class);
    }

    /**
     * @expectedException        \Clothing\Tools\ServiceTracing\Exception
     * @expectedExceptionMessage Tracing has not started!
     */
    public function testNotStartedException()
    {
        $this->mock->getStartTimestamp();
    }

    /**
     * @expectedException        \Clothing\Tools\ServiceTracing\Exception
     * @expectedExceptionMessage Tracing has not started!
     */
    public function testNotStartexFinishException()
    {
        $this->mock->finish();
    }

    /**
     * @expectedException        \Clothing\Tools\ServiceTracing\Exception
     * @expectedExceptionMessage Tracing has not finished!
     */
    public function testNotFinishedException()
    {
        $this->mock->getFinishTimestamp();
    }

    /**
     * @depends testNotStartedException
     * @depends testNotStartexFinishException
     * @depends testNotFinishedException
     */
    public function testBeforeStart()
    {
        $this->assertFalse($this->mock->isStarted());
        $this->assertFalse($this->mock->isFinished());
        $this->assertFalse($this->mock->hasException());
        $this->assertNull($this->mock->getException());
        $this->assertSame(0, (int) $this->mock->getDuration());
    }

    /**
     * @depends testBeforeStart
     */
    public function testBeforeFinish()
    {
        $this->assertSame($this->mock, $this->mock->start());
        $this->assertTrue($this->mock->isStarted());
        $this->assertFalse($this->mock->isFinished());
        $this->assertFalse($this->mock->hasException());
        $this->assertNull($this->mock->getException());
        $this->assertSame(0, (int) $this->mock->getDuration());
    }

    /**
     * @depends testBeforeFinish
     */
    public function testAfterFinish()
    {
        $this->assertSame($this->mock, $this->mock->start());
        usleep(10000);
        $this->assertSame($this->mock, $this->mock->finish());
        $this->assertGreaterThanOrEqual(10, $this->mock->getDuration());
        $this->assertGreaterThanOrEqual(10, $this->mock->getElapsedMilliseconds());
        $this->assertGreaterThanOrEqual(5, $this->mock->getElapsedMilliseconds($this->mock->getFinishTimestamp() - 5));
        $this->assertTrue($this->mock->isStarted());
        $this->assertTrue($this->mock->isFinished());
        $this->assertFalse($this->mock->hasException());
        $this->assertNull($this->mock->getException());
    }

    public function testException()
    {
        $mock = $this->getMockForTrait(ActionTrait::class);
        $this->assertFalse($mock->isStarted());
        $this->assertFalse($mock->isFinished());
        $this->assertFalse($mock->hasException());
        $this->assertNull($mock->getException());
        $this->assertSame($mock, $mock->start());
        usleep(10000);
        $this->assertSame($mock, $mock->setException(new \RuntimeException('testRequesstException')));
        $this->assertTrue($mock->isStarted());
        $this->assertTrue($mock->isFinished());
        $this->assertTrue($mock->hasException());
        $this->assertInstanceOf(\RuntimeException::class, $mock->getException());
        $this->expectException(\Clothing\Tools\ServiceTracing\Exception::class);
        $mock->setException(new \RuntimeException('testRequesstException'));
    }
}
