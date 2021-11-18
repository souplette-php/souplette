<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer;

final class EntitySearch extends EntitySearchNode
{
    private static self $instance;

    public static function create(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
            foreach (EntityLookup::NAMED_ENTITIES as $entity => $unicodeValue) {
                self::$instance->add($entity, $unicodeValue);
            }
        }

        return self::$instance;
    }

    private function __construct() {}

    public function add(string $key, string $value): void
    {
        $node = $this;
        for ($i = 0; $i < \strlen($key); $i++) {
            $char = $key[$i];
            if (!isset($node->children[$char])) {
                $node->children[$char] = new EntitySearchNode();
            }
            $node = $node->children[$char];
        }
        $node->value = $value;
    }
}
