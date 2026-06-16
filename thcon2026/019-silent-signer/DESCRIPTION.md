# Silent Signer (#19)

S.N.A.F.U. agents recovered `sst-fwsign` from a compromised workstation inside the SST Dynamics factory. It appears to be part of the firmware signing pipeline that M4terM4xima uses to flash compromised firmware onto the robots. Our field analysts tried attaching a debugger: each time, the validation fails. Reverse the binary and recover the signing token it accepts.

N.B.: The binary requires root privileges but is harmless. If you're not comfortable running it as root, use a VM.

`Use flagCheck to input the flag you get from the challenge to get the actual flag`

---
**Author:** THCon 2026
