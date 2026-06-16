import hashlib
import binascii
from Crypto.Cipher import AES
from Crypto.Util.Padding import unpad

def get_flag(key_hex):
    ciphertext_hex = "7b324bfbfe9cf9ccdce792276ae92032e9acb2b3342be9e954eb6b8f37d0babf9ea0dfd6c7462b2f3bfd591654940309"
    key = binascii.unhexlify(key_hex)
    raw_data = binascii.unhexlify(ciphertext_hex)
    iv = raw_data[:16]
    encrypted_payload = raw_data[16:]
    cipher = AES.new(key, AES.MODE_CBC, iv)
    decrypted = unpad(cipher.decrypt(encrypted_payload), AES.block_size)
    return decrypted.decode('utf-8')

def gen_key(matrix):
    matrix_str = str(matrix).encode('utf-8')
    return hashlib.sha256(matrix_str).hexdigest()

if __name__ == "__main__":
    print("--- 5x5 Grid Challenge ---")
    print("Enter all 25 values separated by commas (e.g., 1,2,3...25):")
    print("There may be multiple solutions, only one will produce correct key. Values in each cell are 1-25")
    
    user_input = input("> ").strip()
    
    try:
        flat_list = [int(x.strip()) for x in user_input.split(',')]
        
        if len(flat_list) != 25:
            print(f"Error: Expected 25 values, got {len(flat_list)}.")
            exit()
            
        grid = [flat_list[i:i+5] for i in range(0, 25, 5)]

    except ValueError:
        print("Error: Please ensure all inputs are integers separated by commas.")
        exit()

    constraints = [
        ([(0,0), (0,1), (0,2), (0,3), (0,4)], 15),
        ([(1,0), (1,1), (1,2), (1,3), (1,4)], 40),
        ([(2,0), (2,1), (2,2), (2,3), (2,4)], 65),
        ([(3,0), (3,1), (3,2), (3,3), (3,4)], 90),
        ([(4,0), (4,1), (4,2), (4,3), (4,4)], 115),
        ([(0,0), (1,1), (2,2), (3,3), (4,4)], 65),
        ([(0,0), (4,0)], 22)
    ]

    passed_count = sum(1 for cells, target in constraints if sum(grid[r][c] for r, c in cells) == target)

    if passed_count == len(constraints):
        key = gen_key(grid)
        print(key)
        print(f"[+] Constraints passed!")
        print(f"[+] Flag: {get_flag(key)}")
    else:
        print("\n[X] Constraints failed. Grid does not match the required logic.")
