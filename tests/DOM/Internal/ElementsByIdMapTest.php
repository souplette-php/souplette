<?php declare(strict_types=1);

namespace Souplette\Tests\DOM\Internal;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Internal\ElementsByIdMap;
use Souplette\Tests\DOM\DOMBuilder;

final class ElementsByIdMapTest extends TestCase
{
    public function testBasicAddAndRemove()
    {
        $testId = 'testID';
        $doc = DOMBuilder::html()->tag('html')
            ->tag('test')->id($testId)->close()
            ->getDocument();
        $test = $doc->documentElement->firstElementChild;
        $map = new ElementsByIdMap();
        $map->add($testId, $test);
        Assert::assertTrue($map->has($testId), 'Map contains key after ->add()');
        Assert::assertSame($test, $map->getCacheFirstElementWithoutAccessingNodeTree($testId));
        //
        $map->remove($testId, $test);
        Assert::assertFalse($map->has($testId), 'Map does not contains key after ->remove()');
        Assert::assertNull($map->getCacheFirstElementWithoutAccessingNodeTree($testId));
    }

    public function testDuplicateKeys()
    {
        $key = 'testID';
        $doc = DOMBuilder::html()->tag('html')
            ->tag('test1')->id($key)->close()
            ->tag('test2')->id($key)->close()
            ->getDocument();
        $element1 = $doc->documentElement->firstElementChild;
        $element2 = $element1->nextElementSibling;
        $map = new ElementsByIdMap();
        $map->add($key, $element1);
        Assert::assertTrue($map->has($key), 'Map contains key after ->add()');
        Assert::assertFalse($map->hasMultiple($key), 'Map::containsMultiple is false after single ->add()');
        Assert::assertSame($element1, $map->getCacheFirstElementWithoutAccessingNodeTree($key));
        //
        $map->add($key, $element2);
        Assert::assertTrue($map->has($key), 'Map contains key after duplicate ->add()');
        Assert::assertTrue($map->hasMultiple($key), 'Map::containsMultiple is true after duplicate ->add()');
        Assert::assertNull(
            $map->getCacheFirstElementWithoutAccessingNodeTree($key),
            'Adding duplicate key invalidates the cache.'
        );
        Assert::assertSame($element1, $map->get($key, $doc), 'Map::get forces a tree search.');
        Assert::assertSame(
            $element1,
            $map->getCacheFirstElementWithoutAccessingNodeTree($key),
            'After a successful tree search, the cache is hit.'
        );
        //
        $element1->remove();
        Assert::assertSame(
            $element1,
            $map->getCacheFirstElementWithoutAccessingNodeTree($key),
            'Node removal from tree does not invalidate the cache.'
        );
        //
        $map->remove($key, $element1);
        Assert::assertTrue($map->has($key), 'After Map::remove, map still contains the second element.');
        Assert::assertFalse($map->hasMultiple($key), 'After Map::remove, containsMultiple returns false.');
        Assert::assertNull(
            $map->getCacheFirstElementWithoutAccessingNodeTree($key),
            'After Map::remove, the first matching element is removed from the cache.'
        );
        Assert::assertSame(
            $element2,
            $map->get($key, $doc),
        );
        Assert::assertSame(
            $element2,
            $map->getCacheFirstElementWithoutAccessingNodeTree($key),
        );
        //
        $map->remove($key, $element2);
        Assert::assertFalse($map->has($key));
        Assert::assertFalse($map->hasMultiple($key));
        Assert::assertNull($map->getCacheFirstElementWithoutAccessingNodeTree($key));
        Assert::assertNull(
            $map->get($key, $doc),
            'Map::get returns null even though element is still in the tree.'
        );
    }

    public function testRemovedDuplicateKeys()
    {
        $key = 'testID';
        $doc = DOMBuilder::html()->tag('html')
            ->tag('outer')->id($key)
                ->tag('inner')->id($key)
            ->getDocument();
        $outer = $doc->documentElement->firstElementChild;
        $inner = $outer->firstElementChild;
        $map = new ElementsByIdMap();
        $map->add($key, $outer);
        $map->add($key, $inner);
        Assert::assertSame($outer, $map->get($key, $doc));
        Assert::assertTrue($map->hasMultiple($key));
        //
        $outer->remove();
        Assert::assertTrue($map->hasMultiple($key), 'we have not touched the map yet...');
        $map->remove($key, $outer);
        Assert::assertTrue($map->has($key), 'The map still contains an entry for inner...');
        Assert::assertFalse($map->hasMultiple($key));
        Assert::assertNull($map->get($key, $doc), 'Map::get invalidates the cache.');
        Assert::assertFalse($map->has($key));
    }
}
