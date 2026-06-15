#!/bin/sh

set -eu
cd /challenge
cat > /challenge/readflagbinary_printf.c <<'EOF'
#include <stdio.h>
int main(void) {
    FILE *f = fopen("/flag", "r");
    if (!f) return 1;
    char buf[512] = {0};
    if (!fgets(buf, sizeof(buf), f)) return 1;
    fclose(f);
    printf("-printf %s", buf);
    return 0;
}
EOF
gcc -O2 /challenge/readflagbinary_printf.c -o /challenge/readflagbinary_printf
install -o root -g root -m 4755 /challenge/readflagbinary_printf /challenge/readflaghelper
exec python3 /challenge/main.py
