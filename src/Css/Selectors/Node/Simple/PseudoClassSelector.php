<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Node\PseudoClass\AnyLinkPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\CheckedPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\DefaultPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\DisabledPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\EmptyPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\FirstChildPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\FirstOfTypePseudo;
use Souplette\Css\Selectors\Node\PseudoClass\LastChildPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\LastOfTypePseudo;
use Souplette\Css\Selectors\Node\PseudoClass\OnlyChildPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\OnlyOfTypePseudo;
use Souplette\Css\Selectors\Node\PseudoClass\OptionalPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\ReadOnlyPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\ReadWritePseudo;
use Souplette\Css\Selectors\Node\PseudoClass\RequiredPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\RootPseudo;
use Souplette\Css\Selectors\Node\PseudoClass\ScopePseudo;
use Souplette\Css\Selectors\Node\PseudoClass\SelectedPseudo;
use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Specificity;

class PseudoClassSelector extends SimpleSelector
{
    private const KNOWN_CLASSES = [
        'any-link' => AnyLinkPseudo::class,
        'checked' => CheckedPseudo::class,
        'default' => DefaultPseudo::class,
        'disabled' => DisabledPseudo::class,
        'empty' => EmptyPseudo::class,
        'first-child' => FirstChildPseudo::class,
        'first-of-type' => FirstOfTypePseudo::class,
        'last-child' => LastChildPseudo::class,
        'last-of-type' => LastOfTypePseudo::class,
        'link' => AnyLinkPseudo::class,
        'only-child' => OnlyChildPseudo::class,
        'only-of-type' => OnlyOfTypePseudo::class,
        'optional' => OptionalPseudo::class,
        'read-only' => ReadOnlyPseudo::class,
        'read-write' => ReadWritePseudo::class,
        'required' => RequiredPseudo::class,
        'root' => RootPseudo::class,
        'scope' => ScopePseudo::class,
        'selected' => SelectedPseudo::class,
    ];

    final public static function of(string $name): static
    {
        if ($class = self::KNOWN_CLASSES[$name] ?? null) {
            return new $class($name);
        }
        return new self($name);
    }

    public function __construct(
        public string $name,
    ) {
    }

    public function __toString(): string
    {
        return ":{$this->name}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 1);
    }
}
