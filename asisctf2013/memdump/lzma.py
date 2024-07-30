import lzma

# Decompress a .xz file
with lzma.open('mem.dump.xz') as f:
    content = f.read()

# Write the decompressed content to a new file
with open('mem.dump', 'wb') as f:
    f.write(content)

