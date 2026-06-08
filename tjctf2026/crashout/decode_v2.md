# crashout v2 deeper diacritic analysis

## Scope

This pass re-ran the challenge from the exact source text, not the paraphrased clue, and implemented the six requested decoder families against the NFD parse.

Files used:

- [`cipher.txt`](/home/alchemy/Downloads/CTF/tjscctf/crypto/crashout/cipher.txt)
- [`decode_v2.py`](/home/alchemy/Downloads/CTF/tjscctf/crypto/crashout/decode_v2.py)
- [`tried_flags.txt`](/home/alchemy/Downloads/CTF/tjscctf/crypto/crashout/tried_flags.txt)

## Exact parse

Source:

```text
Ỉ H̰OP̀É ÎM̊ OKAỸ ḂÜT̄ Ỉ ŇËẼD̄ H̃ȨL̂ṔṔP̌
```

NFD:

```text
Ỉ H̰OP̀É ÎM̊ OKAỸ ḂÜT̄ Ỉ ŇËẼD̄ H̃ȨL̂ṔṔP̌
```

Base text:

```text
I HOPE IM OKAY BUT I NEED HELPPP
```

No-space base text:

```text
IHOPEIMOKAYBUTINEEDHELPPP
```

That is 25 letters exactly, which matters because it forms a clean 5x5.

## Requested interpretations

### 1. Combining codepoint minus `0x0300`

Values:

```text
[9, 48, 0, 1, 2, 10, 3, 7, 8, 4, 9, 12, 8, 3, 4, 3, 39, 2, 1, 1, 12]
```

Letter renderings:

```text
A=0: J?ABCKDHIEJMIDED?CBBM
A=1: K?BCDLEIJFKNJEFE?DCCN
```

No plausible plaintext.

### 2. Position-of-base-char fill

Using the confirmed first-occurrence mark map to `UNDERSCROLLS`:

```text
with spaces: U N_DE RS ___C ROL U LOCL CSREEL
25-grid fill: UN_DERS___CROLULOCLCSREEL
overlaying unmarked base letters: UNODERSOKACROLULOCLCSREEL
```

This is the strongest structural output in the v2 pass.

Observations:

- `HOPE` becomes `NODE` if the unmarked `O` is preserved.
- The tail `LOCLCSREEL` looks strongly like a distorted `LOOKCLOSER`.
- The whole 25-letter overlay is non-random enough that a second-stage transposition still looks likely.

### 3. Bit-pack

Using the first four unique marks in order as a 2-bit alphabet only gives:

```text
bitstream: 00011011001111
bytes: [27]
ascii: .
```

I also spot-checked other 4-mark subsets/mappings. Nothing produced a convincing ASCII plaintext.

### 4. Mark count per base char

Every non-space character has either zero or one mark, so the count channel collapses to:

```text
0 marks: OOKA
1 mark : IHPEIMYBUTINEEDHELPPP
```

That does not decode directly, but the four unmarked letters are exactly the four holes in the 5x5 placement.

### 5. Top vs bottom marks

Using `U+0320` as the rough above/below split:

```text
top stream (first-occ map):    UDERSCROLULOCLCREEL
bottom stream (first-occ map): NS
top base letters:              IPEIMYBUTINEEDHLPPP
bottom base letters:           HE
```

This did not directly unlock the tail.

### 6. `HELPPP` tail

Tail data:

```text
base: HELPPP
unique-order ids: [7, 12, 5, 4, 4, 11]
first-occ letters: CSREEL
alpha-rank letters: KCDAAB
```

Again, `CSREEL` is not English as-is, but it remains close enough to a mangled `CLOSER` that it should not be ignored.

## Existing clue still confirmed

The old alphabetic core-name rank shift still gives:

```text
A VOID EC OKAN VPK A LZTU WBHOON
```

No new route over that 5x5 made it submission-worthy.

## New practical conclusion

The v2 pass does **not** support the old semantic guess of “avoid the combining marks”.

What it does support:

1. `UNDERSCROLLS` is a genuine stage-one clue from unique mark first-occurrence order.
2. The 25-letter base text is intentionally arranged for a 5x5 structural read.
3. The overlay/tail output points toward a second-stage “look closer” style instruction more than a literal “avoid …” flag body.

## Submissions used in this pass

Appended automatically by [`submit_flag.py`](/home/alchemy/Downloads/CTF/tjscctf/crypto/crashout/submit_flag.py):

- `tjctf{LOOK_CLOSER}` -> rejected
- `tjctf{LOOK̲CLOSER}` -> rejected
- `tjctf{UNDERSCROLL_LOOK_CLOSER}` -> rejected
- `tjctf{UNDERSCROLL̲LOOK̲CLOSER}` -> rejected

## Reproduce

```bash
python3 decode_v2.py
python3 analyze.py
tail -n 8 tried_flags.txt
```

## Current best lead

The best remaining lead is still:

```text
UNDERSCROLLS
```

plus a second structural transform over the 5x5 placement, with the tail likely encoding something in the `LOOK CLOSER` family.

At end of this pass the flag is unresolved.
