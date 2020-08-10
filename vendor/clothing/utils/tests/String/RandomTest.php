<?php

namespace Clothing\Tools\Tests\String;

use Clothing\Tools\Utils\String\Random;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class RandomTest extends TestCase
{
    public function testUniqueID()
    {
        var_dump(Random::uniqueID());
    }
}
