from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth import login, logout
from django.contrib.auth.decorators import login_required
from django.contrib.auth.forms import AuthenticationForm
from django.template import Template, Context
from .models import Post, Profile
from .forms import RegisterForm, PostForm


class BugInfo:
    def __init__(self, severity, bug_type, vendor):
        self.severity = severity
        self.type = bug_type
        self.vendor = vendor


class InsectInfo:
    def __init__(self, legs, order, species):
        self.legs = legs
        self.order = order
        self.species = species


class Bugs:
    def __init__(self):
        self.cve_2024_1234 = BugInfo("critical", "RCE", "Apache")
        self.cve_2024_5678 = BugInfo("high", "SQLi", "WordPress")
        self.cve_2024_9999 = BugInfo("medium", "XSS", "React")


class Insects:
    def __init__(self):
        self.beetle = InsectInfo(6, "Coleoptera", "350,000+")
        self.moth = InsectInfo(6, "Lepidoptera", "160,000+")
        self.ant = InsectInfo(6, "Hymenoptera", "22,000+")


BENCH_CONTEXT = {
    "bugs": Bugs(),
    "insects": Insects(),
    "platform": "bugxxor",
    "version": "0.1.0-alpha",
    "total_bugs": 1337,
    "total_insects": 67,
}


def home(request):
    posts = Post.objects.select_related("author", "author__profile")[:20]
    return render(request, "board/home.html", {"posts": posts})


def post_detail(request, pk):
    post = get_object_or_404(Post, pk=pk)
    rendered = Template(post.content).render(Context(BENCH_CONTEXT))
    return render(
        request, "board/post_detail.html", {"post": post, "rendered": rendered}
    )


@login_required
def post_create(request):
    form = PostForm(request.POST or None)
    if form.is_valid():
        post = form.save(commit=False)
        post.author = request.user
        post.save()
        return redirect("post_detail", pk=post.pk)
    return render(request, "board/post_create.html", {"form": form})


def register_view(request):
    form = RegisterForm(request.POST or None)
    if form.is_valid():
        user = form.save()
        Profile.objects.create(user=user, flair=form.cleaned_data["flair"])
        login(request, user)
        return redirect("home")
    return render(request, "board/register.html", {"form": form})


def login_view(request):
    form = AuthenticationForm(request, data=request.POST or None)
    if form.is_valid():
        login(request, form.get_user())
        return redirect("home")
    return render(request, "board/login.html", {"form": form})


def logout_view(request):
    logout(request)
    return redirect("home")


@login_required
def flag_view(request):
    if not request.user.is_superuser:
        return render(request, "board/flag.html", {"error": "Nice try. Admin only."})
    try:
        flag = open("/flag").read().strip()
    except FileNotFoundError:
        flag = "Flag file not found."
    return render(request, "board/flag.html", {"flag": flag})
