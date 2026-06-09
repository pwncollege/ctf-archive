import os

from flask import Flask, render_template, request, make_response
import requests


BACKEND_URL = os.environ.get("BACKEND_URL")

app = Flask(__name__, template_folder="templates")


def _fetch_admin_submissions(cookie_header: str | None):
    headers = {}
    if cookie_header:
        headers["Cookie"] = cookie_header

    resp = requests.get(
        f"{BACKEND_URL}/approval/api/submissions",
        headers=headers,
        timeout=5,
    )

    if resp.status_code == 403:
        return None, "Unauthorized – are you logged in as admin?"
    if not resp.ok:
        return None, f"Failed to load submissions (status {resp.status_code})"

    try:
        data = resp.json()
    except Exception:
        return None, "Backend returned invalid JSON for submissions"

    return data, None

@app.route("/admin/submissions.html")
def admin_submissions():
    cookie_header = request.headers.get("Cookie")
    data, error = _fetch_admin_submissions(cookie_header)
    if error is not None or data is None:
        return render_template("submissions.html", error=error, pending=[], approved=[], rejected=[], csrf_token="", backend_url=""), 200
    csrf_token = data.get("csrf_token", "")
    pending = data.get("pending", [])
    approved = data.get("approved", [])
    rejected = data.get("rejected", [])
    resp = make_response(render_template(
        "submissions.html",
        backend_url="",
        error=None,
        csrf_token=csrf_token,
        pending=pending,
        approved=approved,
        rejected=rejected,
    ))
    resp.headers['Cache-Control'] = 'public'
    return resp


@app.route("/admin/")
def admin_root():
    return admin_submissions()


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=9999, debug=False)
