# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/1441)

## Challenge Setup
Static-artifact challenge. Copy the handout files and `flagCheck` into `/challenge/`, then make `flagCheck`
setuid-root so it can read the root-only `/flag`:

```
chmod u+s /challenge/flagCheck
```

`DownUnder.gba` is a Game Boy Advance ROM; load it in an emulator such as mGBA. The flag is drawn off-screen, viewable via the emulator's map viewer.

Solve the challenge to recover the flag, then run `/challenge/flagCheck` and enter it to print the
pwn.college `/flag`.
