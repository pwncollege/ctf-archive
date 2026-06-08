def xor_cipher(data, key):
    hex_output = []
    for i in range(len(data)):
        char_xor = ord(data[i]) ^ ord(key[i % len(key)])
        hex_output.append(f"{char_xor:02x}")
    return "".join(hex_output)

HARDCODED_KEY = "cybergame"
plain_flag = "SK-CERT{...}" # empty flag for demonstration

print(xor_cipher(plain_flag, HARDCODED_KEY))
