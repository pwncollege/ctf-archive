import base64

def encoded_flag(in_file, out_file, iterations):
    with open(in_file, "r") as flag_file:
        flag = flag_file.readline()

        # Convert flag to bytes
        encoded_flag = flag.encode('utf-8')
        for _ in range(iterations):
            encoded_flag = base64.encodebytes(encoded_flag)

        # Convert bytes back to string before writing to the file
        with open(out_file, "w") as onion_file:
            onion_file.write(encoded_flag.decode('utf-8'))

if __name__ == '__main__':
    encoded_flag('/flag', '/challenge/basilisk64.txt', 13)

