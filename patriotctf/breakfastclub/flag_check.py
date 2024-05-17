import hashlib
import os

def get_actual_flag():
    """Read the actual flag using the `cat /flag.txt` command."""
    try:
        with os.popen("cat /challenge/flag.txt") as f:
            flag = f.read().strip()
        return flag
    except Exception as e:
        return f"Failed to retrieve the flag: {e}"

# Expected SHA1 value of the correct input flag
expected_flag_sha = "65fad9ebbf59f48b11ae47f785b55ddc46f85db4"  # SHA1 of "PCTF{H@5H_8R0WNS}"

# Prompt the user to input the flag
user_input_flag = input("Enter the flag: ")

# Compute the SHA1 of the user input
input_flag_sha = hashlib.sha1(user_input_flag.encode()).hexdigest()

# Validate the SHA1 value
if input_flag_sha == expected_flag_sha:
    # If valid, retrieve and print the actual flag
    actual_flag = get_actual_flag()
    print("Actual Flag:", actual_flag)
else:
    print("Invalid flag")

