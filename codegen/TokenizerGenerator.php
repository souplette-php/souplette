<?php declare(strict_types=1);

namespace Souplette\Codegen;

use Souplette\Codegen\Twig\TokenizerExtension;
use Souplette\Html\Tokenizer\TokenizerState;
use Twig\Environment;

final class TokenizerGenerator extends AbstractCodeGenerator
{
    protected function getTemplateFile(): string
    {
        return 'tokenizer.php.twig';
    }

    protected function getOutputFile(): string
    {
        return __DIR__.'/../src/Html/Tokenizer/Tokenizer.php';
    }

    protected function createEnvironment(): Environment
    {
        $twig = parent::createEnvironment();
        $twig->addExtension(new TokenizerExtension());

        return $twig;
    }

    protected function createContext(): array
    {
        $context = [
            'state_names' => [],
        ];

        foreach (TokenizerState::cases() as $case) {
            $context['state_names'][] = $case->name;
        }

        return $context;
    }
}
