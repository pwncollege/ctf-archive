# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/1441)

## Challenge Setup
Static-artifact challenge. Copy the handout files and `flagCheck` into `/challenge/`, then make `flagCheck`
setuid-root so it can read the root-only `/flag`:

```
chmod u+s /challenge/flagCheck
```

Ships both the Intel (`rust_vm_intel`) and Apple-silicon/ARM (`rust_vm_m1`) builds for dynamic analysis; the solution is architecture-independent.

Solve the challenge to recover the flag, then run `/challenge/flagCheck` and enter it to print the
pwn.college `/flag`.
