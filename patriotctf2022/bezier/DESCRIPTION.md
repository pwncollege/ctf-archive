We are doing some DFIR on an employee's laptop after he got hacked. We've gotten everything except the method the hacker used to keep persistence on the machine. Luckily, we had backed up the employee's registry a few days before the attack. Given that registry file and one from after the attack, can you figure out the method of persistence used?

The flag is the MITRE ID of the persistence mechanism. For example, the MITRE ID of "Scheduled Task/Job: Cron" is T1053.003, so its respective flag would be PCTF{T1053.003}

`Use flagCheck to input the flag you get from the challenge to get the actual flag`

---
**Author:** Daniel Getter (NihilistPenguin)
