<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Simple;

use Souplette\CSS\Selectors\Node\PseudoClass\AnyLinkPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\CheckedPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\DefaultPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\DefinedPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\DisabledPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\EmptyPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\EnabledPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\FirstChildPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\FirstOfTypePseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\LastChildPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\LastOfTypePseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\OnlyChildPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\OnlyOfTypePseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\OptionalPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\ReadOnlyPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\ReadWritePseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\RequiredPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\RootPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\ScopePseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\SelectedPseudo;
use Souplette\CSS\Selectors\Node\SimpleSelector;
use Souplette\CSS\Selectors\Specificity;

class PseudoClassSelector extends SimpleSelector
{
    private const KNOWN_CLASSES = [
        'any-link' => AnyLinkPseudo::class,
        'checked' => CheckedPseudo::class,
        'default' => DefaultPseudo::class,
        'defined' => DefinedPseudo::class,
        'disabled' => DisabledPseudo::class,
        'empty' => EmptyPseudo::class,
        'enabled' => EnabledPseudo::class,
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
