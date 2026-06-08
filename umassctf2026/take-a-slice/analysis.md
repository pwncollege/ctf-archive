# Take a Slice Analysis

## Verified file facts

- `artifacts/take-a-slice_extracted/cake` is a binary STL.
- Header is 80 bytes of zeros.
- Triangle count at offset `0x50` is `39210`.
- File size matches STL layout exactly: `80 + 4 + 39210 * 50 = 1960584`.
- Geometry bounds are sane after STL parsing; prior float-stream interpretations were invalid.

## Slice/OCR findings

- Text is visible in the proper STL z-slices, especially:
  - `artifacts/proper_slices_z/slice_02_z4.62.png`
  - `artifacts/proper_slices_z/slice_03_z6.93.png`
- RapidOCR on the full z-slices returned:
  - `slice_02_z4.62.png` -> `MASSISL1F`
  - `slice_03_z6.93.png` -> `&D1C3}`
- A few x-slices also produced text fragments:
  - `proper_slices_x/x_02_9.64.png` -> `221`
  - `proper_slices_x/x_03_15.13.png` -> `C3`

## Submission attempts

All of the following live CTFd submissions returned `incorrect`:

- `UMASS{MASS_IS_SL1CE_AND_D1C3}`
- `UMASS{MASS_IS_SLICE_AND_DICE}`
- `UMASS{MASS_IS_SL1C3_AND_D1C3}`
- `UMASS{MASS_IS_SL1CE_AND_D1C3}`
- `UMASS{MASS_IS_SL1C3_AND_D1CE}`
- `UMASS{MASS_IS_SLICE_AND_D1CE}`
- `UMASS{MASS_IS_LIFE_AND_DICE}`
- `UMASS{MASSISL1F&D1C3}`
- `UMASS{MASSISL1F_D1C3}`
- `UMASS{MASSISL1FD1C3}`
- `UMASS{MASSISL1F_1C3}`
- `UMASS{MASS_IS_L1F_AND_D1C3}`
- `UMASS{MASS_IS_L1F_AND_D1CE}`
- `UMASS{MASS_IS_L1F_&_D1C3}`
- `UMASS{MASS_IS_L1F_&_D1CE}`
- `UMASS{MASS_IS_L1F_AND_DICE}`
- `UMASS{MASS_IS_L1F_&_DICE}`

## Current status

- The accepted flag is `UMASS{MASSISLIFE_&_DICE}`.
- Confirmation came from the CTFd team-solve state for challenge `8` and the fact that subsequent attempts on this session returned `already_solved`.
