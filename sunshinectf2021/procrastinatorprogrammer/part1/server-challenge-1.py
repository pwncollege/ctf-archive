#!/usr/local/bin/python3
import os
import sys
import multiprocessing
from math import *

sys.stdout.reconfigure(line_buffering=True)
next_challenge_port = os.getenv('CHALLENGE_2_PORT', 65001)
next_challenge_domain = os.getenv('CHALLENGE_2_DOMAIN', 'procrastinate-castle.2021.sunshinectf.org')

def test_client(stdinput):
    print("Give me an equation please!\n")
    client_input = stdinput.readline()
    # remove newline
    client_input = client_input[:-1]
    # the or "" is to not confuse the solver with random None values.
    print(eval(client_input) or "")
    return 0

# launches a client session for a given level.
def client_thread():
    try:
        message = 'Welcome to the ProcrastinatorProgrammer backend.\n'
        message += 'Please give me an equation! Any equation! I need to be fed some data to do some processing!'
        message += 'I\'m super secure, and can use all python! I just use `eval()` on your data and then whamo, python does all the work!'
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
            
        print("\nIf you completed part 1 of the challenge...")
        print("\nYour princess is in another castle! 🔥🏰🔥")
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