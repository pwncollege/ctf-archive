#!/usr/bin/env python2

import os
import sys
import argparse
import pyvirtualdisplay.smartdisplay
import easyprocess
import time
import shutil
import struct
import socket
import PIL.Image
import PIL.ImageChops
import PIL.ImageSequence
import base64
import StringIO
import signal
import tarfile
import psutil

from xdo import Xdo

DEBUG = False

ACTUAL_HARD_DISK_DIR = "/home/www/hd/"
PRISTINE_HARD_DISK = "/opt/www/NS33_2GB.dd.tar.gz"

HOME_DIR = "/opt/www/"

PREVIOUS_BINARY = "/opt/www/previous"

TIMEOUT_SECONDS = 600

image_info = {'login_screen':
              {
                  'filename': 'login_screen_good.png',
                  'start': (280, 260),
                  'size': (590, 350)
              },
              'login_desktop':
              {
                  'filename': 'login_desktop_good.png',
                  'start': (200, 0),
                  'size': (830, 820)
               },
              'www':
              {
                  'filename': 'www_ready_good.png',
                  'start': (170, 35),
                  'size': (550, 350)
              },
              'document_inspector':
              {
                  'filename': 'document_inspector_ready_good.png',
                  'start': (570, 100),
                  'size': (470, 380),
              }
}

# Make sure our output is unbuffered
unbuffered = os.fdopen(sys.stdout.fileno(), 'w', 0)
sys.stdout = unbuffered

def test_images():
    import PIL.Image
    for name, value in image_info.iteritems():
        img = PIL.Image.open(value['filename'])
        crop = img.crop((value['start'][0], value['start'][1], value['start'][0]+value['size'][0], value['start'][1]+value['size'][1]))
        crop.show()

screenshot_num = 0
def send_screen_shot(img):
    img = img.crop((0, 0, 1120, 830))
    #img = img.convert('RGB')
    output = StringIO.StringIO()
    img.save(output, format="PNG")
    contents = output.getvalue()
    global screenshot_num
    screenshot_file = "/tmp/screenshot-"+str(screenshot_num)+".png"
    with open(screenshot_file, "w") as f:
        f.write(contents)
    print "DEBUG stored in ", screenshot_file
    
    
    screenshot_num += 1
    
def get_flag():
    try:
        with open('/flag', 'r') as f:
            return f.read()
    except IOError:
        return "flag{fake-flag}"
        

def start_ns(url):

    # First, create the copy of the hard drive
    if DEBUG:
        print "creating the HD"

    # kill any previous binaries
    for proc in psutil.process_iter():
        if proc.name() == "previous":
            proc.kill()
        
    tar = tarfile.open(PRISTINE_HARD_DISK, 'r:gz')
    tar.extractall(ACTUAL_HARD_DISK_DIR)

    if DEBUG:
        print "done creating the HD"

    flag = get_flag()
    with pyvirtualdisplay.smartdisplay.SmartDisplay(visible=0, size=(1300, 900)) as v_display:
        if DEBUG:
            print v_display
            print v_display.display

        with easyprocess.EasyProcess(PREVIOUS_BINARY) as previous:

            time.sleep(.50)
            xdo = Xdo()

            done = False
            while not done:
                wins = xdo.search_windows('Previous')
                # check if alive
                if not previous.is_alive():
                    print "Error, couldn't boot emulator"
                    return
                if wins:
                    done = True
                    window = wins[0]
                else:
                    time.sleep(.50)

            # Get the relative X and Y for the window
            loc = xdo.get_window_location(window)

            print "Booting up"

            # wait until we get to the login screen
            wait_until_login_screen(xdo, loc, window, v_display)

            print "Finally booted"

            current_image = v_display.waitgrab()
            send_screen_shot(current_image)

            log_into_ns(xdo, window)

            print "Waiting to log in"

            # wait for the screen to load
            wait_until_login_desktop(xdo, loc, window, v_display)

            print "Logged in"

            current_image = v_display.waitgrab()
            send_screen_shot(current_image)

            load_in_flag(xdo, window, flag)

            # Send the data
            send_url_to_ns(url)    

            send_copy_command(xdo, window)

            time.sleep(.50)

            current_image = v_display.waitgrab()
            send_screen_shot(current_image)

            print "Starting the old dog"

            send_start_www_command(xdo, window)

            # Wait until WWW is ready
            wait_until_www_ready(xdo, loc, window, v_display)

            print "That old dog is ready"

            current_image = v_display.waitgrab()
            send_screen_shot(current_image)
            

            # Close out the terminal
            # Command-q is the keyboard shortcut to quit
            xdo.send_keysequence_window(window, "Super_L+q")
            time.sleep(.50)
            select_open_document(xdo, loc, window)

            # Wait until Document Inspector is open
            print "Sending your input"
            wait_until_document_inspector_open(xdo, loc, window, v_display)

            current_image = v_display.waitgrab()
            send_screen_shot(current_image)
            
            move_to_address_field(xdo, loc, v_display)
            time.sleep(.50)
            xdo.click_window(window, 1)

            current_image = v_display.waitgrab()
            send_screen_shot(current_image)

            # Paste that shit!
            # Command-v is the keyboard shortcut
            xdo.send_keysequence_window(window, "Super_L+v")
            time.sleep(.50)

            current_image = v_display.waitgrab()
            send_screen_shot(current_image)            

            print "That old dog can still visit URLs!"

            # if DEBUG:
            #     input("Wait for debugging")

            # Open it!
            xdo.send_keysequence_window(window, "Return")

            print "tbl would be proud"

            # Wait for 5 seconds, taking screenshots
            for i in xrange(5):
                time.sleep(1)
                current_image = v_display.waitgrab()
                send_screen_shot(current_image)

        # Crazy hack b/c something is sending an error message:
        # "XIO:  fatal IO error 0 (Success) on X server ":1001""
        # Which is freaking crazy, doesn't make any 
        devnull = open(os.devnull, 'w')
        os.dup2(devnull.fileno(), 2)


    
def log_into_ns(xdo, window):
    xdo.enter_text_window(window, "me", 100000)
    time.sleep(.50)
    xdo.send_keysequence_window(window, "Tab")
    time.sleep(.50)
    xdo.enter_text_window(window, "password", 100000)
    time.sleep(.50)
    xdo.send_keysequence_window(window, "Return")

def save_screenshot(v_display, name):
    before = time.time()
    img = v_display.waitgrab()
    after = time.time()
    print "screenshot", name, "took", after - before
    img.save(name + ".png")
    print "screenshot saved at", name + ".png"


def pixel_diff(first, second):
    assert first.size == second.size

    res = PIL.ImageChops.difference(first, second)
    count = 0
    for rgb in res.getdata():
        if rgb != (0, 0, 0):
            count += 1
    return count

def crop_to(img, target):
    return img.crop((target['start'][0], target['start'][1], target['start'][0] + target['size'][0], target['start'][1] + target['size'][0]))

def wait_until_screen_matches(screen, v_display, percent_threshold=1):
    delay = 0.5
    done = False    
    target = image_info[screen]
    good_image = PIL.Image.open(target['filename'])
    good_crop = crop_to(good_image, target)

    i = 0
    while not done:
        time.sleep(delay)
        current_image = v_display.waitgrab()
        current_crop = crop_to(current_image, target)
        num_pixel_diff = pixel_diff(good_crop, current_crop)

        width, height = current_crop.size

        total_pixels = width * height * 1.0

        diff = (num_pixel_diff / total_pixels) * 100.0
        
        i += 1
        if i % 10 == 0:
            send_screen_shot(current_image)
        if DEBUG:
            print "current", screen, "num pixel diff", num_pixel_diff, "percent pixel diff", diff

        if diff < percent_threshold:
            done = True
        
def wait_until_login_screen(xdo, loc, window, v_display):
    wait_until_screen_matches('login_screen', v_display)
    # give it some time, just in case
    time.sleep(1)

def wait_until_login_desktop(xdo, loc, window, v_display):
    wait_until_screen_matches('login_desktop', v_display)
    # give it some time, just in case
    time.sleep(1)
    

def wait_until_www_ready(xdo, loc, window, v_display):
    wait_until_screen_matches('www', v_display)
    # give it some time, just in case
    time.sleep(1)
    

def wait_until_document_inspector_open(xdo, loc, window, v_display):
    wait_until_screen_matches('document_inspector', v_display)
    # give it some time, just in case
    time.sleep(1)
    


def select_open_document(xdo, loc, window):
    # Note: the keyboard shortcut here is Command-O
    # I disabled the previous mode so NO KEYBOARD REQUIRED!
    xdo.send_keysequence_window(window, "Super_L+Shift+o")

def move_to_address_field(xdo, loc, v_display):
    xdo.move_mouse(loc.x, loc.y)
    time.sleep(.50)
    target_x = 700
    target_y = 380

    current_x = 0
    current_y = 0

    grad_x = 5
    grad_y = 5

    i = 0
    done = False
    while not done:

        i += 1
        if i % 30 == 0:
            current_image = v_display.waitgrab()
            send_screen_shot(current_image)
        
        if current_x + grad_x > target_x:
            to_move_x = target_x - current_x
        else:
            to_move_x = grad_x
            
        if current_y + grad_y > target_y:
            to_move_y = target_y - current_y
        else:
            to_move_y = grad_y

        if DEBUG:
            print "at", current_x, current_y, "moving", to_move_x, to_move_y, "target", target_x, target_y

        xdo.move_mouse_relative(to_move_x, to_move_y)

        time.sleep(0.5)

        current_x += to_move_x
        current_y += to_move_y

        if current_x == target_x and current_y == target_y:
            done = True

def move_to_terminal(xdo, loc):
    xdo.move_mouse(loc.x, loc.y)
    time.sleep(.50)
    xdo.move_mouse_relative(200, 200)
    time.sleep(.50)
    xdo.move_mouse_relative(200, 140)
    
    # xdo.move_mouse(loc.x+400, loc.y+340)


def send_copy_command(xdo, window):
    xdo.enter_text_window(window, "cat /input ", 100000)
    time.sleep(.50)
    xdo.send_keysequence_window(window, "Shift+bar")
    time.sleep(.50)
    xdo.enter_text_window(window, " copy", 100000)
    time.sleep(.50)
    xdo.send_keysequence_window(window, "Return")

def load_in_flag(xdo, window, flag):
    xdo.enter_text_window(window, "echo ", 100000)
    time.sleep(.50)
    xdo.enter_text_window(window, flag, 100000)
    time.sleep(.50)
    xdo.send_keysequence_window(window, "Shift+bar")
    time.sleep(.50)
    xdo.enter_text_window(window, "tee /me/flag", 100000)
    time.sleep(.50)
    xdo.send_keysequence_window(window, "Return")

    time.sleep(.50)
    xdo.enter_text_window(window, "clear", 100000)
    time.sleep(.50)
    xdo.send_keysequence_window(window, "Return")
    

def send_start_www_command(xdo, window):
    xdo.enter_text_window(window, "open ./", 100000)
    time.sleep(.50)    
    xdo.send_keysequence_window(window, "Shift+W")
    time.sleep(.50)    
    xdo.enter_text_window(window, "orld", 100000)
    time.sleep(.50)    
    xdo.send_keysequence_window(window, "Shift+W")
    time.sleep(.50)    
    xdo.enter_text_window(window, "ide", 100000)
    time.sleep(.50)
    xdo.send_keysequence_window(window, "Shift+W")
    time.sleep(.50)    
    xdo.enter_text_window(window, "eb.app", 100000)
    time.sleep(.50)
    xdo.send_keysequence_window(window, "Return")
    

def send_url_to_ns(url):
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

    s.connect(('127.0.0.1', 42424))

    to_send = struct.pack('>I', len(url)) + url

    if DEBUG:
        print repr(to_send)

    s.sendall(to_send)

    result = s.recv(1024)

    if DEBUG:
        print result

def handler(signum, frame):
    raise Exception("Took too long, perhaps we got stuck in a loop. Oops.")

def main():
    signal.signal(signal.SIGALRM, handler)
    signal.alarm(TIMEOUT_SECONDS)
    os.chdir(HOME_DIR)
    print "Welcome to the pre-alpha web aka 520d462abb92809b4fa1eaaafabbaee4"
    print
    print "What URL would you like this old dog to fetch?"
    result = sys.stdin.readline()

    result = result.rstrip('\n')
    
    if not result.startswith("http://"):
        print "Error, I only know http"
        return

    if len(result) > 2048:
        print "Error, URL too big"
        return
    
    start_ns(result)
    print "after"

if __name__ == '__main__':
    main()

