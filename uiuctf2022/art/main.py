from time import time
from PIL import Image, ImageDraw
from random import randint, sample, seed, choice
import os, io, base64, signal

def timeout(a,b):
    print('too slow!')
    exit()
def fill_flag(n):
    init = [[False for _ in range(5)] for _ in range(5)]
    assert n <= 26 and n >= 2
    if n == 26 or n == 0:
        return [[True, True, True, True, False]] + [[True for _ in range(5)] for _ in range(4)]
    n-=2
    while n > 0:
        for i in reversed(range(5)):
            b = False
            for j in range(5):
                if init[i][j] == False:
                    init[i][j] = True
                    n -= 1
                    b = True
                    break
            if b:
                break


    return init
def fill_header(header, n):
    n -= 2 # min length 3
    sx= 47
    if n > 0:
        header.putpixel((sx+1, 2), (192, 192, 0))
        n -= 1
    if n > 0:
        header.putpixel((sx+1, 3), (192, 192, 0))
        n -= 1
    if n > 0:
        header.putpixel((sx, 2), (192, 192, 0))
        n -= 1
    if n > 0:
        header.putpixel((sx, 3), (192, 192, 0))
        n -= 1
    x = sx
    while n > 0:
        header.putpixel((x, 4), (192, 192, 0))
        x -= 1
        n -= 1
    
    return header

# returns true if generation suceeded
def gen(block, is_first):
    # force battelle logo if first image
    if is_first:
        background = os.path.join('art', 'battelle.png')
    else:
        background = os.path.join('art', choice(os.listdir("art")))

    mona = Image.open(background)
    mona.paste(header, (0, 0))
    draw = ImageDraw.Draw(mona)

    def rand_coord(x, y, bad_ranges):
        edge_inlay = 30
        # how many iterations of rand to try before giving up
        rand_loop_max = 200
        rand_loop_count = 0
        while True:
            rand_loop_count += 1
            if rand_loop_count > rand_loop_max:
                return -1, -1
            r_x = randint(edge_inlay, x - edge_inlay)
            r_y = randint(edge_inlay, y - edge_inlay)
            for [[x_lo, y_lo], [x_hi, y_hi]] in bad_ranges:
                # this is bad, we overlapped on one of the axes
                # we don't want to overlap *either* of the axes because then the
                # lines would be too close
                if r_x >= x_lo and r_x <= x_hi or r_y >= y_lo and r_y <= y_hi:
                    break
            else: # completes normally, so valid
                break
            continue
        return r_x, r_y
    x, y = mona.size # w, h
    boxes = []
    coords = []
    rotation = 180
    block_margin = 20 # space around each block where they can't overlap
    for i in range(len(word)):
        r_x, r_y = rand_coord(x, y, boxes)
        if r_x == -1 and r_y == -1:
            # we couldn't find a valid coordinate, these blocks are bad
            # try another one
            return None, False
        rotation += 90
        rotation %= 360
        coords.append((r_x, r_y))
        box = (
            (r_x - block_margin, r_y - block_margin),
            (r_x + block.size[0] + block_margin, r_y + block.size[1] + block_margin)
        )
        boxes.append(box)
    s = [56, 4]
    order = [s]

    cur = 1
    # top, right, bottom, left
    while len(coords) > 0:
        if cur == 0:
            best = min(coords, key=lambda c: c[1])
        elif cur == 1:
            best = max(coords, key=lambda c: c[0])
        elif cur == 2:
            best = max(coords, key=lambda c: c[1])
        else:
            best = min(coords, key=lambda c: c[0])
        order.append(best)
        coords.remove(best)
        cur += 1
        cur %= 4

    overlay_after = []
    # draw paths & objects
    for i in range(1, len(order)):
        start_x, start_y = order[i-1]
        end_x, end_y = order[i]
        # start_y += 11

        # if the start and end are not where they are supposed to be (according
        # to the orientation (i % 4)), then restart the generation
        # there needs to be some buffer same as the size of the block
        buffer_size = 20 # extra large just to be safe
        if i % 4 == 1 and not (start_x + buffer_size < end_x and start_y + buffer_size < end_y):
            # need to be right and bottom
            return None, False
        elif i % 4 == 2 and not (start_x > end_x + buffer_size and start_y + buffer_size < end_y):
            # need to be left and bottom
            return None, False
        elif i % 4 == 3 and not (start_x > end_x + buffer_size and start_y > end_y + buffer_size):
            # need to be left and top
            return None, False
        elif i % 4 == 0 and not (start_x + buffer_size < end_x and start_y > end_y + buffer_size):
            # need to be right and top
            return None, False

        if (start_x < end_x and start_y < end_y) or (start_y > end_y and start_x > end_x):
            r_1 = ((start_x, start_y), (end_x, start_y))
            r_2 = ((end_x, start_y), (end_x, end_y))
        else:
            r_1 = ((start_x, start_y), (start_x, end_y))
            r_2 = ((start_x, end_y), (end_x, end_y))
        overlay_after.append(((end_x, end_y), block.copy()))
        c_x, c_y = r_1[1]
        draw.rectangle(((c_x-1, c_y-1), (c_x+1, c_y+1)), fill='black')
        draw.rectangle(r_1, fill='white')
        draw.rectangle(r_2, fill='white')

    # draw the flag checkers after we draw the long white lines
    for i, (coords, block) in enumerate(overlay_after):
        # use the fact that we rotate between right, bottom, left, top when cycling
        # through the blocks
        # print(word[i], offsets[i], i)
        for row_i, row in enumerate(fill_patterns[i]):
            for col_i, fill_pixel in enumerate(row):
                if fill_pixel:
                    block.putpixel((col_i+1, row_i), (255, 255, 0))
        if i % 4 == 0:
            correct_orientation = block
        elif i % 4 == 1:
            correct_orientation = block.transpose(Image.Transpose.ROTATE_270)
        elif i % 4 == 2:
            correct_orientation = block.transpose(Image.Transpose.FLIP_TOP_BOTTOM)
        elif i % 4 == 3:
            correct_orientation = block.transpose(Image.Transpose.ROTATE_90).transpose(Image.Transpose.FLIP_TOP_BOTTOM)
        mona.paste(correct_orientation, coords)

        if i == len(overlay_after) - 1:
            end = Image.open('end.ppm')
            x,y=coords
            if i % 4 == 0:
                end_rot = end
                y += block.size[1]
                x -= 2
            elif i % 4 == 1:
                end_rot = end.transpose(Image.Transpose.ROTATE_270)
                x -= (end.size[0]-1)
                y -= 2
            elif i % 4 == 2:
                x -= 2
                y -= end.size[1]
                end_rot = end.transpose(Image.Transpose.FLIP_TOP_BOTTOM)
            elif i % 4 == 3:
                x += block.size[1]
                y -= 2
                end_rot = end.transpose(Image.Transpose.ROTATE_90).transpose(Image.Transpose.FLIP_TOP_BOTTOM)
            mona.paste(end_rot, (x,y))

    # mona.save("out.png")
    # print(f'done in {loop_count} iterations')
    return mona, True

FLAG = 'uiuctf{m0ndr14n_b3st_pr0gr4mm3r_ngl}'
ITERS = 10
if __name__ == '__main__':
    signal.signal(signal.SIGALRM, timeout)
    loop_max = 1000
    words = list(map(lambda x: x.strip(), open('flaglist.txt', 'r').read().strip().split('\n')))
    header_original = Image.open("piet_header.ppm")
    block = Image.open("check_block.ppm")

    for iter in range(ITERS):
        loop_count = 0
        s = randint(0, 10000000)
        seed(s)



        word = sample(words, 1)[0]
        word_rev = word[::-1]
        #assert 's' not in word # breaks program (offset 1:/)
        offsets = [26 - (ord(c) % 26) for c in word_rev]
        # print(word_rev, offsets)
        fill_patterns = [fill_flag(o) for o in offsets]
        header = header_original.copy()
        header = fill_header(header, len(word_rev))
        while True:
            loop_count += 1
            if loop_count > loop_max:
                print('ERROR: Loop count exceeded!')
                print(f'Could not generate an image for flag "{word}"')
                break

            pic, success = gen(block, iter == 0)
            if success:
                break
        
        print('Torrented Picture Data (Base64):')
        img_byte_arr = io.BytesIO()
        pic.save(img_byte_arr, format='PNG')
        img_base64 = base64.b64encode(img_byte_arr.getvalue()).decode('utf-8')
        print(img_base64)
        signal.alarm(15)
        res = input(f'Enter flag #{iter+1}/{ITERS} (15s):')
        signal.alarm(0)
        if res.strip() == word:
            print('Correct!')
            continue
        else:
            print('Incorrect!')
            exit()
    
    print("i'll just use google images next time :D")
    print(FLAG)
