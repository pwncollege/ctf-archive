#!/opt/pwn.college.python

flag = open("/flag", "r").read().strip().encode()

#msg_a=b"Hey Dave its Alice here.My flag is zh3r0{GCD_c0m3s_"
#msg_b=b"Hey Dave its Bob here.My flag is 70_R3sCue_3742986}"

msg_a=b"Hey Dave its Alice here.My flag is" + flag[0:27]
msg_b=b"Hey Dave its Bob here.My flag is" + flag[27:]
