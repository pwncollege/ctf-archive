import base64


def encoded_flag(in_file, out_file, iterations):
    with open(in_file, "r") as flag_file:
        flag = flag_file.readline()

        encoded_flag = flag
        for _ in range(iterations):
            encoded_flag = base64.encodestring(encoded_flag)

        with open(out_file, "w") as onion_file:
            onion_file.write(encoded_flag)


if __name__ == '__main__':
    encoded_flag('./flag.txt', './basilisk64.txt', 13)
