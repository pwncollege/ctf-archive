CC := gcc
CFLAGS := -fno-stack-protector -no-pie

all: chal

chal: chal.c
	$(CC) $(CFLAGS) $< -o $@

clean:
	rm -f chal
