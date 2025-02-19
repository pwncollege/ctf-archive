#!/opt/pwn.college/python

from PIL import Image
import random

flag = open("/flag").readlines()[0].strip()

blue = Image.new(mode="RGB", size=(512, 512), color=(64, 77, 205))
pixels = blue.load()


for index, c in enumerate(flag):
    width = blue.size[0] // len(flag)
    height = blue.size[1] // len(flag)
    for _ in range(ord(c)):
        p = random.randint(0, width-1)+width*index,random.randint(0, height-1)+height*index
        color_index = random.randint(0, 2)
        color = list(pixels[p])
        color[color_index] += 1
        pixels[p] = tuple(color)

blue.save('/challenge/blue.png')
