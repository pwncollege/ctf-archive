import random
import string
import os
import shutil


directory="/instances"

unique_id = ''.join(random.choice(string.ascii_uppercase + string.digits) for _ in range(32))
os.mkdir(f"{directory}/{unique_id}")

shutil.copy("/src/flash.img", f"{directory}/{unique_id}/flash.img")
shutil.copy("/src/bootloader.bin", f"{directory}/{unique_id}/bootloader.bin")

os.chdir(f"{directory}/{unique_id}")

os.system("timeout 300 qemu-system-x86_64 -drive format=raw,file=bootloader.bin -drive format=raw,file=flash.img -nographic -m 1G -monitor /dev/null")

os.chdir(f"{directory}")

shutil.rmtree(f"{directory}/{unique_id}")