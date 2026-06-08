#!/usr/bin/env python3

import itertools
import unicodedata as ud
from collections import Counter, defaultdict

from wordfreq import zipf_frequency


SOURCE_TEXT = open("cipher.txt", encoding="utf-8").read().strip()


def parse_nfd_entries(text):
    nfd = ud.normalize("NFD", text)
    entries = []
    i = 0
    while i < len(nfd):
        ch = nfd[i]
        if ch == " ":
            entries.append({"base": " ", "marks": [], "raw_kind": "space"})
            i += 1
            continue
        if ud.combining(ch):
            raise ValueError(f"unexpected leading combining mark at {i}")
        base = ch
        marks = []
        i += 1
        while i < len(nfd) and ud.combining(nfd[i]):
            marks.append(nfd[i])
            i += 1
        entries.append({"base": base, "marks": marks, "raw_kind": None})
    return entries


def annotate_raw_kind(entries, raw_text):
    logical = []
    i = 0
    while i < len(raw_text):
        ch = raw_text[i]
        if ch == " ":
            i += 1
            continue
        if ud.combining(ch):
            raise ValueError(f"unexpected raw combining mark at {i}")
        nfd = ud.normalize("NFD", ch)
        if len(nfd) > 1:
            logical.append((nfd[0], list(nfd[1:]), "precomposed"))
            i += 1
            continue
        base = ch
        i += 1
        marks = []
        while i < len(raw_text) and ud.combining(raw_text[i]):
            marks.append(raw_text[i])
            i += 1
        logical.append((base, marks, "decomposed" if marks else "plain"))

    non_space_entries = [entry for entry in entries if entry["base"] != " "]
    if len(non_space_entries) != len(logical):
        raise ValueError("logical/raw parse length mismatch")

    for entry, (base, marks, raw_kind) in zip(non_space_entries, logical):
        if entry["base"] != base or entry["marks"] != marks:
            raise ValueError(f"parse mismatch: {entry} != {(base, marks, raw_kind)}")
        entry["raw_kind"] = raw_kind


def mark_name(mark):
    return ud.name(mark).replace("COMBINING ", "")


def core_name(mark):
    return mark_name(mark).replace(" ACCENT", "")


def only_marked(entries):
    return [(idx, entry["base"], entry["marks"][0]) for idx, entry in enumerate(entries) if entry["marks"]]


def unique_marks(marked):
    out = []
    for _, _, mark in marked:
        if mark not in out:
            out.append(mark)
    return out


def shift_letter(ch, amount, direction=-1):
    base = ord(ch) - 65
    return chr((base + direction * amount) % 26 + 65)


def render_mapped_marks(entries, mark_map, unmarked="_", spaces=True):
    out = []
    for entry in entries:
        base = entry["base"]
        marks = entry["marks"]
        if base == " ":
            if spaces:
                out.append(" ")
            continue
        if marks:
            out.append(mark_map[marks[0]])
        else:
            out.append(unmarked)
    return "".join(out)


def render_shift(entries, value_map, direction=-1):
    out = []
    for entry in entries:
        base = entry["base"]
        marks = entry["marks"]
        if base == " ":
            out.append(" ")
        elif marks:
            out.append(shift_letter(base, value_map[marks[0]], direction=direction))
        else:
            out.append(base)
    return "".join(out)


def base_text(entries, spaces=True):
    return "".join(entry["base"] for entry in entries if spaces or entry["base"] != " ")


def rows_to_grid(text, n=5):
    if len(text) != n * n:
        raise ValueError(f"expected {n*n} chars, got {len(text)}")
    return [list(text[i : i + n]) for i in range(0, len(text), n)]


def route_rows(grid):
    yield "rows_ltr", "".join("".join(row) for row in grid)
    yield "rows_rtl", "".join("".join(row[::-1]) for row in grid)
    yield "rows_snake", "".join("".join(row if i % 2 == 0 else row[::-1]) for i, row in enumerate(grid))


def route_cols(grid):
    n = len(grid)
    cols = [[grid[r][c] for r in range(n)] for c in range(n)]
    yield "cols_ttb", "".join("".join(col) for col in cols)
    yield "cols_btt", "".join("".join(col[::-1]) for col in cols)
    yield "cols_snake", "".join("".join(col if i % 2 == 0 else col[::-1]) for i, col in enumerate(cols))


def route_spiral(grid):
    g = [row[:] for row in grid]
    out = []
    while g:
        out.extend(g.pop(0))
        if not g:
            break
        for row in g:
            out.append(row.pop())
        if g and g[-1]:
            out.extend(g.pop()[::-1])
        if g and g[0]:
            for row in g[::-1]:
                out.append(row.pop(0))
        g = [row for row in g if row]
    return "".join(out)


def route_rev_spiral(grid):
    coords = [[(r, c) for c in range(len(grid))] for r in range(len(grid))]
    g = [row[:] for row in coords]
    out = []
    while g:
        out.extend(g.pop(0))
        if not g:
            break
        for row in g:
            out.append(row.pop())
        if g and g[-1]:
            out.extend(g.pop()[::-1])
        if g and g[0]:
            for row in g[::-1]:
                out.append(row.pop(0))
        g = [row for row in g if row]
    return "".join(grid[r][c] for r, c in out[::-1])


def simple_segment_score(text):
    text = text.replace("_", "")
    if not text:
        return -1e9, []
    dp = [(-1e9, []) for _ in range(len(text) + 1)]
    dp[0] = (0.0, [])
    for i in range(len(text)):
        score, parts = dp[i]
        if score < -1e8:
            continue
        for j in range(i + 2, min(len(text), i + 12) + 1):
            word = text[i:j].lower()
            zf = zipf_frequency(word, "en")
            if zf < 2.0:
                continue
            cand = score + zf
            if cand > dp[j][0]:
                dp[j] = (cand, parts + [text[i:j]])
        if score - 6 > dp[i + 1][0]:
            dp[i + 1] = (score - 6, parts + [text[i : i + 1]])
    return dp[-1]


def route_report(name, text):
    grid = rows_to_grid(text.replace(" ", ""))
    out = []
    routes = list(route_rows(grid)) + list(route_cols(grid))
    routes.extend([("spiral", route_spiral(grid)), ("rev_spiral", route_rev_spiral(grid))])
    for route_name, routed in routes:
        score, parts = simple_segment_score(routed)
        out.append((score, route_name, routed, parts))
    out.sort(reverse=True)
    print(f"\n{name} 5x5 routes:")
    for score, route_name, routed, parts in out[:8]:
        print(f"  {route_name:11} {score:6.2f} {routed} | {parts}")


def interpretation_codepoint(marked):
    vals = [ord(mark) - 0x300 for _, _, mark in marked]
    letters_a0 = []
    letters_a1 = []
    for val in vals:
        letters_a0.append(chr(65 + val) if 0 <= val < 26 else "?")
        letters_a1.append(chr(65 + val + 1) if 0 <= val < 25 else "?")
    return {
        "values": vals,
        "letters_a0": "".join(letters_a0),
        "letters_a1": "".join(letters_a1),
    }


def interpretation_position_fill(entries, mark_map):
    no_space = render_mapped_marks(entries, mark_map, unmarked="_", spaces=False)
    with_space = render_mapped_marks(entries, mark_map, unmarked="_", spaces=True)
    overlay_base = []
    for entry in entries:
        if entry["base"] == " ":
            continue
        if entry["marks"]:
            overlay_base.append(mark_map[entry["marks"][0]])
        else:
            overlay_base.append(entry["base"])
    return {
        "with_spaces": with_space,
        "grid_fill": no_space,
        "overlay_base": "".join(overlay_base),
    }


def interpretation_bitpack(marked):
    unique = unique_marks(marked)
    four = unique[:4]
    mark_to_bits = {four[i]: f"{i:02b}" for i in range(4)}
    usable = [mark_to_bits[mark] for _, _, mark in marked if mark in mark_to_bits]
    bitstream = "".join(usable)
    bytes_out = []
    for i in range(0, len(bitstream) - 7, 8):
        bytes_out.append(int(bitstream[i : i + 8], 2))
    ascii_out = "".join(chr(v) if 32 <= v < 127 else "." for v in bytes_out)
    return {
        "mapping": {mark_name(mark): bits for mark, bits in mark_to_bits.items()},
        "bitstream": bitstream,
        "bytes": bytes_out,
        "ascii": ascii_out,
    }


def interpretation_count(entries, first_map):
    counts = [len(entry["marks"]) for entry in entries if entry["base"] != " "]
    grouped = defaultdict(list)
    for entry in entries:
        if entry["base"] == " ":
            continue
        grouped[len(entry["marks"])].append(entry["base"])
    return {
        "counts": counts,
        "grouped": {k: "".join(v) for k, v in sorted(grouped.items())},
        "ones_as_marks": render_mapped_marks(entries, first_map, unmarked="_", spaces=False),
    }


def interpretation_top_bottom(marked, first_map, alpha_rank):
    top = []
    bottom = []
    for _, base, mark in marked:
        payload = {
            "base": base,
            "first": first_map[mark],
            "alpha": chr(64 + alpha_rank[mark]),
        }
        if ord(mark) < 0x320:
            top.append(payload)
        else:
            bottom.append(payload)
    return {
        "top_first": "".join(item["first"] for item in top),
        "bottom_first": "".join(item["first"] for item in bottom),
        "top_alpha": "".join(item["alpha"] for item in top),
        "bottom_alpha": "".join(item["alpha"] for item in bottom),
        "top_base": "".join(item["base"] for item in top),
        "bottom_base": "".join(item["base"] for item in bottom),
    }


def interpretation_tail(entries, first_map, alpha_rank):
    tail = [entry for entry in entries if entry["base"] != " "][-6:]
    marks = [entry["marks"][0] for entry in tail]
    return {
        "base": "".join(entry["base"] for entry in tail),
        "unique_order": [unique_marks(only_marked(entries)).index(mark) + 1 for mark in marks],
        "first_letters": "".join(first_map[mark] for mark in marks),
        "alpha_letters": "".join(chr(64 + alpha_rank[mark]) for mark in marks),
        "mark_names": [core_name(mark) for mark in marks],
    }


def representation_report(entries):
    marked_entries = [entry for entry in entries if entry["marks"]]
    pattern = "".join("P" if entry["raw_kind"] == "precomposed" else "D" for entry in marked_entries)
    bits_p1 = "".join("1" if ch == "P" else "0" for ch in pattern)
    bits_d1 = "".join("1" if ch == "D" else "0" for ch in pattern)
    return {
        "pattern": pattern,
        "bits_p1": bits_p1,
        "bits_d1": bits_d1,
    }


def main():
    entries = parse_nfd_entries(SOURCE_TEXT)
    annotate_raw_kind(entries, SOURCE_TEXT)
    marked = only_marked(entries)
    uniq = unique_marks(marked)

    first_map = {mark: ch for mark, ch in zip(uniq, "UNDERSCROLLS")}
    alpha_marks = sorted(uniq, key=core_name)
    alpha_rank = {mark: i for i, mark in enumerate(alpha_marks, 1)}
    alpha_letters = {mark: chr(64 + alpha_rank[mark]) for mark in uniq}

    print("Base text:", base_text(entries))
    print("Base text (no spaces):", base_text(entries, spaces=False))
    print("Marked positions:", len(marked))
    print("Unique marks:", len(uniq))

    print("\nPer-character parse:")
    for idx, entry in enumerate(entries):
        if entry["base"] == " ":
            print(f"{idx:02d} SPACE")
            continue
        marks = entry["marks"]
        if marks:
            cp = " ".join(f"U+{ord(mark):04X}" for mark in marks)
            names = " | ".join(mark_name(mark) for mark in marks)
        else:
            cp = "-"
            names = "-"
        print(f"{idx:02d} {entry['base']} | {cp} | {names} | {entry['raw_kind']}")

    print("\nInterpretation 1: codepoint - 0x0300")
    interp1 = interpretation_codepoint(marked)
    print("  values:", interp1["values"])
    print("  A=0 :", interp1["letters_a0"])
    print("  A=1 :", interp1["letters_a1"])

    print("\nInterpretation 2: position-of-base-char fill")
    interp2_first = interpretation_position_fill(entries, first_map)
    interp2_alpha = interpretation_position_fill(entries, alpha_letters)
    print("  first-occurrence map (spaces):", interp2_first["with_spaces"])
    print("  first-occurrence grid fill   :", interp2_first["grid_fill"])
    print("  first-occurrence overlay     :", interp2_first["overlay_base"])
    print("  alpha-rank fill (spaces)     :", interp2_alpha["with_spaces"])
    print("  alpha-rank overlay           :", interp2_alpha["overlay_base"])

    print("\nInterpretation 3: bit-pack first four marks")
    interp3 = interpretation_bitpack(marked)
    print("  mapping:", interp3["mapping"])
    print("  bitstream:", interp3["bitstream"])
    print("  bytes:", interp3["bytes"])
    print("  ascii:", interp3["ascii"])

    print("\nInterpretation 4: mark count per base char")
    interp4 = interpretation_count(entries, first_map)
    print("  counts:", interp4["counts"])
    print("  grouped base chars by count:", interp4["grouped"])
    print("  mapped singles in 25-grid  :", interp4["ones_as_marks"])

    print("\nInterpretation 5: top vs bottom")
    interp5 = interpretation_top_bottom(marked, first_map, alpha_rank)
    for key, value in interp5.items():
        print(f"  {key}: {value}")

    print("\nInterpretation 6: HELPPP tail")
    interp6 = interpretation_tail(entries, first_map, alpha_rank)
    for key, value in interp6.items():
        print(f"  {key}: {value}")

    print("\nExisting alpha-rank shift clue")
    shifted = render_shift(entries, alpha_rank, direction=-1)
    shifted_flat = shifted.replace(" ", "")
    print("  shifted:", shifted)
    print("  flat   :", shifted_flat)

    if len(shifted_flat) == 25:
        route_report("alpha-shift", shifted_flat)

    route_report("first-occurrence grid fill", interp2_first["grid_fill"])
    route_report("first-occurrence overlay", interp2_first["overlay_base"])

    print("\nRepresentation channel")
    rep = representation_report(entries)
    for key, value in rep.items():
        print(f"  {key}: {value}")

    print("\nFrequencies:")
    for mark, count in Counter(mark for _, _, mark in marked).most_common():
        print(f"  {count:02d} {mark} {mark_name(mark)}")


if __name__ == "__main__":
    main()
