
# Selector support

## Selectors level 4

https://drafts.csswg.org/selectors-4/

### Logical combinations

https://drafts.csswg.org/selectors-4/#logical-combination

* [x] `:is()`
* [x] `:not()`
* [x] `:where()`
* [x] `:has()`

### Elemental selectors

https://drafts.csswg.org/selectors-4/#elemental-selectors

* [x] type selector
* [x] universal selector
* [ ] namespaces in elemental selectors
* [ ] [:defined](https://drafts.csswg.org/selectors-4/#the-defined-pseudo)

### Attribute selectors

https://drafts.csswg.org/selectors-4/#attribute-selectors

* [x] `[att]`
* [x] `[att=val]`
* [x] `[att~=val]`
* [x] `[att|=val]`
* [x] `[att^=val]`
* [x] `[att$=val]`
* [x] `[att*=val]`

### Linguistic pseudo-classes

* [ ] `:dir()`
* [ ] `:lang()`

### Location pseudo-classes

* [x] `:any-link`
* [x] `:link` (same as :any-link)
* [x] `:visited` (never matches)
* [x] `:local-link`
* [x] `:target` (never matches)
* [x] `:target-within` (never matches)
* [x] `:scope`

### User action pseudo-classes

* [x] `:hover` (never matches)
* [x] `:active` (never matches)
* [x] `:focus` (never matches)
* [x] `:focus-visible` (never matches)
* [x] `:focus-within` (never matches)

### Time-dimensional pseudo-classes

* [x] `:current` (never matches)
* [x] `:past` (never matches)
* [x] `:future` (never matches)

### Resource state pseudo-classes

* [x] `:playing` (never matches)
* [x] `:paused` (never matches)
* [x] `:seeking` (never matches)
* [x] `:buffering` (never matches)
* [x] `:stalled` (never matches)
* [x] `:muted` (never matches)
* [x] `:volume-locked` (never matches)

### Input pseudo-classes

* [x] `:enabled`
* [x] `:disabled`
* [x] `:read-only`
* [x] `:read-write`
* [ ] `:placeholder-shown`
* [x] `:default`
* [x] `:checked`
* [ ] `:indeterminate`
* [ ] `:blank`
* [x] `:valid` (never matches)
* [x] `:invalid` (never matches)
* [x] `:in-range` (never matches)
* [x] `:out-of-range` (never matches)
* [x] `:required`
* [x] `:optional`
* [x] `:user-valid` (never matches)
* [x] `:user-invalid` (never matches)

### Tree-structural pseudo-classes

* [x] `:root`
* [x] `:empty`
* [x] `:nth-child()`
* [x] `:nth-last-child()`
* [x] `:first-child`
* [x] `:last-child`
* [x] `:only-child`
* [x] `:nth-of-type()`
* [x] `:nth-last-of-type()`
* [x] `:first-of-type`
* [x] `:last-of-type`
* [x] `:only-of-type`

### Combinators

* [x] ` ` descendant
* [x] `>` child
* [x] `+` next-sibling
* [x] `~` subsequent-sibling

### Grid-structural selectors

* [ ] ` || ` column combinator
* [x] `:nth-col()` (never matches)
* [x] `:nth-last-col()` (never matches)

### Pseudo elements

https://www.w3.org/TR/css-pseudo-4

All pseudo elements are parsed but none of them actually match anything.
