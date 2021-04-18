<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

use DOMElement;
use Souplette\Css\Selectors\Node\Selector;

final class QueryContext
{
    public Selector $selector;
    public ?DOMElement $previousElement = null;
    public bool $isSubSelector = false;
    public bool $inRightmostCompound = true;
    public bool $caseInsensitiveClasses = false;
    public bool $caseInsensitiveIds = false;
    public bool $caseInsensitiveTypes = true;

    public function __construct(
        public DOMElement $element,
    ) {
    }
}
