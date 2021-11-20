<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

use Souplette\Css\Selectors\Node\Combinator;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\Functional\Has;
use Souplette\Css\Selectors\Node\Functional\Is;
use Souplette\Css\Selectors\Node\Functional\Not;
use Souplette\Css\Selectors\Node\Functional\NthChild;
use Souplette\Css\Selectors\Node\Functional\NthLastChild;
use Souplette\Css\Selectors\Node\Functional\NthLastOfType;
use Souplette\Css\Selectors\Node\Functional\NthOfType;
use Souplette\Css\Selectors\Node\Functional\Where;
use Souplette\Css\Selectors\Node\RelativeSelector;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\AttributeSelector;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Node\Simple\PseudoElementSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Node\Simple\UniversalSelector;
use Souplette\Css\Selectors\Query\Evaluator\ComplexEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\CompoundEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Functional\HasEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Functional\NotEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Functional\NthChildEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Functional\NthLastChildEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Functional\NthLastOfTypeEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Functional\NthOfTypeEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\ListEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\NeverEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\AnyLinkEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\CheckedEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\DefaultEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\DisabledEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\EmptyEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\FirstChildEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\FirstOfTypeEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\LastChildEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\LastOfTypeEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\OnlyChildEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\OnlyOfTypeEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\OptionalEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\ReadOnlyEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\ReadWriteEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\RequiredEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\RootEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\ScopeEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\SelectedEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoClass\UnsupportedPseudoClassEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\PseudoElementEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Simple\AttributeEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Simple\ClassEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Simple\IdEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Simple\TypeEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Simple\UniversalEvaluator;
use Souplette\Css\Selectors\Query\Exception\UnsupportedSelector;

/**
 * @todo ATM the compiler is only used for solving selector iteration.
 * We should find a way to produce an executable AST directly from the parser.
 */
final class Compiler
{
    private const PSEUDO_CLASSES = [
        'any-link' => AnyLinkEvaluator::class,
        'checked' => CheckedEvaluator::class,
        'default' => DefaultEvaluator::class,
        'disabled' => DisabledEvaluator::class,
        'empty' => EmptyEvaluator::class,
        'first-child' => FirstChildEvaluator::class,
        'first-of-type' => FirstOfTypeEvaluator::class,
        'last-child' => LastChildEvaluator::class,
        'last-of-type' => LastOfTypeEvaluator::class,
        'link' => AnyLinkEvaluator::class,
        'only-child' => OnlyChildEvaluator::class,
        'only-of-type' => OnlyOfTypeEvaluator::class,
        'optional' => OptionalEvaluator::class,
        'read-only' => ReadOnlyEvaluator::class,
        'read-write' => ReadWriteEvaluator::class,
        'required' => RequiredEvaluator::class,
        'root' => RootEvaluator::class,
        'scope' => ScopeEvaluator::class,
        'selected' => SelectedEvaluator::class,
    ];

    /**
     * @var array<string, EvaluatorInterface>
     */
    private static array $EVALUATOR_CACHE = [];

    private bool $currentSelectorMatchesNothing = false;

    public function compile(Selector $selector): EvaluatorInterface
    {
        $this->currentSelectorMatchesNothing = false;
        return $this->doCompile($selector);
    }

    private function doCompile(Selector $selector): EvaluatorInterface
    {
        switch ($selector::class) {
            case SelectorList::class:
                /** @var SelectorList $selector */
                $evaluators = array_map(fn($selector) => $this->doCompile($selector), $selector->selectors);
                return match (\count($evaluators)) {
                    0 => new NeverEvaluator(),
                    1 => $evaluators[0],
                    default => new ListEvaluator($evaluators),
                };
            case ComplexSelector::class:
                /** @var ComplexSelector $selector */
                return $this->compileComplexSelector($selector);
            case CompoundSelector::class:
                /** @var CompoundSelector $selector */
                $evaluators = array_map(fn($selector) => $this->doCompile($selector), $selector->selectors);
                return match (\count($evaluators)) {
                    0 => new NeverEvaluator(),
                    1 => $evaluators[0],
                    default => new CompoundEvaluator($evaluators),
                };
            case RelativeSelector::class:
                return $this->compileRelativeSelector($selector);
            case UniversalSelector::class:
                return new UniversalEvaluator();
            case TypeSelector::class:
                /** @var TypeSelector $selector */
                return new TypeEvaluator($selector->tagName, $selector->namespace);
            case IdSelector::class:
                /** @var IdSelector $selector */
                return new IdEvaluator($selector->id);
            case ClassSelector::class:
                /** @var ClassSelector $selector */
                return new ClassEvaluator($selector->class);
            case AttributeSelector::class:
                /** @var AttributeSelector $selector */
                return new AttributeEvaluator(
                    $selector->attribute,
                    $selector->operator,
                    $selector->value,
                    $selector->namespace,
                    $selector->forceCase
                );
            case PseudoClassSelector::class:
                /** @var PseudoClassSelector $selector */
                return $this->compilePseudoClass($selector);
            case Is::class:
            case Where::class:
                /** @var Is|Where $selector */
                return $this->doCompile($selector->selectorList);
            case Not::class:
                /** @var Not $selector */
                return new NotEvaluator($this->doCompile($selector->selectorList));
            case Has::class:
                /** @var Has $selector */
                return new HasEvaluator($this->doCompile($selector->selectorList));
            case NthChild::class:
                /** @var NthChild $selector */
                $anPlusB = $selector->anPlusB;
                return new NthChildEvaluator(
                    $anPlusB->a,
                    $anPlusB->b,
                    $selector->selectorList ? $this->doCompile($selector->selectorList) : null
                );
            case NthLastChild::class:
                /** @var NthLastChild $selector */
                $anPlusB = $selector->anPlusB;
                return new NthLastChildEvaluator(
                    $anPlusB->a,
                    $anPlusB->b,
                    $selector->selectorList ? $this->doCompile($selector->selectorList) : null
                );
            case NthOfType::class:
                /** @var NthOfType $selector */
                $anPlusB = $selector->anPlusB;
                return new NthOfTypeEvaluator($anPlusB->a, $anPlusB->b);
            case NthLastOfType::class:
                /** @var NthLastOfType $selector */
                $anPlusB = $selector->anPlusB;
                return new NthLastOfTypeEvaluator($anPlusB->a, $anPlusB->b);
            case PseudoElementSelector::class:
                return new PseudoElementEvaluator();
            default:
                throw new UnsupportedSelector((string)$selector);
        }
    }

    private function compileComplexSelector(ComplexSelector $selector): EvaluatorInterface
    {
        return match ($selector->combinator) {
            null => $this->doCompile($selector->lhs),
            default => new ComplexEvaluator(
                $this->doCompile($selector->lhs),
                $selector->combinator,
                $this->doCompile($selector->rhs),
            ),
        };
    }

    private function compilePseudoClass(PseudoClassSelector $selector): EvaluatorInterface
    {
        $class = self::PSEUDO_CLASSES[$selector->name] ?? UnsupportedPseudoClassEvaluator::class;
        if (!isset(self::$EVALUATOR_CACHE[$class])) {
            self::$EVALUATOR_CACHE[$class] = new $class();
        }

        return self::$EVALUATOR_CACHE[$class];
    }

    /**
     * @see https://drafts.csswg.org/selectors-4/#relative
     * @see https://drafts.csswg.org/selectors-4/#absolutizing
     */
    private function compileRelativeSelector(RelativeSelector $selector): EvaluatorInterface
    {
        // 1. If the selector starts with a combinator other than the white space form of the descendant combinator,
        // prepend :scope as the initial compound selector.
        if ($selector->combinator !== Combinator::DESCENDANT) {
            return $this->compileComplexSelector(
                new ComplexSelector(
                    new PseudoClassSelector('scope'),
                    $selector->combinator,
                    $selector->selector,
                )
            );
        }
        // 2. Otherwise, if the selector does not contain any instance of the :scope pseudo-class
        // (either at the top-level or as an argument to a functional pseudo-class),
        // prepend :scope followed by the white space form of the descendant combinator.
        if (!self::containsScopePseudoClass($selector)) {
            return $this->compileComplexSelector(
                new ComplexSelector(
                    new PseudoClassSelector('scope'),
                    Combinator::DESCENDANT,
                    $selector->selector,
                )
            );
        }
        // 3. Otherwise, the selector is already absolute.
        return $this->doCompile($selector->selector);
    }

    private static function containsScopePseudoClass(Selector $selector): bool
    {
        foreach ($selector->simpleSelectors() as $selector) {
            if ($selector instanceof PseudoClassSelector && $selector->name === 'scope') {
                return true;
            }
        }
        return false;
    }
}
