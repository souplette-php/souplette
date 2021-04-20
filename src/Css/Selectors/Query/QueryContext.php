<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

use DOMElement;
use DOMParentNode;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Html\Dom\Node\HtmlDocument;
use Souplette\Html\Dom\Node\HtmlElement;

final class QueryContext
{
    public function __construct(
        public HtmlElement|HtmlDocument $root,
        public bool $caseInsensitiveClasses = false,
        public bool $caseInsensitiveIds = false,
        public bool $caseInsensitiveTypes = true,
    ) {
    }
}
