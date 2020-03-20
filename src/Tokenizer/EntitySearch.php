<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer;

final class EntitySearch extends EntitySearchNode
{
    public static function create(): self
    {
        // TODO: this could become a singleton to improve speed over memory.
        $root = new self();
        foreach (EntityLookup::NAMED_ENTITIES as $entity => $unicodeValue) {
            $root->add($entity, $unicodeValue);
        }

        return $root;
    }

    private function __construct() {}

    public function add(string $key, string $value): void
    {
        $node = $this;
        for ($i = 0; $i < strlen($key); $i++) {
            $char = $key[$i];
            if (!isset($node->children[$char])) {
                $node->children[$char] = new EntitySearchNode();
            }
            $node = $node->children[$char];
        }
        $node->value = $value;
    }
}
