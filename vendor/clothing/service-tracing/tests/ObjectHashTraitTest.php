<?php

namespace Clothing\Tools\Tests;

use Clothing\Tools\ServiceTracing\ObjectHashTrait;

/**
 * @internal
 */
class ObjectHashTraitTest extends TestCase
{
    public function testAll()
    {
        $mock = $this->getMockForTrait(ObjectHashTrait::class);
        $this->assertSame($mock, $mock->registerObjectHash());
        $this->assertSame(spl_object_hash($mock), $mock->getHashId());

        $class = get_class($mock);
        $this->assertSame($mock, $class::withHashId($mock->getHashId()));
        $this->assertFalse($class::withHashId(uniqid()));
    }
}
