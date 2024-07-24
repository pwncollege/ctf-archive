import hashlib

correct_flag = "DUCTF{h0pe_y0u_enjoy3d_th3_fr33_cat_p1c_:)}"
correct_flag_hash = hashlib.sha256(correct_flag.encode()).hexdigest()
print(correct_flag_hash)

