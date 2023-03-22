
.PHONY: test coverage codegen

bench: codegen
	composer bench

test: codegen
	composer test

coverage: codegen
	compsoer coverage

codegen: \
	src/Encoding/EncodingLookup.php \
	src/HTML/Tokenizer/Tokenizer.php \
	src/HTML/Tokenizer/EntityLookup.php \

src/Encoding/EncodingLookup.php: codegen/templates/encoding_lookup.php.twig
	php bin/generate-encodings

src/HTML/Tokenizer/Tokenizer.php: codegen/templates/tokenizer.php.twig $(wildcard codegen/templates/tokenizer/*.php.twig)
	php bin/generate-tokenizer.php

src/HTML/Tokenizer/EntityLookup.php: codegen/templates/entity_lookup.php.twig
	php bin/generate-entities.php
