# REHOSTING

Files can be found here: [HKCERTCTF 2022](https://github.com/hkcert-ctf/CTF-Challenges/tree/main/CTF-2022/03-base64-encryption)

## Challenge Setup
This challenge requires the `article.txt` file to be present in the same directory. The `chall` script will encrypt the article (which contains the flag) using base64 substitution cipher.

## Flag
The flag `hkcert22{statistical_cryptanalysis_works_on_longer_texts}` is embedded within the article text to provide sufficient data for frequency analysis. This makes the challenge solvable through proper cryptanalytic techniques rather than just guessing from known prefix/suffix.