#!/usr/bin/exec-suid -- /usr/bin/python3
from flask import *

app = Flask(__name__)

BLACK_LIST = ['application', 'request', 'getitem', '}}', 'import', '[', '|join', 'mro', '.', 'base', 'builtins', 'attr', 'render_template', ']', '_', '{{']

with open("/flag", "r") as f:
    FLAG = f.read()
with open("/challenge/index.html") as f:
    INDEX = f.read()

def check(txt: str):
    if any(i in txt for i in BLACK_LIST):
        return False
    return True

@app.after_request
def waf(response: Response):
    if FLAG in "".join(str(response.response)):
        return Response("Bad Hacker!!!")
    return response

@app.route("/", methods=["GET", "POST"])
def index():
    if request.method == "GET":
        return render_template_string(INDEX.format(result=""))

    n = request.form['n']

    if not n:
        return render_template_string(INDEX.format(result=""))

    if not check(n):
        return render_template_string(INDEX.format(result="Bad Hacker!!!"))
    else:
        return render_template_string(INDEX.format(result="""
<div class="card mt-3" style="width: 20rem;">
    <div class="card-body">
        <h2>{{"""+n+"""}}</h2>
    </div>
</div>"""))

if __name__ == '__main__':
    app.run()
