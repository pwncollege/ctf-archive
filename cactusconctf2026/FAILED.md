# Failed Rehosts

- `legendary`: the shipped `crypto-legendary.py` imports `solver.solver`, but no `solver/` package is included in the archive, so the original entrypoint does not start on pwn.college.
- `notepadv3`: the archived exploit path works, but the bundled `backdoor()` executes `/bin/sh`, which drops privileges in a setuid deployment on pwn.college; the archive also does not include the original static flag needed for a faithful `flagCheck` fallback.
- `rpghackv2`: the archive contains only the game client and reverse-engineering artifacts; the original server needed to host the challenge is missing.
- `react2shell`: the archive only includes `DESCRIPTION.md` and no deployable application source or build output.
- `linkstack`: the archive only includes `DESCRIPTION.md` and `WRITEUP.md`, with no LinkStack instance or challenge-specific data bundle to host.
- `prosforhire`: the archive only includes `DESCRIPTION.md` and `WRITEUP.md`, with no application source or seed data.
- `embracemcp`: the archive only includes `DESCRIPTION.md` and `WRITEUP.md`, with no MCP server or backend application.
- `hiddendiscounts`: the archive only includes `DESCRIPTION.md` and `WRITEUP.md`, with no chatbot or backend application.
