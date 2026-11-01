from flask import Flask, session, redirect, url_for, request, render_template, render_template_string
import requests
from datetime import datetime

app = Flask(__name__, static_url_path='',static_folder='static/')
app.secret_key = 'cb84fba2-5a0e-4ae4-85c2-3ae338f40474'
with open("flag.txt", "r") as f:
    app.config["FLAG"] = f.read().strip()
app.config["DEBUG"] = False

@app.route('/hello')
@app.route('/hello/<user>')
def hello_world(user=None):
    user = user or ''
    return render_template('hello.html', user=user)

@app.route('/')
def index():
    return app.send_static_file("index.html")

@app.route("/render", methods=["POST"])
def render_template_endpoint():
    data = request.form
    template = request.form["template"]
    if ".py" in template or "app" in template:
        template = "index.html"
    template = requests.get("http://127.0.0.1:5000/" + template).text
    return render_template_string(template)




@app.route("/render", methods=["OPTIONS"])
def render_options():
    return "testing"


@app.route('/templates/matches.html')
def matches():
    return app.send_static_file("templates/matches.html")

@app.route('/templates/teams.html')
def teams():
    return app.send_static_file("templates/teams.html")

@app.route('/templates/admin.html')
def admin():
    return app.send_static_file("templates/admin.html")


if __name__ == '__main__':
    app.run(debug=False, host='0.0.0.0')
