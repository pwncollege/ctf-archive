#!/opt/pwn.college/python
flag = open('/flag', 'rb').read().strip()
secret_msg = b' Nice job! I hope you enjoyed the challenge. Here\'s your flag: ' + flag
