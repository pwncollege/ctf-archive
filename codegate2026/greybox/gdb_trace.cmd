set pagination off
set confirm off
set print elements 0
break *0x4012c7
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H00 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x4012db
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H01 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x40130f
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H02 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x40133f
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H03 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x401377
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H04 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x4013af
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H05 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x4013e4
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H06 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x401419
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H07 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x40144d
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H08 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x401485
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H09 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x4014bd
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H10 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x4014f1
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H11 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x401525
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H12 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x401559
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H13 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x40158d
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H14 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x4015c1
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H15 pc=%u op=%u rdi=%p next=%p flag=%u\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68), *(unsigned int *)($m + 0x1c)
continue
end
break *0x4015fe
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H16 pc=%u op=%u rdi=%p next=%p\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68)
continue
end
break *0x40161f
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H17 pc=%u op=%u rdi=%p next=%p flag=%u\n", *(unsigned int *)($m + 0x20), *(unsigned char *)(*(void **)($m + 0x28) + *(unsigned int *)($m + 0x20)), $rdi, *(void **)($rdi + 0x68), *(unsigned int *)($m + 0x1c)
continue
end
break *0x401652
commands
silent
set $m = *(void **)($rdi + 0xc8)
printf "H18 pc=%u rdi=%p flag=%u\n", *(unsigned int *)($m + 0x20), $rdi, *(unsigned int *)($m + 0x1c)
continue
end
run < ../../input_A.bin
