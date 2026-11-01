#!/usr/local/bin/python3
import math
import os
import sys
import multiprocessing
from math import *

sys.stdout.reconfigure(line_buffering=True)
previous_challenge_port = os.getenv('CHALLENGE_1_PORT', 65000)
previous_challenge_domain = os.getenv('CHALLENGE_1_DOMAIN', 'procrastinate.2021.sunshinectf.org')
next_challenge_port = os.getenv('CHALLENGE_3_PORT', 65002)
next_challenge_domain = os.getenv('CHALLENGE_3_DOMAIN', 'procrastinate-sequel.2021.sunshinectf.org')

# copied from http://lybniz2.sourceforge.net/safeeval.html to show it's unsafe.
safe_list = [
    'math', 'acos', 'asin', 'atan', 'atan2', 'ceil', 'cos', 'cosh', 'degrees',
    'e', 'exp', 'fabs', 'floor', 'fmod', 'frexp', 'hypot', 'ldexp', 'log',
    'log10', 'modf', 'pi', 'pow', 'radians', 'sin', 'sinh', 'sqrt', 'tan',
    'tanh'
]
#use the list to filter the local namespace
safe_dict = dict([(k, locals().get(k, None)) for k in safe_list])
#add any needed builtins back in.
safe_dict['abs'] = abs

def test_client(stdinput):
    print("Halt in the name of the law!\n")
    print("What was the ./key found in the previous challenge?\n")
    key = stdinput.readline()
    _expected = os.getenv("PREV_KEY", "")
    if (not _expected) or (_expected not in key):
        print("Nada. Sorry, but this isn't a guessing challenge!")
        print(f"Back to `{previous_challenge_domain} {previous_challenge_port}` you go!")
        return 1

    print("Give me an equation please!\n")
    client_input = stdinput.readline()
    # remove newline
    client_input = client_input[:-1]
    # the or "" is to not confuse the solver with random None values.
    print(eval(client_input, {}, safe_dict) or "")
    return 0

# launches a client session for a given level.
def client_thread():
    try:
        message = 'Welcome to the ProcrastinatorProgrammer backend.\n'
        message += 'Please give me an equation! Any equation! I need to be fed some data to do some processing!'
        message += 'Due to technical difficulties with the last challenge, I\'ve upped my ante! Now I know it\'s secure!'
        message += 'I\'m super secure, and can use most python math! I just use `eval(client_input, \{\}, safe_math_functions)` on your data and then whamo, python does all the work!'
        message += 'Whatever you do, don\'t look at my ./key!'
        message += "\n"
        print(message)

        # thanks to jfs https://stackoverflow.com/a/8981813
        new_stdin = os.fdopen(os.dup(sys.stdin.fileno()))
        failed = False
        try:
            # Eh not really needed but it'll distract them longer if they think they can bring the challenge down somehow.
            client_process = multiprocessing.Process(target=test_client, args=[new_stdin])
            
            client_process.start()
            client_process.join(30)

            if client_process.is_alive():
                client_process.terminate()
                print("Too slow! You must not be from Florida!")
                raise RuntimeWarning()

            # thanks to ATOzTOA (https://stackoverflow.com/a/14924210) for helping with the multiprocessing code
            if client_process.exitcode != 0:
                print("You must not be from Florida!")
                raise RuntimeWarning()
        finally:
            new_stdin.close()
            
        print("\nIf you completed part 2 of the challenge...")
        print("\nYou need sequels. MORE SEQUELS!! 🔥🏰🔥")
        print(f"\n{next_challenge_domain} {next_challenge_port} holds your next clue.")
        return 0

    except RuntimeWarning:
        print("Come visit Florida again some time!")
        return 0
    except KeyboardInterrupt:
        print("Killing server", file=sys.stderr)
        print("Server killed by Sunshine CTF admins, technical difficulties currently with this challenge, please come back soon. This is not part of the challenge... sorry. :(")
        return 0

client_thread()