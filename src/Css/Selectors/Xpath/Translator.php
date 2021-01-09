<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath;

use Souplette\Css\Selectors\Node\AttributeSelector;
use Souplette\Css\Selectors\Node\ClassSelector;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\IdSelector;
use Souplette\Css\Selectors\Node\PseudoClassSelector;
use Souplette\Css\Selectors\Node\PseudoElementSelector;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\TypeSelector;
use Souplette\Css\Selectors\Xpath\Exception\UnsupportedSelector;
use Souplette\Css\Selectors\Xpath\Translator\AttributeSelectorTranslator;
use Souplette\Css\Selectors\Xpath\Translator\ClassSelectorTranslator;
use Souplette\Css\Selectors\Xpath\Translator\ComplexSelectorTranslator;
use Souplette\Css\Selectors\Xpath\Translator\CompoundSelectorTranslator;
use Souplette\Css\Selectors\Xpath\Translator\FunctionalSelectorTranslator;
use Souplette\Css\Selectors\Xpath\Translator\IdSelectorTranslator;
use Souplette\Css\Selectors\Xpath\Translator\PseudoClassSelectorTranslator;
use Souplette\Css\Selectors\Xpath\Translator\PseudoElementSelectorTranslator;
use Souplette\Css\Selectors\Xpath\Translator\SelectorListTranslator;
use Souplette\Css\Selectors\Xpath\Translator\TypeSelectorTranslator;

final class Translator
{
    private const TRANSLATORS = [
        SelectorList::class => SelectorListTranslator::class,
        ComplexSelector::class => ComplexSelectorTranslator::class,
        CompoundSelector::class => CompoundSelectorTranslator::class,
        TypeSelector::class => TypeSelectorTranslator::class,
        IdSelector::class => IdSelectorTranslator::class,
        ClassSelector::class => ClassSelectorTranslator::class,
        AttributeSelector::class => AttributeSelectorTranslator::class,
        PseudoClassSelector::class => PseudoClassSelectorTranslator::class,
        PseudoElementSelector::class => PseudoElementSelectorTranslator::class,
        FunctionalSelector::class => FunctionalSelectorTranslator::class,
    ];

    private array $translators = [];

    private TranslationContext $context;

    public function __construct()
    {
        foreach (self::TRANSLATORS as $nodeClass => $translatorClass) {
            $this->translators[$nodeClass] = new $translatorClass();
        }
    }

    public function addTranslator(string $nodeClass, callable $translator)
    {
        $this->translators[$nodeClass] = $translator;
    }

    public function translate(Selector $node): string
    {
        $this->context = new TranslationContext($this, new ExpressionBuilder());
        $this->visit($node);
        return $this->context->expr->build();
    }

    public function visit(Selector $node)
    {
        $translator = $this->translators[$node::class] ?? null;
        if (!$translator) {
            throw new UnsupportedSelector($node);
        }
        $translator($node, $this->context);
    }
}
