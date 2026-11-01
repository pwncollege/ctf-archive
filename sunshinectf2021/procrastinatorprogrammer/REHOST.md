# REHOSTING

Files can be found here: [SunshineCTF 2021](https://github.com/SunshineCTF/SunshineCTF-2021-Public/tree/master/Scripting/ProcrastinatorProgrammer)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/procrastinatorprogrammer/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.init`
- `part1/server-challenge-1.py`
- `part2/server-challenge-2.py`
- `part3/server-challenge-3.py`

## Runtime Notes

- The bundled `.init` runs as root when the challenge container starts. It reads the root-only `/flag`, splits it into three slices, and writes one slice into each `partN/key` owned by a dedicated per-stage service user (`ppsvc1`/`ppsvc2`/`ppsvc3`, mode `400`) so no single stage can read another stage's slice.
- It then launches the three chained services with `socat` (one per stage), each dropped to its own service user:
  - stage 1 on `0.0.0.0:65000`
  - stage 2 on `0.0.0.0:65001`
  - stage 3 on `0.0.0.0:65002`
- Stages 2 and 3 are told the previous stage's slice via the `PREV_KEY` environment variable (set by `.init`) instead of the original hard-coded literal, so the gate check works against the runtime flag.
- `socat` and `python3` (with the standard library) are present in the base image; the container has no network, so nothing is installed at runtime.
