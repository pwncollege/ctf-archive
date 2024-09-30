import json
import os
import secrets
from datetime import timedelta
from decimal import Decimal

from flask_login import LoginManager
from flask_sqlalchemy import SQLAlchemy
from flask_wtf.csrf import CSRFProtect
from sqlalchemy import event
from sqlalchemy.orm import DeclarativeBase

from flask import Flask, abort, redirect, request

domain = None
# domain = "127.0.0.1"
# domain = "walframsigma.challs.m0lecon.it"


app = Flask(__name__)

app.config.update(
    APPLICATION_ROOT="/",
    MAX_CONTENT_LENGTH=16 * 1000 * 1000,
    MAX_COOKIE_SIZE=4093,
    PREFERRED_URL_SCHEME="https",
    SECRET_KEY=secrets.token_hex(256),
    SEND_FILE_MAX_AGE_DEFAULT=timedelta(hours=12),
    SERVER_NAME=domain,
    USE_X_SENDFILE=False,  # disabled: does not work with gunicorn
)


# init extended json encoder
class JSONEncoder(json.JSONEncoder):
    # JSON ENCODER EXTENSION
    def default(self, o):
        if type(o) is Decimal:
            return float(o)
        return json.JSONEncoder.default(self, o)


# init flask-sqlalchemy
app.config.update(
    # SQLALCHEMY_DATABASE_URI="sqlite:///:memory:?cache=shared",
    SQLALCHEMY_DATABASE_URI="sqlite:////tmp/db.sqlite3",
    # SQLALCHEMY_BINDS={},
    # SQLALCHEMY_ECHO=True,
    # SQLALCHEMY_RECORD_QUERIES=True,
    SQLALCHEMY_TRACK_MODIFICATIONS=False,
    # SQLALCHEMY_ENGINE_OPTIONS={},
)


class Base(DeclarativeBase):
    pass


db = SQLAlchemy(model_class=Base)
db.init_app(app)


def enable_foreign_keys(dbapi_connection, connection_record):
    cursor = dbapi_connection.cursor()
    cursor.execute("PRAGMA foreign_keys = ON;")
    cursor.close()


@app.before_request
def force_https():
    if not app.debug or request.is_secure or request.headers.get("X-Forwarded-Proto", "http") == "https":
        app.config["SESSION_COOKIE_SECURE"] = True
        app.config["SESSION_COOKIE_HTTPONLY"] = True
        if request.url.startswith("http://") and not request.headers.get("X-Forwarded-Proto", "http") == "https":
            return redirect(
                request.url.replace("http://", "https://", 1),
                code=301,
            )


@app.before_request
def make_nonce():
    if get_nonce() == "":
        request.csp_nonce = secrets.token_urlsafe(16)


def get_nonce():
    return getattr(request, "csp_nonce", "")


@app.after_request
def security_headers(response):
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Download-Options"] = "noopen"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["X-XSS-Protection"] = "1; mode=block"
    response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
    response.headers["Strict-Transport-Security"] = f"max-age={31556926}; includeSubDomains; preload"
    nonce = get_nonce()
    response.headers["Content-Security-Policy"] = " ".join(map(lambda i: f"{i[0]} {' '.join(i[1])};", {
        "default-src": ["'none'", ],
        "base-uri": ["'none'", ],
        "connect-src": ["'self'", ],
        "font-src": ["'self'", "data:",],
        "form-action": ["'self'", ],
        "frame-ancestors": ["'none'", ],
        "frame-src": ["'none'", ],
        "img-src": ["'self'", "data:",],
        "object-src": ["'none'", ],
        "script-src": [f"'nonce-{nonce}'", "'strict-dynamic'", "'unsafe-eval'",],
        "style-src": [f"'nonce-{nonce}'",],
        "upgrade-insecure-requests": ["", ],
        "worker-src": ["'none'", ],
    }.items()))
    response.headers["Cross-Origin-Embedder-Policy"] = "require-corp"
    response.headers["Cross-Origin-Opener-Policy"] = "same-origin"
    response.headers["Cross-Origin-Resource-Policy"] = "same-site"
    response.headers["Permissions-Policy"] = ", ".join(map(lambda i: f"{i[0]}=({' '.join(i[1])})", {
        "attribution-reporting": [],
        "browsing-topics": [],
        "camera": [],
        "geolocation": [],
        "interest-cohort": [],
        "join-ad-interest-group": [],
        "microphone": [],
        "run-ad-auction": [],
    }.items()))
    del response.headers["Server"]
    del response.headers["X-Powered-By"]
    # response.headers["X-DNS-Prefetch-Control"] = "off"
    return response


app.jinja_env.globals["csp_nonce"] = get_nonce

# init session
app.config.update(
    PERMANENT_SESSION_LIFETIME=timedelta(minutes=30),
    SESSION_COOKIE_DOMAIN=domain,
    SESSION_COOKIE_HTTPONLY=True,
    SESSION_COOKIE_NAME="session",
    SESSION_COOKIE_PATH="/",
    SESSION_COOKIE_SAMESITE="Strict",
    SESSION_COOKIE_SECURE=True,
    SESSION_PERMANENT=True,
    SESSION_REFRESH_EACH_REQUEST=True,
    SESSION_SQLALCHEMY_TABLE="sessions",
    SESSION_SQLALCHEMY=db,
    SESSION_TYPE="sqlalchemy",
    SESSION_USE_SIGNER=True,
)

# init flask-wtf
app.config.update(
    # RECAPTCHA_DATA_ATTRS=None,
    # RECAPTCHA_DIV_CLASS="g-recaptcha",
    # RECAPTCHA_HTML=None,
    # RECAPTCHA_PARAMETERS=None,
    # RECAPTCHA_PRIVATE_KEY=None,
    # RECAPTCHA_PUBLIC_KEY=None,
    # RECAPTCHA_SCRIPT="https://www.google.com/recaptcha/api.js",
    # RECAPTCHA_VERIFY_SERVER="https://www.google.com/recaptcha/api/siteverify",
    WTF_CSRF_CHECK_DEFAULT=False,
    WTF_CSRF_ENABLED=True,
    WTF_CSRF_FIELD_NAME="csrf_token",
    WTF_CSRF_HEADERS=["X-CSRFToken", "X-CSRF-Token"],
    WTF_CSRF_METHODS={"POST", "PUT", "PATCH", "DELETE"},
    WTF_CSRF_SECRET_KEY=secrets.token_hex(256),
    WTF_CSRF_SSL_STRICT=True,
    WTF_CSRF_TIME_LIMIT=5 * 60,
    WTF_I18N_ENABLED=True,
)
csrfprotect = CSRFProtect(app)

# init flask-login
app.config.update(
    REMEMBER_COOKIE_DOMAIN=domain,
    REMEMBER_COOKIE_DURATION=timedelta(minutes=30),
    REMEMBER_COOKIE_HTTPONLY=True,
    REMEMBER_COOKIE_NAME="remember_token",
    REMEMBER_COOKIE_PATH="/",
    REMEMBER_COOKIE_REFRESH_EACH_REQUEST=True,
    REMEMBER_COOKIE_SAMESITE="Strict",
    REMEMBER_COOKIE_SECURE=True,
    SESSION_PROTECTION="strong",
    USE_SESSION_FOR_NEXT=True,
)
login_manager = LoginManager(app)


def register_blueprints(app):
    from auth import auth
    from website import website
    app.register_blueprint(auth)
    app.register_blueprint(website)


register_blueprints(app)

with app.app_context():
    event.listen(db.engine, "connect", enable_foreign_keys)
    # create all missing db tables
    db.create_all()
    from models import User

    # default admin user
    User.register(
        id_=0,
        username=os.environ["ADMIN_USER"],
        password=os.environ["ADMIN_PASSWORD"],
    )
    # default user
    User.register(
        id_=1,
        username="user",
        password="password",
    )


@app.route("/teapot")
async def teapot():
    abort(418)

if __name__ == "__main__":
    app.run()
