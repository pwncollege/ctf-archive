#!/usr/bin/env python3

import heapq
import itertools
import unicodedata as ud
from collections import defaultdict
from difflib import SequenceMatcher

from wordfreq import zipf_frequency


SOURCE_TEXT = open("cipher.txt", encoding="utf-8").read().strip()


def parse_nfd_entries(text):
    nfd = ud.normalize("NFD", text)
    entries = []
    i = 0
    while i < len(nfd):
        ch = nfd[i]
        if ch == " ":
            i += 1
            continue
        if ud.combining(ch):
            raise ValueError(f"unexpected leading combining mark at {i}")
        base = ch
        i += 1
        marks = []
        while i < len(nfd) and ud.combining(nfd[i]):
            marks.append(nfd[i])
            i += 1
        entries.append({"base": base, "mark": marks[0] if marks else None})
    return entries


def mark_name(mark):
    return ud.name(mark).replace("COMBINING ", "")


def core_name(mark):
    return mark_name(mark).replace(" ACCENT", "")


def base_text(entries):
    return "".join(entry["base"] for entry in entries)


def rows_to_grid(text, n=5):
    return [list(text[i : i + n]) for i in range(0, len(text), n)]


def unique_marks(entries):
    out = []
    for entry in entries:
        mark = entry["mark"]
        if mark and mark not in out:
            out.append(mark)
    return out


def first_occurrence_map(entries, letters):
    out = {}
    for entry in entries:
        mark = entry["mark"]
        if mark and mark not in out:
            out[mark] = letters[len(out)]
    return out


def render_mark_stream(entries, mapping):
    return "".join(mapping[entry["mark"]] for entry in entries if entry["mark"])


def render_grid_fill(entries, mapping, fill_unmarked="_"):
    out = []
    for entry in entries:
        if entry["mark"]:
            out.append(mapping[entry["mark"]])
        else:
            out.append(fill_unmarked)
    return "".join(out)


def shift_letter(ch, amount, direction=-1):
    base = ord(ch) - 65
    return chr((base + direction * amount) % 26 + 65)


def alpha_rank_shift(entries):
    uniq = unique_marks(entries)
    rank = {mark: i for i, mark in enumerate(sorted(uniq, key=core_name), 1)}
    out = []
    for entry in entries:
        if entry["mark"]:
            out.append(shift_letter(entry["base"], rank[entry["mark"]]))
        else:
            out.append(entry["base"])
    return "".join(out)


def mask_positions(entries):
    return [i for i, entry in enumerate(entries) if entry["mark"]]


def grid_routes_indices(n=5):
    grids = []

    def add(name, coords):
        grids.append((name, [r * n + c for r, c in coords]))

    coords = [[(r, c) for c in range(n)] for r in range(n)]
    add("rows_ltr", [cell for row in coords for cell in row])
    add("rows_rtl", [cell for row in coords for cell in row[::-1]])
    add(
        "rows_snake",
        [cell for i, row in enumerate(coords) for cell in (row if i % 2 == 0 else row[::-1])],
    )

    cols = [[(r, c) for r in range(n)] for c in range(n)]
    add("cols_ttb", [cell for col in cols for cell in col])
    add("cols_btt", [cell for col in cols for cell in col[::-1]])
    add(
        "cols_snake",
        [cell for i, col in enumerate(cols) for cell in (col if i % 2 == 0 else col[::-1])],
    )

    spiral = []
    g = [row[:] for row in coords]
    while g:
        spiral.extend(g.pop(0))
        if not g:
            break
        for row in g:
            spiral.append(row.pop())
        if g and g[-1]:
            spiral.extend(g.pop()[::-1])
        if g and g[0]:
            for row in g[::-1]:
                spiral.append(row.pop(0))
        g = [row for row in g if row]
    add("spiral", spiral)
    add("rev_spiral", spiral[::-1])
    add("diag_main", [(i, i) for i in range(n)])
    add("diag_anti", [(i, n - 1 - i) for i in range(n)])
    return grids


def full_grid_routes_indices(n=5):
    return [(name, route) for name, route in grid_routes_indices(n) if len(route) == n * n]


def route_text(text, route):
    return "".join(text[i] for i in route)


def simple_segment_score(text):
    text = text.replace("_", "")
    if not text:
        return -1e9, ()
    dp = [(-1e9, ()) for _ in range(len(text) + 1)]
    dp[0] = (0.0, ())
    for i in range(len(text)):
        score, parts = dp[i]
        if score < -1e8:
            continue
        for j in range(i + 2, min(len(text), i + 12) + 1):
            word = text[i:j].lower()
            zf = zipf_frequency(word, "en")
            if zf < 2.0:
                continue
            cand = (score + zf, parts + (text[i:j],))
            if cand[0] > dp[j][0]:
                dp[j] = cand
        if score - 6 > dp[i + 1][0]:
            dp[i + 1] = (score - 6, parts + (text[i : i + 1],))
    return dp[-1]


def top_route_report(name, text, limit=10):
    routes = grid_routes_indices(5)
    scored = []
    for route_name, route in routes:
        routed = route_text(text, route)
        score, parts = simple_segment_score(routed)
        scored.append((score, route_name, routed, parts))
    scored.sort(reverse=True)
    print(f"\n{name}")
    for score, route_name, routed, parts in scored[:limit]:
        print(f"  {route_name:10} {score:6.2f} {routed} | {list(parts)}")


def fill_mask(sequence, mask_route, read_route, holes="_", n=5):
    mask = set(mask_positions(ENTRIES))
    ordered_mask = [i for i in mask_route if i in mask]
    if len(ordered_mask) != len(sequence):
        raise ValueError("mask/sequence length mismatch")
    cells = [holes] * (n * n)
    for idx, ch in zip(ordered_mask, sequence):
        cells[idx] = ch
    filled = "".join(cells)
    return filled, route_text(filled, read_route)


def top_mask_fill_reports(sequence_name, sequence, limit=15):
    routes = full_grid_routes_indices(5)
    heap = []
    for place_name, place_route in routes:
        for read_name, read_route in routes:
            filled, routed = fill_mask(sequence, place_route, read_route)
            score, parts = simple_segment_score(routed)
            item = (score, place_name, read_name, filled, routed, parts)
            if len(heap) < limit:
                heapq.heappush(heap, item)
            elif score > heap[0][0]:
                heapq.heapreplace(heap, item)
    print(f"\nmask-fill search: {sequence_name}")
    for score, place_name, read_name, filled, routed, parts in sorted(heap, reverse=True):
        print(
            f"  {score:6.2f} place={place_name:10} read={read_name:10} "
            f"{routed} | {list(parts)} | fill={filled}"
        )


def similarity_search(name, text, targets, limit=10):
    g = rows_to_grid(text, 5)
    heap = []
    for row_perm in itertools.permutations(range(5)):
        rg = [g[i] for i in row_perm]
        for col_perm in itertools.permutations(range(5)):
            cand = "".join("".join(row[j] for j in col_perm) for row in rg)
            best_target = None
            best_ratio = -1.0
            for target in targets:
                ratio = SequenceMatcher(None, cand, target).ratio()
                if ratio > best_ratio:
                    best_ratio = ratio
                    best_target = target
            item = (best_ratio, cand, best_target, row_perm, col_perm)
            if len(heap) < limit:
                heapq.heappush(heap, item)
            elif best_ratio > heap[0][0]:
                heapq.heapreplace(heap, item)
    print(f"\nrow/col permutation search: {name}")
    for ratio, cand, target, row_perm, col_perm in sorted(heap, reverse=True):
        print(f"  {ratio:0.3f} {cand} | target={target} | rows={row_perm} cols={col_perm}")


def pairwise_coordinate_reads(values, label):
    base = base_text(ENTRIES)
    print(f"\npairwise coordinate reads: {label}")
    for mode in ("mod5", "one_to_five"):
        out = []
        vals = values[:]
        if len(vals) % 2:
            vals = vals[:-1]
        for a, b in zip(vals[::2], vals[1::2]):
            if mode == "mod5":
                r = a % 5
                c = b % 5
            else:
                r = (a - 1) % 5
                c = (b - 1) % 5
            out.append(base[r * 5 + c])
        text = "".join(out)
        score, parts = simple_segment_score(text)
        print(f"  {mode:11} {score:6.2f} {text} | {list(parts)}")


ENTRIES = parse_nfd_entries(SOURCE_TEXT)


def main():
    base = base_text(ENTRIES)
    first_map = first_occurrence_map(ENTRIES, "UNDERSCROLLS")
    mark_stream = render_mark_stream(ENTRIES, first_map)
    clue_grid = render_grid_fill(ENTRIES, first_map, "_")
    overlay_grid = "".join(
        mapped if mapped is not None else entry["base"]
        for entry, mapped in zip(
            ENTRIES,
            [first_map[entry["mark"]] if entry["mark"] else None for entry in ENTRIES],
        )
    )
    rank_text = alpha_rank_shift(ENTRIES).replace(" ", "")

    uniq = unique_marks(ENTRIES)
    mod_values = [ord(mark) - 0x0300 for mark in uniq]
    full_mod_values = [ord(entry["mark"]) - 0x0300 for entry in ENTRIES if entry["mark"]]
    full_rank_values = [{mark: i for i, mark in enumerate(uniq, 1)}[entry["mark"]] for entry in ENTRIES if entry["mark"]]
    mod25_read = "".join(base[v % 25] for v in full_mod_values)

    print("base:", base)
    print("mark stream:", mark_stream)
    print("clue grid:", clue_grid)
    print("overlay grid:", overlay_grid)
    print("rank text:", rank_text)
    print("mod25 read:", mod25_read)
    print("unique marks:")
    for i, mark in enumerate(uniq, 1):
        print(f"  {i:02d} {mark} {mark_name(mark)}")

    top_route_report("base grid routes", base)
    top_route_report("clue grid routes", clue_grid)
    top_route_report("overlay grid routes", overlay_grid)
    top_route_report("rank routes", rank_text)

    top_mask_fill_reports("first-occurrence mark stream", mark_stream)
    top_mask_fill_reports("mod25 direct base read", mod25_read)

    similarity_search(
        "overlay vs likely meta phrases",
        overlay_grid,
        [
            "UNDERSCROLLLOOKCLOSER",
            "LOOKCLOSERUNDERSCROLL",
            "CLOSERLOOKUNDERSCROLL",
            "SCROLLUNDERLOOKCLOSER",
            "LOOKUNDERSCROLLCLOSER",
        ],
    )

    pairwise_coordinate_reads(full_mod_values, "codepoint-minus-0300")
    pairwise_coordinate_reads(full_rank_values, "first-occurrence ranks")


if __name__ == "__main__":
    main()
