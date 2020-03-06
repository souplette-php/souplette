<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Codegen;

use ju1ius\HtmlParser\Codegen\Twig\TokenizerExtension;
use ju1ius\HtmlParser\Tokenizer\TokenizerStates;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TokenizerGenerator
{
    public function generate()
    {
        $twig = $this->createEnvironment();
        $context = $this->createContext();
        $code = $twig->render('tokenizer.php.twig', $context);
        file_put_contents(__DIR__.'/../src/Tokenizer/Tokenizer.php', $code);
    }

    private function createEnvironment(): Environment
    {
        $loader = new FilesystemLoader(__DIR__ . '/templates');
        $twig = new Environment($loader, [
            'strict_variables' => true,
            'autoescape' => false,
        ]);
        $twig->addExtension(new TokenizerExtension());

        return $twig;
    }

    private function createContext(): array
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
