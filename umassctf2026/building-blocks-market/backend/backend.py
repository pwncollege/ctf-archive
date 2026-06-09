import os

from flask import Flask, redirect, url_for, render_template
from flask_login import LoginManager, current_user

from models import db, User
from routes import auth_bp, marketplace_bp, submissions_bp


def load_flag():
    flag_path = "/flag"
    try:
        with open(flag_path, "r", encoding="utf-8") as flag_file:
            return flag_file.read().strip()
    except OSError:
        return os.getenv("FLAG")


class Config:
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    SESSION_COOKIE_SECURE = True
    SESSION_COOKIE_HTTPONLY = True
    SESSION_COOKIE_SAMESITE = "Lax"


class DevelopmentConfig(Config):
    DEBUG = True
    SQLALCHEMY_ECHO = True
    SESSION_COOKIE_SECURE = False


class ProductionConfig(Config):
    DEBUG = False
    SQLALCHEMY_ECHO = False


class TestingConfig(Config):
    TESTING = True
    SQLALCHEMY_DATABASE_URI = "sqlite:///:memory:"


def get_config():
    env = os.getenv("FLASK_ENV", "development").lower()

    if env == "production":
        return ProductionConfig
    elif env == "testing":
        return TestingConfig
    else:
        return DevelopmentConfig


def load_environment_variables():
    return {
        "BACKEND_HOST": os.getenv("BACKEND_HOST", "0.0.0.0"),
        "BACKEND_PORT": int(os.getenv("BACKEND_PORT", 5001)),
        "DB_PATH": os.getenv("DB_PATH", "sqlite:///buildingblocks.db"),
        "SECRET_KEY": os.getenv("SECRET_KEY"),
        "DEBUG_MODE": os.getenv("DEBUG_MODE", "True").lower() in ("true", "1", "yes"),
        "FLASK_ENV": os.getenv("FLASK_ENV", "development"),
        "FLAG": load_flag(),
    }


def create_app(config=None):
    app = Flask(__name__, static_folder="static", template_folder="templates")

    if config is None:
        config = get_config()

    app.config.from_object(config)

    env_vars = load_environment_variables()
    app.config["SQLALCHEMY_DATABASE_URI"] = env_vars["DB_PATH"]
    app.config["SECRET_KEY"] = env_vars["SECRET_KEY"]
    app.config["FLAG"] = env_vars.get("FLAG")
    app.config["SEND_FILE_MAX_AGE_DEFAULT"] = 3600

    db.init_app(app)

    login_manager = LoginManager()
    login_manager.init_app(app)
    login_manager.login_view = "auth.login"
    login_manager.login_message = "Please log in to access this page."


    @login_manager.user_loader
    def load_user(user_id):
        return User.query.get(int(user_id))

    app.register_blueprint(auth_bp)
    app.register_blueprint(marketplace_bp)
    app.register_blueprint(submissions_bp)


    @app.route("/")
    def index():
        if current_user.is_authenticated:
            return redirect(url_for("marketplace.marketplace"))
        return render_template("index.html")

    with app.app_context():
        db.create_all()

        admin_username = os.environ.get("ADMIN_USERNAME")
        admin_password = os.environ.get("ADMIN_PASSWORD")

        existing_admin = User.query.filter_by(username=admin_username).first()
        if not existing_admin:
            admin_user = User(username=admin_username)
            admin_user.set_password(admin_password)
            try:
                db.session.add(admin_user)
                db.session.commit()
            except Exception:
                db.session.rollback()

    return app


def main():
    env_vars = load_environment_variables()

    app = create_app()

    app.run(
        host=env_vars["BACKEND_HOST"],
        port=env_vars["BACKEND_PORT"],
        debug=env_vars["DEBUG_MODE"],
    )


if __name__ == "__main__":
    main()
