from functools import wraps
from urllib.parse import urljoin, urlparse

from flask_login import current_user, login_required, login_user, logout_user
from flask_wtf import FlaskForm
from flask_wtf.csrf import generate_csrf
from wtforms import (BooleanField, PasswordField, StringField, SubmitField,
                     validators)

from flask import (Blueprint, Response, abort, jsonify, redirect,
                   render_template, request, session, url_for)
from models import User
from server import db, login_manager

auth = Blueprint("auth", __name__)


def is_safe_url(target):  # avoid open redirects
    ref_url = urlparse(str(request.url))
    test_url = urlparse(urljoin(str(request.url), target))
    # ensure the scheme is http(s) and the netloc is the same as the host
    return test_url.scheme in ("http", "https") and ref_url.netloc == test_url.netloc and test_url != ref_url


def get_redirect_target(default="/"):
    for target in [
        session.pop("next", None),
        request.headers.get("Referer", None),
        request.args.get("next", None),
        default,
    ]:
        if not target:
            continue
        if is_safe_url(target):
            return target


def redirect_back(default="/", **kwargs):
    if "code" not in kwargs:
        kwargs["code"] = 303
    return redirect(get_redirect_target(default=default), **kwargs)


@login_manager.user_loader
def load_user(user_id):
    return db.session.execute(db.select(User).filter(
        User.id == user_id)).scalars().one_or_none()


def admin_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if current_user.is_authenticated and current_user.id == 0:
            return f(*args, **kwargs)
        return abort(401)
    return decorated_function


@auth.route("/csrf", methods=["GET"])
def csrf():
    return Response(generate_csrf(), status=200, mimetype="text/plain")


def str_form_errors(form_errors):
    str_errors = []
    for k, errors in form_errors.items():
        if k is None:
            k = "Error"
        for error in errors:
            str_errors.append(f"{k}: {error}")
    return ", ".join(str_errors)


class LoginForm(FlaskForm):
    username = StringField(
        label="Username",
        validators=[
            validators.InputRequired(),
        ],
        id="username",
        default="user",
        name="username",
    )
    password = PasswordField(
        label="Password",
        validators=[
            validators.InputRequired(),
        ],
        id="password",
        default="password",
        name="password",
    )
    remember_me = BooleanField(
        label="Remember me",
        id="remember_me",
        default=False,
        name="remember_me",
    )
    submit = SubmitField(
        label="Login",
        id="submit",
        name="submit",
    )

    _fail_message = "Wrong credentials"

    def validate(self, extra_validators=None):
        if not super().validate(extra_validators=extra_validators):
            return False
        self._user = db.session.execute(db.select(User).filter(
            User.username == self.username.data)).scalars().one_or_none()
        if self._user is None:
            self.form_errors.append(self._fail_message)
            return False
        if not self._user.verify(self.password.data):
            self.form_errors.append(self._fail_message)
            return False
        return True


@auth.route("/login", methods=["GET", "POST"])
def login():
    if current_user.is_authenticated:
        return redirect_back()
    form = LoginForm()
    if form.validate_on_submit():
        login_user(form._user, remember=form.remember_me.data)
        return redirect_back()
    return render_template("login.html", form=form)


login_manager.login_view = "auth.login"


def username_does_not_exist_validator(form, field):
    if User.exists(username=field.data):
        raise validators.ValidationError("username already exists")
    return True


class RegisterForm(FlaskForm):
    username = StringField(
        "Username",
        validators=[
            validators.DataRequired(),
            validators.Length(min=3),
            username_does_not_exist_validator,
        ]
    )
    password = PasswordField(
        "Password",
        validators=[
            validators.DataRequired(),
            validators.Length(min=8),
        ]
    )
    confirm = PasswordField(
        "Repeat password",
        validators=[
            validators.DataRequired(),
            validators.EqualTo("password", message="passwords do not match"),
        ]
    )
    submit = SubmitField(
        label="Submit",
        id="submit",
        name="submit",
    )


@auth.route("/register", methods=["GET", "POST"])
def register():
    form = RegisterForm()
    if form.validate_on_submit():
        User.register(
            username=form.username.data,
            password=form.password.data,
        )
        return redirect(url_for("auth.login"), code=303)
    return render_template("register.html", form=form)


@auth.route("/logout", methods=["GET", "POST"])
@login_required
def logout():
    logout_user()
    return redirect("/", code=303)


@auth.route("/whoami", methods=["GET"])
def whoami():
    if current_user.is_authenticated:
        return Response(f"{current_user.id}: {current_user.username}", status=200, mimetype="text/plain")
    return Response(f"{None}: {None}", status=401, mimetype="text/plain")


# @login_manager.unauthorized_handler
# def unauthorized():
#     return abort(401)
