#!/bin/sh
qemu-system-arm \
	-m 1024 \
	-M virt,highmem=off \
 	-kernel zImage \
    	-initrd rootfs.cpio \
	-nic user,hostfwd=tcp:0.0.0.0:8080-:80 \
 	-nographic 
