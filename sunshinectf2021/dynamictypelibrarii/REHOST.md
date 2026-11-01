# REHOSTING

Files can be found here: [SunshineCTF 2021](https://github.com/SunshineCTF/SunshineCTF-2021-Public/tree/master/Pwn/DynamicTypeLibraRII)

## Challenge Setup

Copy the challenge files into the deployment directory. A typical pwn.college layout is `/challenge/dynamictypelibrarii/`, but keep the copied file tree identical to the archived bundle.

Artifacts:
- `.init`
- `dynamic_type_librarii` (the challenge binary)
- `libc.so.6` (glibc 2.28)
- `ld-linux-x86-64.so.2` (glibc 2.28 loader)

## Runtime Notes

- The binary is linked against a hard-coded loader path (`/home/dynamic_type_librarii/lib/ld-2.28.so`) with a matching RUNPATH. The bundled `.init` runs as root at container start and recreates that path from the shipped `ld-linux-x86-64.so.2` and `libc.so.6`, so the original binary runs unmodified against its exact glibc 2.28.
- `.init` then serves the binary over `socat` on `0.0.0.0:1337` as root, so the challenge runs with the privileges needed to read the root-only `/flag`.
- `socat` is present in the base image; the container has no network, so nothing is installed at runtime.
