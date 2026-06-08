# crashout decode analysis

## Source

Challenge text:

```text
Ỉ H̰OP̀É ÎM̊ OKAỸ ḂÜT̄ Ỉ ŇËẼD̄ H̃ȨL̂ṔṔP̌
```

## Notes

- Base reading after stripping marks appears to be `I HOPE IM OKAY BUT I NEED HELPPP`.
- Existing teammate intel reports two partial findings:
  - rank-based decode begins with `AVOID`
  - first-occurrence diacritics in order yields `UNDERSCROLLS`

This file is being updated with reproducible analysis and final solve details.

## Reproducible tooling

- [`analyze.py`](/home/alchemy/Downloads/CTF/tjscctf/crypto/crashout/analyze.py) parses the text in NFD form and prints:
  - the base text
  - unique combining marks in first-occurrence order
  - the `UNDERSCROLLS` first-occurrence clue
  - the alphabetic core-name rank shift result
- [`submit_flag.py`](/home/alchemy/Downloads/CTF/tjscctf/crypto/crashout/submit_flag.py) submits a candidate with `curl` and appends the exact API result to [`tried_flags.txt`](/home/alchemy/Downloads/CTF/tjscctf/crypto/crashout/tried_flags.txt).

## Confirmed technical findings

### 1. NFD decomposition

Normalized text:

```text
Ỉ H̰OP̀É ÎM̊ OKAỸ ḂÜT̄ Ỉ ŇËẼD̄ H̃ȨL̂ṔṔP̌
```

Base reading:

```text
I HOPE IM OKAY BUT I NEED HELPPP
```

Unique combining marks in first-occurrence order:

1. `HOOK ABOVE`
2. `TILDE BELOW`
3. `GRAVE ACCENT`
4. `ACUTE ACCENT`
5. `CIRCUMFLEX ACCENT`
6. `RING ABOVE`
7. `TILDE`
8. `DOT ABOVE`
9. `DIAERESIS`
10. `MACRON`
11. `CARON`
12. `CEDILLA`

### 2. First-occurrence clue

Mapping the first new mark to `U`, the second to `N`, and so on gives the clean clue:

```text
UNDERSCROLLS
```

This only appears cleanly on the first occurrence of each unique mark, not on the full repeated mark stream.

### 3. Alphabetic core-name rank shift

Sort the 12 unique marks alphabetically by core name:

1. `ACUTE`
2. `CARON`
3. `CEDILLA`
4. `CIRCUMFLEX`
5. `DIAERESIS`
6. `DOT ABOVE`
7. `GRAVE`
8. `HOOK ABOVE`
9. `MACRON`
10. `RING ABOVE`
11. `TILDE`
12. `TILDE BELOW`

Subtract each mark rank from its base letter, leaving unmarked letters unchanged:

```text
A VOID EC OKAN VPK A LZTU WBHOON
```

Without spaces:

```text
AVOIDECOKANVPKALZTUWBHOON
```

This is the strongest clue supporting the word `AVOID`.

## What I tested

- Simple property-to-shift mappings: codepoint order, first-occurrence order, name length, combining class, frequency, and mixed formulas. None beat the alphabetic-name ranking in plausibility.
- 5x5 grid reads of `AVOIDECOKANVPKALZTUWBHOON`: rows, columns, snake, spiral, row/column permutations. No coherent English read emerged.
- Source-representation splits: precomposed vs decomposed characters, above vs below marks, and mixed decoders. No validated plaintext.
- Crib-constrained brute force: fixing the first four shift values so the first five letters read `AVOID`, then brute-forcing the remaining `8!` assignments. No convincing continuation appeared.

## Submission results

See [`tried_flags.txt`](/home/alchemy/Downloads/CTF/tjscctf/crypto/crashout/tried_flags.txt) for exact timestamps and API responses.

Key outcomes:

- `tjctf{AVOID_UNDERSCROLLS}` was already reported rejected in teammate intel.
- Tested separator variants:
  - combining low line style guess `AVOID̲UNDERSCROLLS`
  - `TILDE BELOW` separator `AVOID̰UNDERSCROLLS`
  - `CEDILLA` separator `AVOIḐUNDERSCROLLS`
  - no separator `AVOIDUNDERSCROLLS`
- Also tested a few phrase variants:
  - `A_VOID_UNDERSCROLLS`
  - `AVOID_SCROLLS`
  - `AVOID_UNDER_SCROLLS`
  - `AVOID_THE_COMBINING_MARKS`
  - `AVOID_THE_UNDERSCROLLS`

All rejected. One rapid-fire attempt hit rate limiting before being evaluated.

## Current best assessment

Two strong clues are real:

1. `AVOID`
2. `UNDERSCROLLS`

But the direct composition of those clues did not validate, even with the most plausible separator variants. The challenge likely has a second-stage transform that has not yet been isolated.
