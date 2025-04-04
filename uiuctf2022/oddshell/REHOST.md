# REHOSTING

Link to files: [UIUICTF 2022](https://github.com/sigpwny/UIUCTF-2022-Public/tree/main/pwn/odd_shell)

## Challenge Setup
This challenge just has 1 elf called `chal`, since this challenge gives shell access when you do it correctly and `pwn.college` drops the privs so I made a `patched_chal` which is essentially a wrapper that makes the privs needed to ```cat /flag``` persist when `chal` is run.