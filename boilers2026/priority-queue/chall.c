#include <stdbool.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

#define INITIAL_CAPACITY (8)
#define CAPACITY_MULTIPLIER (2)

char **array;
int capacity;
int size;

void move_up(int i) {
    int parent = (i - 1) / 2;

    while (i > 0 && strcmp(array[i], array[parent]) < 0) {
        char *temp = array[parent];
        array[parent] = array[i];
        array[i] = temp;

        i = parent;
        parent = (i - 1) / 2;
    }
}

void move_down(int i) {
    while (true) {
        int left = i * 2 + 1;
        int right = left + 1;

        int min = i;
        if (left < size && strcmp(array[left], array[min]) < 0) {
            min = left;
        }
        if (right < size && strcmp(array[right], array[min]) < 0) {
            min = right;
        }

        if (min == i) break;

        char *temp = array[i];
        array[i] = array[min];
        array[min] = temp;

        i = min;
    }
}

void insert(void) {
    puts("Message: ");
    char buffer[128] = { 0 };
    scanf("%127s", buffer);
    
    char *chunk = malloc(strlen(buffer) + 1);
    strcpy(chunk, buffer);

    if (capacity == size) {
        int new_capacity = capacity * CAPACITY_MULTIPLIER;
        char **new = calloc(new_capacity, sizeof(char *));
        memcpy(new, array, capacity * sizeof(char *));
        free(array);
        array = new;
        capacity = new_capacity;
    }

    array[size] = chunk;
    move_up(size++);
}

void delete(void) {
    if (size == 0) {
        puts("Queue is empty!");
        return;
    }

    puts(array[0]);
    free(array[0]);

    array[0] = array[--size];
    move_down(0);
}

void peek(void) {
    if (size == 0) {
        puts("Queue is empty!");
        return;
    }

    puts(array[0]);
}

void edit(void) {
    if (size == 0) {
        puts("Queue is empty!");
        return;
    }

    puts("Message: ");
    read(fileno(stdin), array[0], 32);

    move_down(0);
}

void count(void) {
    printf("%d\n", size);
}

int main(void) {
    setbuf(stdin, NULL);
    setbuf(stdout, NULL);
    setbuf(stderr, NULL);

    FILE *file = fopen("flag.txt", "r");
    if (file) {
        char *flag = malloc(100);
        fgets(flag, 100, file);
        fclose(file);
    }

    array = calloc(INITIAL_CAPACITY, sizeof(char *));
    capacity = INITIAL_CAPACITY;
    size = 0;

    puts("=== Welcome to the priority queue interface ===");

    while (true) {
        puts("Operation (insert/delete/peek/edit/count/quit): ");
        char buffer[16] = { 0 };
        scanf("%15s", buffer);

        if (strcmp(buffer, "insert") == 0) {
            insert();
        } else if (strcmp(buffer, "delete") == 0) {
            delete();
        } else if (strcmp(buffer, "peek") == 0) {
            peek();
        } else if (strcmp(buffer, "edit") == 0) {
            edit();
        } else if (strcmp(buffer, "count") == 0) {
            count();
        } else if (strcmp(buffer, "quit") == 0) {
            break;
        } else {
            puts("Invalid operation!");
        }
    }

    puts("Bye!");    
    return 0;
}
