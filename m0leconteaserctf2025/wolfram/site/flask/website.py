from flask_login import login_required
from flask_wtf import FlaskForm
from wtforms import SubmitField, TextAreaField

from auth import admin_required
from flask import Blueprint, redirect, render_template, url_for

website = Blueprint("website", __name__)


class ExpressionForm(FlaskForm):
    expression = TextAreaField(
        id="expression",
        default="\n".join(filter(lambda x: len(x) > 0, map(lambda x: x.strip(), """
            fx(t) = 5 * ( 16 * sin(t) ^ 3 )
            fy(t) = 5 * ( 13 * cos(t) - 5 * cos(2 * t) - 2 * cos(3 * t) - cos(4 * t) )
            ft(x) = x / 100 * 2 * pi
            to_xy(x, [["t", ft, "x"], ["x", fx, "t"], ["y", fy, "t"]])
        """.split("\n")))),
        name="expression",
    )
    draw = SubmitField(
        label="📈 Draw",
        id="draw",
        name="draw",
    )


@website.route("/")
@website.route("/canvas")
@login_required
def index():
    form = ExpressionForm()
    return render_template("canvas.html", form=form)


@website.route("/favicon.ico")
def favicon():
    return redirect(url_for("static", filename="img/favicon.svg"), code=301)


@website.route("/flag")
@login_required
@admin_required
def flag():
    with open("/run/secrets/flag", "r") as f:
        return f.read()
