<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\Functional\NthChild;
use Souplette\Css\Selectors\Node\Functional\NthLastChild;
use Souplette\Css\Selectors\Node\Functional\NthLastOfType;
use Souplette\Css\Selectors\Node\Functional\NthOfType;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\AttributeSelector;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Node\Simple\UniversalSelector;
use Souplette\Css\Selectors\Query\Evaluator\ComplexEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\CompoundEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\Functional\NthChildEvaluator;
use Souplette\Css\Selectors\Query\Evaluator\ListEvaluator;
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
    public function compile(Selector $selector): EvaluatorInterface
    {
        switch ($selector::class) {
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
            //case PseudoClassSelector::class:
            //    return null;
            case NthChild::class:
            case NthLastChild::class:
            case NthOfType::class:
            case NthLastOfType::class:
                /** @var NthChild $selector */
                $anPlusB = $selector->anPlusB;
                return new NthChildEvaluator($anPlusB->a, $anPlusB->b);
            case CompoundSelector::class:
                /** @var CompoundSelector $selector */
                $evaluators = array_map(fn($selector) => $this->compile($selector), $selector->selectors);
                return new CompoundEvaluator($evaluators);
            case ComplexSelector::class:
                /** @var ComplexSelector $selector */
                return $this->compileComplexSelector($selector);
            case SelectorList::class:
                /** @var SelectorList $selector */
                $evaluators = array_map(fn($selector) => $this->compile($selector), $selector->selectors);
                return new ListEvaluator($evaluators);
            default:
                throw new UnsupportedSelector((string)$selector);
        }
    }

    private function compileComplexSelector(ComplexSelector $selector): ComplexEvaluator
    {
        return new ComplexEvaluator(
            $this->compile($selector->lhs),
            $selector->combinator,
            $this->compile($selector->rhs),
        );
    }
}
