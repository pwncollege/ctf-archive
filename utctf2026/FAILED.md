# Failed Rehosts

- `hourofjoy`: binary requires `GLIBC_2.34`, which is not available in the pwn.college environment.
- `obliviouserror`: archive only includes the OT client snippet and solver; the original remote service implementation is missing.
- `rudeguard`: binary requires `GLIBC_2.34`, which is not available in the pwn.college environment.
- `timetopretend`: archive only includes the leaked PCAP and solver; the original web service implementation is missing.
- `watson`: Checkpoint B depends on external `ithqsu.zip` / `Calc.exe` artifacts referenced in the triage, but the archived KAPE bundle only contains references to them and not the files themselves.
