from django.db import models
from django.contrib.auth.models import User

FLAIR_CHOICES = [
    ("hacker", "🕷️ Hacker (i like bugs)"),
    ("entomologist", "🪲 Entomologist (i like bugs)"),
]


class Profile(models.Model):
    user = models.OneToOneField(User, on_delete=models.CASCADE)
    flair = models.CharField(max_length=32, choices=FLAIR_CHOICES, default="hacker")

    def __str__(self):
        return f"{self.user.username} ({self.get_flair_display()})"


class Post(models.Model):
    author = models.ForeignKey(User, on_delete=models.CASCADE)
    title = models.CharField(max_length=200)
    content = models.TextField()
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ["-created_at"]

    def __str__(self):
        return self.title
