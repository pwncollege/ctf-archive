def xor(a, b):
    return hex(int(a, 16)^int(b, 16))[2:]

Key1 = '5dcec311ab1a88ff66b69ef46d4aba1aee814fe00a4342055c146533'
Key1Key3 = '9a13ea39f27a12000e083a860f1bd26e4a126e68965cc48bee3fa11b'
Key2Key3Key5 = '557ce6335808f3b812ce31c7230ddea9fb32bbaeaf8f0d4a540b4f05'
Key1Key4Key5 = '7b33428eb14e4b54f2f4a3acaeab1c2733e4ab6bebc68436177128eb'
Key3Key4 = '996e59a867c171397fc8342b5f9a61d90bda51403ff6326303cb865a'
FlagKey1Key2Key3Key4Key5 = '306d34c5b6dda0f53c7a0f5a2ce4596cfea5ecb676169dd7d5931139'

#for key 3
Key3 = xor(Key1, Key1Key3)
Key4 = xor(Key3, Key3Key4)
Key1Key4 = xor(Key1, Key4)
Key1Key2Key3Key5 = xor(Key1, Key2Key3Key5)
Key1Key2Key3Key4Key5 = xor(Key4, Key1Key2Key3Key5)

Flag = hex(int(FlagKey1Key2Key3Key4Key5, 16)^int(Key1Key2Key3Key4Key5, 16))[2:]
Final_flag = bytes.fromhex(Flag).decode('ASCII')
print(Final_flag)

