# MoM5m4g1C Challenge Solution

## Overview
This repository contains the solution for the MoM5m4g1C CTF challenge, demonstrating a buffer overflow vulnerability exploitation using the `gets` function.

## Compilation
Compile the source code with disabled stack protection and non-position independent code:
```bash
gcc MoM5m4g1C.c -o MoM5m4g1C -fno-stack-protector -no-pie
```
**Note**: The `gets` function is used, which is unsafe and deprecated due to its vulnerability to buffer overflow attacks.

## Running the Executable
To run the compiled executable and trigger the buffer overflow, use the following command:
```bash
./MoM5m4g1C
```
When prompted, input a long string to overflow the buffer and alter the adjacent variable to execute a command that reveals a secret message.

## Example Input
```bash
Fill the water bottle kid!11111111111111111111111111111111111111111111111111122222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222233333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333344444444444444444444444444444444444444444444444444444444444444444
```

This input results in a buffer overflow that manipulates the control flow of the program, ultimately leading to the unintended execution of `system("cat gift.txt")` which reveals the flag.

## Output
Upon successful execution, the program outputs the flag:
```plaintext
wtfCTF{N1c3!n0w_U_c4N_34t_uR_Ch0c0L4t3}
```
However, note that executing with excessive input leads to a segmentation fault after revealing the flag.

## Security Note
This challenge demonstrates a critical security flaw commonly found in older C programs that use unsafe functions like `gets()`. Always use safer alternatives like `fgets()` in production code to avoid buffer overflow vulnerabilities.

## Conclusion
By understanding and exploiting the buffer overflow vulnerability in this challenge, participants can gain insights into basic security practices and the importance of using safe functions in C programming.
