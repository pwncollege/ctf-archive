import subprocess
git_url = input('Git url to clone > ')
subprocess.run(["git", "clone", git_url], capture_output=False)
print('Done cloning!')
