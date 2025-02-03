from flask import Flask, request, session, g, redirect, url_for, abort, render_template, flash
from urllib.parse import unquote
from time import time
from hashlib import sha256

DEBUG = False
SECRET_KEY = '[REDACTED]'
PASSWORD = '[REDACTED]'
SALT = 'pqzr3rCj2GX5VFsfbwCzHMD9'

HASH_LENGTH = 64
TIME_LENGTH = 8

WINDOW = 5

app = Flask(__name__)
app.config.from_object(__name__)
app.config.from_envvar('FLASKR_SETTINGS', silent=True)

# Helper functions, because who needs encodings these days
def str2bytes(b):
    return bytes(map(ord, b))

def bytes2str(s):
    return "".join(map(ord, s))

@app.route('/', methods=['GET', 'POST'])
def main():
    if request.method == 'POST':
        data = unquote(request.form['data'])

        # data is in the form hash + time
        # hash = sha256(sha256(salt + password) + time)
        # hash and time are in hex

        req_time = int(data[-TIME_LENGTH:], 16)
        cur_time = int(time())

        if not (cur_time - WINDOW < req_time <= cur_time + WINDOW):
            return render_template('index.html', errmsg='Request timed out.')

        time_data = data[HASH_LENGTH:]
        hash_data = data[:HASH_LENGTH]

        pass_hash = sha256(str2bytes(SALT + PASSWORD)).hexdigest()

        full_hash = sha256(str2bytes(pass_hash) + str2bytes(time_data)).hexdigest()

        if hash_data != full_hash:
            return render_template('index.html', errmsg='Incorrect password hash.')
        else:
            return render_template('flag.html')

    return render_template('index.html')

if __name__ == '__main__':
    app.run()
