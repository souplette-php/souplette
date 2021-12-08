<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Dom\Traversal\ElementTraversal;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class ReadOnlyTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::html()->tag('form')->attr('match')
            ->tag('input')
            ->tag('input')->attr('readonly')->attr('match')
            ->tag('input')->attr('disabled')->attr('match')
            ->tag('textarea')->close()
            ->tag('textarea')->attr('readonly')->attr('match')->close()
            ->tag('textarea')->attr('disabled')->attr('match')->close()
            ->tag('div')->attr('contenteditable', 'true')
                ->tag('p')->close()
                ->tag('p')->attr('contenteditable', 'false')->attr('match')->close()
            ->close()
            ->tag('div')->attr('contenteditable', 'false')->attr('match')->close()
            ->getDocument();
        $readOnly = PseudoClassSelector::of('read-only');
        $readWrite = PseudoClassSelector::of('read-write');
        foreach (ElementTraversal::descendantsOf($doc) as $element) {
            $mustMatch = $element->hasAttribute('match');
            QueryAssert::elementMatchesSelector($element, $readOnly, $mustMatch);
            QueryAssert::elementMatchesSelector($element, $readWrite, !$mustMatch);
        }
    }
}
