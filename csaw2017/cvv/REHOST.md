# REHOSTING

Link to files: [CSAW 2017](https://github.com/osirislab/CSAW-CTF-2017-Quals/tree/master/pwn/minesweeper)

## Challenge Setup
This challenge does not require any additional files except the minesweeper binary execuatble. The binary file can be re-compiled with the python files it was originally made with which can be found in the link provided above.

## Flag Location Change
In the python file, the location fo the flag in the last function is `/opt/flag.txt`, as the challenge was originally provided as a python file so I changed the location of the `flag` to `/flag` as per pwn.college.

I converted the python file to binary execuatble by using these commands-
```
pip install pyinstaller
```
then we use the python file-
```
pyinstaller --onefile file.py
```
The binary file can be found in the same directory the above command is run in the `dist` folder.