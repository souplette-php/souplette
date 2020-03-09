<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

final class OpenElementsStack extends \SplStack
{
    public function contains(\DOMElement $element): bool
    {
        foreach ($this as $node) {
            if ($node === $element) {
                return true;
            }
        }

        return false;
    }
}
