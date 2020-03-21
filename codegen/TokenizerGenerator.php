<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Codegen;

use ju1ius\HtmlParser\Codegen\Twig\TokenizerExtension;
use ju1ius\HtmlParser\Tokenizer\TokenizerStates;
use Twig\Environment;

final class TokenizerGenerator extends AbstractCodeGenerator
{
    protected function getTemplateFile(): string
    {
        return 'tokenizer.php.twig';
    }

    protected function getOutputFile(): string
    {
        return __DIR__.'/../src/Tokenizer/Tokenizer.php';
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

        $ref = new \ReflectionClass(TokenizerStates::class);
        foreach ($ref->getConstants() as $name => $constant) {
            $context['state_names'][] = $name;
        }

        return $context;
    }
}
