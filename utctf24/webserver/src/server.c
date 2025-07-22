
#include "stdio.h"
#include "stdlib.h"
#include "string.h"
#include "setjmp.h"
#include "dirent.h"
#include "unistd.h"
#include "fcntl.h"
#include "sys/stat.h"
#include "sys/sendfile.h"

// {METHOD} {PATH} HTTP/1.1\r\n
// {header}: {value}\r\n
// {header}: {value}\r\n
// {header}: {value}\r\n
// \r\n

char *take_until_char(char *s, int *i, char c) {
    int start = *i;
    for (; s[*i] != c && s[*i] != '\0'; (*i)++);
    s[*i] = '\0';
    (*i)++;
    return &s[start];
}
char *take_until_newline(char *s, int *i) {
    int start = *i;
    while (1) {
        char cur = s[*i];
        if (cur == '\r' || cur == '\n' || cur == '\0') break;
        *i += 1;
    }
    int end = *i;
    while (1) {
        char cur = s[*i];
        if (cur != '\r' && cur != '\n') break;
        *i += 1;
    }
    s[end] = '\0';
    *i += 1;
    return &s[start];
}

char *malloc_str(const char *s) {
    return strcpy(malloc(strlen(s) + 1), s);
}
char *malloc_str_len(const char *s, int len) {
    char* buf = malloc(len + 1);
    buf[len] = '\0';
    return strncpy(buf, s, len);
}

char *resolve_path(const char *base, char *path) {
    int len = strlen(path);
    int write = 1;
    int read = 1;
    int seg_start = 1;
    if (path[0] != '/') return NULL;

    int segment_count = 0;
    char **segments = NULL;

    while (1) {
        if (path[read] == '/' || path[read] == '\0') {
            // end of segment; segment = [seg_start, read)
            int seg_len = read - seg_start;
            if (seg_len == 2 && strncmp(&path[seg_start], "..", seg_len) == 0) {
                // remove prev seg
                segment_count = segment_count > 0 ? segment_count - 1 : 0;
                segments = realloc(segments, segment_count * sizeof(char*));
            } else if (seg_len == 1 && strncmp(&path[seg_start], ".", seg_len) == 0) {
                // skip this
            } else {
                // add new seg
                segments = realloc(segments, (segment_count + 1) * sizeof(char*));
                segments[segment_count] = malloc_str_len(&path[seg_start], seg_len);
                // printf("segment: '%s' %d\n", segments[segment_count], seg_len);
                segment_count += 1;
            }

            if (path[read] == '\0') {
                break;
            }
            read += 1;
            seg_start = read;
        } else {
            read += 1;
        }
    }

    int base_len = strlen(base);

    int out_len = 0;
    out_len += base_len;
    for (int i = 0; i < segment_count; i++) {
        out_len += 1 + strlen(segments[i]);
    }
    char *buf_out = malloc(out_len + 1);
    int index = 0;

    memcpy(&buf_out[index], base, base_len);
    index += base_len;

    for (int i = 0; i < segment_count; i++) {
        buf_out[index] = '/';
        index += 1;
        int len = strlen(segments[i]);
        memcpy(&buf_out[index], segments[i], len);
        index += len;
        free(segments[i]);
        segments[i] = NULL;
    }
    free(segments);
    buf_out[index] = '\0';
    return buf_out;
}

extern char *gets(char *s);

struct Header {
    char *name;
    char *value;
};


typedef void (*handler_fn)(char *method, char *path, char *version, int header_count, struct Header *headers, char *data, jmp_buf err);

void debug_handler(char *method, char *path, char *version, int header_count, struct Header *headers, char *data, jmp_buf err) {
    printf("HTTP/1.1 %d %s\r\n", 403, "Forbidden");
    printf("Content-Type: %s\r\n", "text/html");
    printf("\r\n");

    printf("<body>\n");
    printf("<style>code{background:#EEE;padding:0.1em 0.3em;}</style>\n");

    printf("<h1>Forbidden</h1>\n");

    printf("<h2>Query: <code>%s</code> <code>%s</code> <code>%s</code></h2>\n", method, path, version);
    char *resolved_path = resolve_path("", path);
    printf("<h2>Resolved path: <code>%s</code></h2>\n", resolved_path);
    printf("<ul>\n");
    for (int i = 0; i < header_count; i++) {
        printf("<li><code>%s</code>: <code>%s</code></li>\n", headers[i].name, headers[i].value); // reflected XSS? (html in referrer or other header?)
    }
    printf("</ul></body>\n");
}

void print_error(int code, char* msg) {
    printf("HTTP/1.1 %d %s\r\n", code, msg);
    printf("Content-Type: %s\r\n", "text/html");
    printf("\r\n");

    printf("<!doctype html><html lang='en'>\n");
    printf("<head>\n  <meta charset='utf-8'>\n  <meta name='viewport' content='width=device-width, initial-scale=1'>\n</head>\n");
    printf("<body>\n");
    printf("<h1>Error: %d %s</h1>\n", code, msg);
    printf("</body>\n</html>\n");
}

int ends_with(const char* str, const char* suffix) {
    int str_len = strlen(str);
    int suffix_len = strlen(suffix);
    if (str_len < suffix_len) return 0;
    return strcmp(&str[str_len - suffix_len], suffix) == 0;
}

void fileserv_handler(char *method, char *path, char *version, int _header_count, struct Header *_headers, char *data, jmp_buf err) {
    char *resolved_path = resolve_path("./", path);
    if (resolved_path == NULL) {
        print_error(400, "Bad Request");
        return;
    }

    struct stat pstat;

    int fd = open(resolved_path, O_RDONLY);
    if (fd == -1) {
        print_error(404, "Not Found");
        return;
    }

    if (fstat(fd, &pstat) == -1) {
        close(fd);
        print_error(500, "Internal server error");
        return;
    }

    if (S_ISREG(pstat.st_mode)) {
        char* content_type = "text/plain";
        if (ends_with(resolved_path, ".html")) {
            content_type = "text/html";
        }

        printf("HTTP/1.1 %d %s\r\n", 200, "OK");
        printf("Content-Type: %s\r\n", content_type);
        printf("Content-Length: %ld\r\n", pstat.st_size);
        printf("\r\n");

        sendfile(0, fd, NULL, pstat.st_size);
        close(fd);
    } else if (S_ISDIR(pstat.st_mode)) {
        int files_cap = 0;
        int files_len = 0;
        char **files = NULL;

        DIR *dir;
        struct dirent *ent;
        if ((dir = fdopendir(fd)) != NULL) {
            while ((ent = readdir(dir)) != NULL) {
                if (files_len + 1 > files_cap) {
                    int new_cap = files_cap < 4 ? 4 : files_cap * 2;
                    files = realloc(files, sizeof(char*) * new_cap);
                    files_cap = new_cap;
                }
                files[files_len] = malloc_str(ent->d_name);
                files_len += 1;
            }
            closedir(dir);
        } else {
            close(fd);
            longjmp(err, 16);
        }

        for (int i = 0; i < files_len; i++) {
            int min = i;
            for (int j = i + 1; j < files_len; j++) {
                if (strcmp(files[min], files[j]) > 0) {
                    min = j;
                }
            }
            char* tmp = files[i];
            files[i] = files[min];
            files[min] = tmp;
        }

        printf("HTTP/1.1 %d %s\r\n", 200, "OK");
        printf("Content-Type: %s\r\n", "text/html");
        printf("\r\n");

        printf("<body>\n");
        printf("<style>code{background:#EEE;padding:0.1em 0.3em;}</style>\n");
        printf("<h1>Query: <code>%s</code> <code>%s</code> <code>%s</code></h1>\n", method, path, version);
        printf("<h1>Resolved path: <code>%s</code></h1>\n", resolved_path);
        printf("<ul>\n");

        char last_char = path[strlen(path) - 1];
        for (int i = 0; i < files_len; i++) {
            if (last_char == '/') {
                printf("<li><a href=\"%s\">%s</a></li>\n", files[i], files[i]);
            } else {
                printf("<li><a href=\"%s/%s\">%s</a></li>\n", path, files[i], files[i]);
            }
        }

        printf("</ul></body>\n");

    } else {
        close(fd);
        print_error(500, "Internal server error");
        return;
    }
}



int main(int argc, char **argv) {
    int ret = 1;

    int jmp_res;
    jmp_buf err;

    if ((jmp_res = setjmp(err)) != 0) {
        ret = jmp_res;
        goto error;
    }

    char buf[512];

    char *method;
    char *path;
    char *version;

    {
        char *query_line = gets(buf);
        if (query_line == NULL) longjmp(err, 1);

        int index = 0;
        method = take_until_char(query_line, &index, ' ');
        path = take_until_char(query_line, &index, ' ');
        version = take_until_newline(query_line, &index);

        method = malloc_str(method);
        path = malloc_str(path);
        version = malloc_str(version);
    }

    if (strcmp(version, "HTTP/1.0") != 0 && strcmp(version, "HTTP/1.1") != 0) longjmp(err, 2);

    int header_count = 0;
    int header_cap = 0;
    struct Header *headers = NULL;

    int content_length = 0;

    for (;;) {
        char *header_line = gets(buf);
        if (header_line == NULL) longjmp(err, 1);
        if (strlen(header_line) == 0 || strcmp(header_line, "\r") == 0) break; // "\n" or "\r\n"; end of query

        int index = 0;
        char *name = take_until_char(header_line, &index, ':');
        char *value = take_until_newline(header_line, &index);
        name = malloc_str(name);
        value = malloc_str(value);

        if (header_count + 1 > header_cap) {
            int new_cap = header_cap < 4 ? 4 : header_cap * 2;
            headers = realloc(headers, sizeof(struct Header) * new_cap);
            header_cap = new_cap;
        }
        struct Header h;
        h.name = name;
        h.value = value;
        headers[header_count] = h;
        header_count += 1;

        if (strcasecmp(h.name, "content-length") == 0) {
            char* end = NULL;
            int value = strtol(h.value, &end, 10);
            if (end != h.value) {
                content_length = value;
            }
        }
    }

    int max_length = 128 * 1024;
    content_length = content_length > max_length ? max_length : content_length;

    char* data = "";
    if (content_length != 0) {
        data = (char*) malloc(content_length + 1);
        fread(data, content_length, 1, stdin);
        data[content_length] = 0;
    }

    handler_fn handler;
    
    if (strstr(path, "flag.txt") != NULL) {
        handler = debug_handler;
    } else {
        handler = fileserv_handler;
    }

    handler(method, path, version, header_count, headers, data, err);

    ret = 0;
    goto cleanup;

error:
    printf("error\n");

cleanup:
    free(method);
    free(path);
    free(version);
    free(headers);

    exit(ret);
}

