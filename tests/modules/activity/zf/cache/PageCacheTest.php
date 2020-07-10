<?php
namespace  app\tests\modules\activity\zf\cache;

use app\modules\activity\zf\cache\PageCache;
use app\tests\TestCase;

class PageCacheTest extends TestCase
{
    /**
     * @var PageCache
     */
    protected $obj;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();
        $this->obj=  new PageCache();
    }

    public function testgenerateKey()
    {
        $result = $this->obj->generateKey('zf-pc', '492','123,456');
        $this->assertEquals('PageCache-4f200c21ff0f6949-5ea24367a8e5b89d4d54aaf64d16112d', $result);

        $result = $this->obj->getPrefix('zf-pc', '492');
        $this->assertEquals('PageCache-4f200c21ff0f6949', $result);
    }

    public function testRefreshKey()
    {
        app()->redis->set($this->obj->getPrefix('zf-pc', -1), '123456');
        $result = $this->obj->refreshKey('zf-pc', '-1');
        $this->assertEquals(1, $result);
    }
}
