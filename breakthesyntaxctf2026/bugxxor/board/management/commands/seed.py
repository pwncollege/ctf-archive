import os
from django.core.management.base import BaseCommand
from django.contrib.auth.models import User
from board.models import Profile, Post


class Command(BaseCommand):
    help = "Seed admin user and sample posts"

    def handle(self, *args, **options):
        admin_pw = os.environ.get("ADMIN_PASSWORD", "admin")
        admin, created = User.objects.get_or_create(
            username="admin",
            defaults={"is_superuser": True, "is_staff": True},
        )
        if created:
            admin.set_password(admin_pw)
            admin.save()
            Profile.objects.create(user=admin, flair="hacker")
            self.stdout.write("Created admin user")

        if not Post.objects.exists():
            Post.objects.bulk_create(
                [
                    Post(
                        author=admin,
                        title="Welcome to bugxxor",
                        content=(
                            "Welcome to {{ platform }}.\n\n"
                            "We currently track {{ total_bugs }} vulnerabilities "
                            "and {{ total_insects }} insect species.\n\n"
                            "Use our get_bug_info helper to pull data into your posts.\n"
                            "Plz don't hack."
                        ),
                    ),
                ]
            )
            self.stdout.write("Created sample posts")
