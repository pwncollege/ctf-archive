#!/usr/bin/env python3

import itertools
import math
import unicodedata as ud
from collections import Counter

from wordfreq import zipf_frequency


def parse_entries(text):
    nfd = ud.normalize("NFD", text)
    entries = []
    i = 0
    while i < len(nfd):
        ch = nfd[i]
        if ch == " ":
            entries.append((" ", []))
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
        entries.append((base, marks))
    return entries


def mark_name(mark):
    return ud.name(mark).replace("COMBINING ", "")


def core_name(mark):
    return mark_name(mark).replace(" ACCENT", "")


def shift(ch, amount, direction=-1):
    base = ord(ch) - 65
    return chr((base + direction * amount) % 26 + 65)


def render_shift(entries, rank_by_mark, direction=-1):
    out = []
    for base, marks in entries:
        if base == " ":
            out.append(" ")
        elif marks:
            out.append(shift(base, rank_by_mark[marks[0]], direction=direction))
        else:
            out.append(base)
    return "".join(out)


def only_marked(entries, mark_map):
    out = []
    for base, marks in entries:
        if marks:
            out.append(mark_map[marks[0]])
    return "".join(out)


def first_occurrence_map(entries, letters):
    seen = {}
    seq = iter(letters)
    out = {}
    for _, marks in entries:
        if not marks:
            continue
        mark = marks[0]
        if mark not in seen:
            seen[mark] = next(seq)
            out[mark] = seen[mark]
    return out


def rows_to_grid(s, n=5):
    if len(s) != n * n:
        return None
    return [list(s[i : i + n]) for i in range(0, len(s), n)]


def route_rows(grid):
    yield "rows_ltr", "".join("".join(r) for r in grid)
    yield "rows_rtl", "".join("".join(r[::-1]) for r in grid)
    yield "rows_snake", "".join("".join(r if i % 2 == 0 else r[::-1]) for i, r in enumerate(grid))


def route_cols(grid):
    n = len(grid)
    cols = [[grid[r][c] for r in range(n)] for c in range(n)]
    yield "cols_ttb", "".join("".join(c) for c in cols)
    yield "cols_btt", "".join("".join(c[::-1]) for c in cols)
    yield "cols_snake", "".join("".join(c if i % 2 == 0 else c[::-1]) for i, c in enumerate(cols))


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


def score_englishish(text):
    if "_" in text:
        parts = [p for p in text.split("_") if p]
    else:
        parts = [text]
    score = 0.0
    for part in parts:
        score += zipf_frequency(part.lower(), "en")
    return score / max(1, len(parts))


def main():
    text = open("cipher.txt", encoding="utf-8").read().strip()
    entries = parse_entries(text)
    marked = [(base, marks[0]) for base, marks in entries if marks]
    unique_marks = []
    for _, mark in marked:
        if mark not in unique_marks:
            unique_marks.append(mark)

    print("Base text:", "".join(base for base, _ in entries))
    print("Marked positions:", len(marked))
    print("Unique marks:", len(unique_marks))
    print()

    print("First occurrence order:")
    for i, mark in enumerate(unique_marks, 1):
        print(f"{i:02d} {mark} {mark_name(mark)}")
    print()

    first_map = first_occurrence_map(entries, "UNDERSCROLLS")
    print("First occurrence -> UNDERSCROLLS:")
    print(only_marked(entries, first_map))
    print()

    alpha_marks = sorted(unique_marks, key=core_name)
    alpha_rank = {mark: i for i, mark in enumerate(alpha_marks, 1)}
    print("Alphabetic core-name ranks:")
    for mark in alpha_marks:
        print(f"{alpha_rank[mark]:02d} {mark} {core_name(mark)}")
    print()

    shifted = render_shift(entries, alpha_rank, direction=-1)
    shifted_nospace = shifted.replace(" ", "")
    print("Rank-shift decode:", shifted)
    print("Rank-shift nospace:", shifted_nospace)
    print()

    if len(shifted_nospace) == 25:
        grid = rows_to_grid(shifted_nospace, 5)
        print("5x5 grid:")
        for row in grid:
            print(" ".join(row))
        print()
        candidates = list(route_rows(grid)) + list(route_cols(grid))
        candidates.append(("spiral", route_spiral(grid)))
        ranked = sorted(
            ((name, cand, score_englishish(cand)) for name, cand in candidates),
            key=lambda x: x[2],
            reverse=True,
        )
        print("Simple route scores:")
        for name, cand, score in ranked:
            print(f"{name:12} {score:5.2f} {cand}")
        print()

    print("Frequencies:")
    for mark, count in Counter(mark for _, mark in marked).most_common():
        print(f"{count:02d} {mark} {mark_name(mark)}")


if __name__ == "__main__":
    main()
