#!/usr/bin/env python3
"""Re-run OCR on the STL slice renders used for Take a Slice."""

from __future__ import annotations

import os
from pathlib import Path

from PIL import Image

try:
    from rapidocr_onnxruntime import RapidOCR
except ImportError as exc:  # pragma: no cover
    raise SystemExit("rapidocr_onnxruntime is required") from exc


ROOT = Path(__file__).resolve().parent
OCR = RapidOCR()


def ocr_image(path: Path) -> list[str]:
    image = Image.open(path).convert("L")
    enlarged = image.resize((image.width * 4, image.height * 4), Image.Resampling.LANCZOS)
    temp = Path("/tmp") / path.name
    enlarged.save(temp)
    result, _ = OCR(str(temp))
    if not result:
        return []
    return [item[1] for item in result]


def main() -> None:
    targets = [
        ROOT / "proper_slices_z" / "slice_02_z4.62.png",
        ROOT / "proper_slices_z" / "slice_03_z6.93.png",
        ROOT / "proper_slices_x" / "x_02_9.64.png",
        ROOT / "proper_slices_x" / "x_03_15.13.png",
    ]
    for target in targets:
        texts = ocr_image(target)
        print(f"{target.relative_to(ROOT)} -> {' | '.join(texts) if texts else 'NO OCR'}")


if __name__ == "__main__":
    main()
