import json
from flask import Flask, request, jsonify, render_template, session, redirect
from sqlite3 import connect
import os
import secrets

import requests
import re


app = Flask(__name__)

app.secret_key = os.urandom(16)

METHOD_BLACKLIS = [
    'setwebhook',
    'deletewebhook',
    'getwebhookinfo',
    'getme',
    'logout',
    'close'
]
FLAG_CHAT_ID =  os.getenv('FLAG_CHAT_ID', 'REDACTED')
BOT_TOKEN = os.getenv('BOT_TOKEN', 'REDACTED')
SECRET_TOKEN_TELEGRAM = "REDACTED"


# SETUP DB
def get_db():
    conn = connect('db.sqlite')
    return conn, conn.cursor()


def create_table():
    conn, cursor = get_db()
    cursor.execute('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, password INTEGER)')
    cursor.execute('CREATE TABLE IF NOT EXISTS chats (id INTEGER PRIMARY KEY, chat_id INTEGER, user_id INTEGER)')
    cursor.execute('CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY, chat_id INTEGER, update_json TEXT)')

    # add admin if not exists 
    res = cursor.execute(f'SELECT * FROM users WHERE password = "{os.getenv("ADMIN_PASSWORD", "REDACTED")}" LIMIT 1')
    if not res.fetchone():
        cursor.execute(f'INSERT INTO users (password) VALUES ("{os.getenv("ADMIN_PASSWORD", "REDACTED")}")')
        cursor.execute(f'INSERT INTO chats (chat_id, user_id) VALUES ({FLAG_CHAT_ID}, 1)')
    conn.commit()

@app.before_request
def before_request():
    if theme := request.cookies.get('theme', '/static/themes/1.css'):
        request.theme = theme

@app.after_request
def after_request(response):
    if not request.cookies.get('theme'):
        # CHANGE TEAM BY UI IS NOT IMPLEMENTED
        response.set_cookie('theme', '/static/themes/1.css', secure=True, httponly=True)      
    return response  


@app.route('/')
def home():
    return redirect('/index')

# view
@app.route('/index')
def index():
    _, cursor = get_db()

    password = request.args.get('password')
    if password:
        res = cursor.execute('SELECT * FROM users WHERE password = ? LIMIT 1', (password,))
        if user := res.fetchone():
            session['logged'] = True
            session['user_id'] = user[0]
            return redirect('/')
    
    if not session.get('logged'):
        return render_template('login.html', theme=request.theme)
        
    chats = cursor.execute(f'SELECT chat_id FROM chats WHERE user_id = ?', (session['user_id'], )).fetchall()
    
    return render_template('index.html', chats=chats, theme=request.theme)  

@app.route('/register', methods=['POST'])
def register():
    if session.get('logged'):
        return redirect('/')

    password = secrets.token_hex(16)
    conn, cursor = get_db()

    cursor.execute(f'INSERT INTO users (password) VALUES ("{password}")')
    session['logged'] = True
    session['user_id'] = cursor.lastrowid

    conn.commit()
    return f'Your password is {password}. <a href="/">Go to index</a>'



@app.route('/chat', methods=['POST'])
def chat():

    if not session.get('logged'):
        return redirect('/')
    
    chat_id = request.form.get('chat_id')
    if not chat_id or not chat_id.isdigit() or int(chat_id) == FLAG_CHAT_ID:
        return redirect('/')
    
    # insert if not exists
    conn, cursor = get_db()

    res = cursor.execute('SELECT * FROM chats WHERE chat_id = ? AND user_id = ?', (chat_id, session['user_id'])).fetchone()
    if not res and session['user_id'] != 1: # bot is used for testing, don't save chat_id
        cursor.execute('INSERT INTO chats (chat_id, user_id) VALUES (?, ?)', (int(chat_id), session['user_id']))
    conn.commit()

    return render_template('chat.html', chat_id=chat_id, theme=request.theme)


@app.route("/telegram/webhook", methods=['POST'])
def webhook():

    # check telegram header
    if request.headers.get('X-Telegram-Bot-Api-Secret-Token') != SECRET_TOKEN_TELEGRAM:
        return jsonify({'error': 'Invalid secret'}), 400
    
    data = request.json
    if 'message' not in data:
        return jsonify({'error': 'Ignore'}), 200
    
    update = json.dumps(data)

    conn, cursor = get_db()
    cursor.execute('INSERT INTO messages (chat_id, update_json) VALUES (?, ?)', (data['message']['chat']['id'], update))
    conn.commit()

    return jsonify({'ok': True}), 200
    

@app.route('/telegram/<method>', methods=['POST'])
def telegram(method):

    if not session.get('logged'):
        return redirect('/')
    
    if not method.isalpha():
        return jsonify({'error': 'Invalid method'})

    if method.lower().strip() in METHOD_BLACKLIS:
        return jsonify({'error': 'Method not allowed'}), 405
    
    url = f"https://api.telegram.org/bot{BOT_TOKEN}/{method}"


    params = request.form.to_dict()

    chat_id = params.get('chat_id')
    if not chat_id or not chat_id.isdigit() or int(chat_id) == FLAG_CHAT_ID:
        return jsonify({'error': 'Invalid chat_id'})
    

    _, cursor = get_db()
    res = cursor.execute('SELECT * FROM chats WHERE chat_id = ? AND user_id = ?', (chat_id, session['user_id'])).fetchone()
    if not res and session['user_id'] != 1:
        return jsonify({'error': 'Chat not found'}), 404
    

    
    if method == 'getUpdates':
        # take from cache
        updates = cursor.execute('SELECT update_json FROM messages WHERE chat_id = ? ORDER BY id DESC LIMIT 5', (chat_id,)).fetchall()
        return jsonify({'ok': True, 'result': [json.loads(update[0]) for update in updates]})
    
    response = requests.get(url, params=params)

    if response.status_code != 200:
        return jsonify({'error': 'Telegram API error'}), 500
    try:
        return jsonify(response.json())
    except Exception as e:
        print("ERROR", e)
        return jsonify({"error": "Generic"})


if __name__ == '__main__':
    create_table()
    app.run(debug=True, host="0.0.0.0")



