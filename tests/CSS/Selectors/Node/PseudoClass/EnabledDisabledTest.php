<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\DOM\Traversal\ElementTraversal;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class EnabledDisabledTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('form')
            ->tag('input')->attr('disabled')->attr('match')
            ->tag('button')->attr('disabled')->attr('match')->close()
            ->tag('select')->attr('disabled')->attr('match')->close()
            ->tag('textarea')->attr('disabled')->attr('match')->close()
            ->tag('optgroup')->attr('disabled')->attr('match')->close()
            ->tag('option')->attr('disabled')->attr('match')->close()
            ->tag('fieldset')->attr('disabled')->attr('match')->close()
            ->tag('div')->attr('disabled')
            ->getDocument();
        $disabled = PseudoClassSelector::of('disabled');
        $enabled = PseudoClassSelector::of('enabled');
        foreach (ElementTraversal::descendantsOf($doc) as $node) {
            $mustMatch = $node->hasAttribute('match');
            QueryAssert::elementMatchesSelector($node, $disabled, $mustMatch);
            QueryAssert::elementMatchesSelector($node, $enabled, !$mustMatch);
        }
    }

    public function testItMatchesInsideDisabledContainers()
    {
        $doc = DOMBuilder::html()->tag('form')
            ->tag('fieldset')->attr('disabled')->attr('match')
                ->tag('input')->attr('match')
                ->tag('button')->attr('match')->close()
                ->tag('select')->attr('match')->close()
                ->tag('textarea')->attr('match')->close()
            ->close()
            ->tag('optgroup')->attr('disabled')->attr('match')
                ->tag('option')->attr('match')->close()
            ->close()
            ->getDocument();
        $disabled = PseudoClassSelector::of('disabled');
        $enabled = PseudoClassSelector::of('enabled');
        foreach (ElementTraversal::descendantsOf($doc) as $node) {
            $mustMatch = $node->hasAttribute('match');
            QueryAssert::elementMatchesSelector($node, $disabled, $mustMatch);
            QueryAssert::elementMatchesSelector($node, $enabled, !$mustMatch);
        }
    }

    public function testItDoesntMatchInsideLegend()
    {
        $doc = DOMBuilder::html()->tag('form')
            ->tag('fieldset')->attr('disabled')->attr('match')
                ->tag('legend')
                    ->tag('input')
                ->close()
                ->tag('legend')
                    ->tag('input')->attr('match')
            ->getDocument();
        $disabled = PseudoClassSelector::of('disabled');
        $enabled = PseudoClassSelector::of('enabled');
        foreach (ElementTraversal::descendantsOf($doc) as $node) {
            $mustMatch = $node->hasAttribute('match');
            QueryAssert::elementMatchesSelector($node, $disabled, $mustMatch);
            QueryAssert::elementMatchesSelector($node, $enabled, !$mustMatch);
        }
    }
}
