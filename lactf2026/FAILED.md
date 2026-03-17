# Failed Rehosts

- `threesatproblem`: binary requires `GLIBC_2.34`, which is not available in the pwn.college environment.
- `srabasm`: binary requires `GLIBC_2.34`, which is not available in the pwn.college environment.
- `tictactoe`: binary requires `GLIBC_2.34`, which is not available in the pwn.college environment.
- `misdirection`: the service never became ready on the default pwn.college workspace; `/status` stayed false for more than 18 minutes while startup key generation ran.
- `spreadingsecrets`: Sage-based root finding did not finish in practical time on pwn.college, and one attempt hit PARI stack limits while factoring the degree-19683 polynomial.
- `singletrust`: Node 16 on pwn.college rejects 1-byte GCM tags, so the archived truncated-tag exploit path does not work.
