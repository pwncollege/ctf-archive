# REHOSTING

Files can be found here: [ECTF 2014](https://github.com/pwncollege/ctf-archive/blob/main/ectf2014/python/python.tar.gz)

## Challenge Setup
The file when extracted has a dependecy of a libc file which is `libncurses.so.5`. You can check the dependency by using this command:
```
ldd Python
```

This dependency can be resolved if you install the libc file using this command:

```
sudo apt-get install libncurses5 libncurses5:i386
```

This will ask for permission which you use `y` to agree. You can also use a bash script which I have used like this:

```
#!/bin/bash

y | sudo apt-get install libncurses5 libncurses5:i386
```
This should automatically install without needing any approval.