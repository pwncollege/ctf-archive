from argon2 import PasswordHasher
from argon2.exceptions import VerifyMismatchError
from flask_login import UserMixin
from sqlalchemy.orm.attributes import flag_modified

from server import db

hasher = PasswordHasher()


class User(UserMixin, db.Model):
    __tablename__ = "user"
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.Unicode, unique=True, nullable=False)
    password = db.Column(db.String, nullable=False)

    def get_id(self):
        return str(self.id)

    @classmethod
    def exists(cls, username):
        return db.session.execute(db.select(cls).filter(cls.username == username)).scalars().one_or_none() is not None

    @classmethod
    def register(cls, username, password, id_=None):
        user = db.session.execute(db.select(cls).filter(
            cls.username == username)).scalars().one_or_none()
        if user is None:
            user = cls(username=username)
            if id_ is not None:
                user.id = id_
            db.session.add(user)
        user.password = hasher.hash(password)
        flag_modified(user, "password")
        db.session.commit()
        return user

    def verify(self, password):
        try:
            hasher.verify(self.password, password)
        except VerifyMismatchError:
            return False
        if hasher.check_needs_rehash(self.password):
            self.password = hasher.hash(password)
            flag_modified(self, "password")
            db.session.commit()
        return True
