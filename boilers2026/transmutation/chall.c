#include <stdio.h>
#include <stdlib.h>
#include <sys/mman.h>

#define MAIN ((char *)main)
#define CHALL ((char *)chall)
#define LEN (MAIN - CHALL)

int main(void);

void chall(void) {
    char c = getchar();
    unsigned char i = getchar();
    if (i < LEN) {
        CHALL[i] = c;
    }
}

int main(void) {
    setbuf(stdin, NULL);
    setbuf(stdout, NULL);
    setbuf(stderr, NULL);

    mprotect((char *)((long)CHALL & ~0xfff), 0x1000, PROT_READ | PROT_WRITE | PROT_EXEC);

    chall();
    return 0;
}
