<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\CSS\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Document;
use Souplette\DOM\Element;

/**
 * Ported from web-platform-tests
 * wpt/css/selectors/has-matches-to-uninserted-elements.html
 */
final class HasMatchesDisconnectedElementsTest extends TestCase
{
    #[DataProvider('itMatchesDisconnectedElementsProvider')]
    public function testItMatchesDisconnectedElements(Element $subject, string $selector, bool $expected)
    {
        $msg = sprintf('Selector %s %s', $selector, $expected ? 'matches' : 'does not match');
        Assert::assertSame($expected, $subject->matches($selector), $msg);
    }

    public static function itMatchesDisconnectedElementsProvider(): iterable
    {
        $doc = new Document('html');

        $subject = $doc->createElement('subject');
        $subject->appendChild($doc->createElement('child'));
        yield [$subject, ':has(child)', true];
        yield [$subject, ':has(> child)', true];

        $subject = $doc->createElement('subject');
        $subject->innerHTML = '<child><descendant></descendant></child>';
        yield [$subject, ':has(descendant)', true];
        yield [$subject, ':has(> descendant)', false];

        $subject = $doc->createElement('subject');
        $subject->innerHTML = <<<'HTML'
        <child></child>
        <direct-sibling></direct-sibling>
        <indirect-sibling></indirect-sibling>
        HTML;
        yield [$subject->firstElementChild, ':has(~ direct-sibling)', true];
        yield [$subject->firstElementChild, ':has(+ direct-sibling)', true];
        yield [$subject->firstElementChild, ':has(~ indirect-sibling)', true];
        yield [$subject->firstElementChild, ':has(+ indirect-sibling)', false];

        yield [$subject, ':has(*)', true];
        yield [$subject, ':has(> *)', true];
        yield [$subject, ':has(~ *)', false];
        yield [$subject, ':has(+ *)', false];
    }
}
