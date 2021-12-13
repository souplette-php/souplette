# HTML
- [ ] Tokenizer
  - [x] character buffering to reduce the number of token objects ?
  - [ ] Since we now buffer some character tokens, add tests for whitespace handling,
        i.e. for when the tree builder needs to ignore whitespace-only tokens
  - [ ] Check places where we could use chars_(while|until) instead of consuming one byte at a time.
      Typically, when the spec says « Ignore the character »
- [ ] TreeBuilder
  - [ ] parse errors
- [ ] Serializer
    - [x] boolean attributes

# CSS

- [x] Syntax Module Level 3
- [ ] Selectors Module Level 4
    - [ ] Correctly handle [namespaces](https://drafts.csswg.org/selectors/index.html#type-nmsp)
    - [ ] Correctly handle case sensitivity
    - [ ] Tests
        * https://www.w3.org/Style/CSS/Test/
        * http://test.csswg.org/harness/
        * http://test.csswg.org/suites/selectors-4_dev/nightly-unstable/html/toc.htm
        * http://test.csswg.org/suites/selectors-3_dev/nightly-unstable/xhtml1/toc.xht
        * https://github.com/dperini/nwsapi/tree/master/test
        * https://github.com/facelessuser/soupsieve/tree/master/tests
