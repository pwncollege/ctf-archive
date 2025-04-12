#!/usr/bin/exec-suid -- /usr/bin/python3

flag = open('/flag', 'rb').read().strip()
secret_msg = b' Nice job! I hope you enjoyed the challenge. Here\'s your flag: ' + flag
