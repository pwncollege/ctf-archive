Note: The encryption keys are different on the remote.
However, your flash.img has been patched to work with the key shipped.

You can assume that the flags and keys in bootloader.img have been redacted after build, and all function offsets are identical on the remote.
This is only relevant in the pwn section of the chall however :)

NOTE: the kernel being loaded is plain linux, without any patches or external module loaded.
Don't waste your time trying to reverse engineer it :)