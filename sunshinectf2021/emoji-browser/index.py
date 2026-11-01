# I wonder if anyone will notice that I'm using a challenge from last year as boilerplate.
from urllib.parse import unquote

# Related to web server
from flask import Flask, request, Response, render_template, send_file
app = Flask(__name__)


# Web server endpoints
@app.route("/")
def home():
    # Render login page template
    return render_template("index.html")

# serves stylesheet
@app.route('/index.css')
def serve_css():
    return send_file(
            "index.css",
            download_name="index.css",
            mimetype="text/css"
    )

# discriminate against robots
@app.route('/robots.txt')
def serve_roboto():
    return send_file(
            "robots.txt",
            download_name="robots.txt"
    )

# serves all emoji
@app.route('/emoji/<string:filename>')
def serve_rest(filename):
    # we love mid-ctf patching! :)
    filename = filename.replace("\0", "[0x00_NULL_SENTINAL]").replace("%00", "[0x00_NULL_SENTINAL]").replace("%0", "[0x00_NULL_SENTINAL]")
    print(filename)
    filename = unquote(filename)
    try:
        print(filename)
        filename_tmp = filename + ".svg"
        filename = filename_tmp[:filename.index("[0x00_NULL_SENTINAL]")]
    except:
        filename = filename + ".svg"
    print(f"File path: ./assets/emoji/{filename}")
    try:
        return send_file(
            f"assets/emoji/{filename}",
            download_name=f"./assets/emoji/{filename}",
            as_attachment=True
        )
    except:
        try:
            filename = "/" + filename[:filename.rfind("/")+1]
            print(filename)
            return send_file(
                f"assets/emoji{filename}/index.svg",
                download_name=f"./assets/emoji{filename}/index.svg",
                as_attachment=True
            )
        except:
            return send_file(
                f"assets/emoji/index.svg",
                download_name="404.svg",
                as_attachment=True
            ), 404

@app.route('/emoji/')
def serve_rest_troll():
    return send_file(
        f"assets/emoji/index.svg",
        download_name="./assets/emoji/index.svg",
        as_attachment=True
    )

@app.errorhandler(404)
def not_found(e):
    return render_template("404.html")

def create_app():
    return app


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8000)
