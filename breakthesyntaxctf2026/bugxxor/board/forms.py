from django import forms
from django.contrib.auth.forms import UserCreationForm
from django.contrib.auth.models import User
from .models import Post, FLAIR_CHOICES


class RegisterForm(UserCreationForm):
    flair = forms.ChoiceField(choices=FLAIR_CHOICES)

    class Meta:
        model = User
        fields = ["username", "password1", "password2"]


class PostForm(forms.ModelForm):
    class Meta:
        model = Post
        fields = ["title", "content"]
        widgets = {
            "content": forms.Textarea(
                attrs={
                    "placeholder": "You can use templates! Reference below",
                    "rows": 8,
                }
            ),
        }
