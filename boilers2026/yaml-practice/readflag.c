#include <fcntl.h>
#include <stdio.h>
#include <string.h>
#include <unistd.h>

int main(int argc, char *argv[]) {

    if (argc != 5) {
        return 1;
    }

    if (strcmp(argv[1], "give") != 0 || strcmp(argv[2], "me") != 0 || strcmp(argv[3], "the") != 0 || strcmp(argv[4], "flag") != 0) {
        return 1;
    }

    char buf[256];
    ssize_t n;
    int fd = open("/flag", O_RDONLY);

    if (fd < 0) {
        perror("open");
        return 1;
    }

    n = read(fd, buf, sizeof(buf) - 1);
    if (n < 0) {
        perror("read");
        close(fd);
        return 1;
    }

    buf[n] = '\0';
    close(fd);

    puts(buf);
    return 0;
}
