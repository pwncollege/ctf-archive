#include <assert.h>
#include <malloc.h>
#include <stdbool.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define WF_IMM (0b1)
#define WF_MALLOC_PARAM (0b10)

typedef struct word {
  long flags;
  long length;
  long referenced_by;
  void (*code)(void *);
  void *param;
} word_t;

typedef struct dictionary {
  char *name;
  word_t *word;
  struct dictionary *next;
  bool alloc_name;
} dict_t;

#define FF_COMPILE (0b1)
#define FF_STOP_COMPILE (0b10)
#define FF_FORGET (0b100)

#define MAX_WORD_SIZE (1024)
#define MAX_WORDS (4096)
#define MAX_CONSTANTS (4096)

#define STACK_SIZE (1024)

typedef uint64_t cell;

cell g_flags = 0;
cell g_stack[STACK_SIZE];
word_t **g_ret[STACK_SIZE];
int g_sp = 0;
int g_rp = 0;

void panic(const char *msg) {
  fprintf(stderr, "%s\n", msg);
  exit(-1);
}

static inline cell *stack_top_ptr(int offset) {
  if (offset < 0 || offset >= g_sp) {
    panic("data stack underflow");
  }
  return &g_stack[g_sp - offset];
}

static inline cell stack_pop(void) {
  if (g_sp <= 0) {
    panic("data stack underflow");
  }
  return g_stack[g_sp--];
}

static inline void stack_push(cell value) {
  if (g_sp >= STACK_SIZE - 1) {
    panic("data stack overflow");
  }
  g_stack[++g_sp] = value;
}

static inline word_t **rstack_pop(void) {
  if (g_rp <= 0) {
    panic("return stack underflow");
  }
  return g_ret[g_rp--];
}

static inline void rstack_push(word_t **value) {
  if (g_rp >= STACK_SIZE - 1) {
    panic("return stack overflow");
  }
  g_ret[++g_rp] = value;
}

#define TOP(n) (*stack_top_ptr((n)))
#define POP (stack_pop())
#define SL (g_sp)
#define PUSH(n) (stack_push((n)))

#define RESETR (g_rp = 0)

#define POPR (rstack_pop())
#define PUSHR(w) (rstack_push((w)))
#define RL (g_rp)

void next() {
  if (RL == 0) {
    return;
  }
  word_t **next = POPR;
  PUSHR(next + 1);
  (*next)->code((*next)->param);
}

#define NEXT (next())

void docol(word_t **words) {
  PUSHR(words);
  NEXT;
}

void docon(cell *constant) {
  PUSH(*constant);
  NEXT;
}

void dosys(char *val) {
  system(val);
  NEXT;
}

void _forth_end(void *_) {
  POPR;
  if (RL == 0) {
    return;
  }
  NEXT;
}

void _drop(void *_) {
  POP;
  NEXT;
}
void _2drop(void *_) {
  POP;
  POP;
  NEXT;
}
void _add(void *_) {
  int a = POP;
  int b = POP;
  PUSH(a + b);
  NEXT;
}

void _dup(void *_) {
  int a = TOP(0);
  PUSH(a);
  NEXT;
}

void _2dup(void *_) {
  int a = TOP(0);
  int b = TOP(1);
  PUSH(b);
  PUSH(a);
  NEXT;
}

void _qdup(void *_) {
  int a = TOP(0);
  if (a) {
    PUSH(a);
  }
  NEXT;
}

void _over(void *_) {
  int a = TOP(1);
  PUSH(a);
  NEXT;
}

void _2over(void *_) {
  int a = TOP(3);
  int b = TOP(2);
  PUSH(a);
  PUSH(b);
  NEXT;
}

void _rot(void *_) {
  int x = TOP(2);
  int xp = TOP(1);
  int xpp = TOP(0);

  TOP(0) = x;
  TOP(1) = xpp;
  TOP(2) = xp;
  NEXT;
}

void _swap(void *_) {
  int x = TOP(1);
  int xp = TOP(0);
  TOP(0) = x;
  TOP(1) = xp;
  NEXT;
}

void _2swap(void *_) {
  int a0 = TOP(3);
  int a1 = TOP(2);
  int b0 = TOP(1);
  int b1 = TOP(0);

  TOP(3) = b0;
  TOP(2) = b1;
  TOP(1) = a0;
  TOP(0) = a1;
  NEXT;
}

void _dot(void *_) {
  printf("%ld ", POP);
  NEXT;
}
void _sdot(void *_) {
  for (int i = SL - 1; i >= 0; i--) {
    printf("%ld ", TOP(i));
  }
  NEXT;
}

void _col(void *_) { g_flags |= FF_COMPILE; }
void _semicolon(void *_) { g_flags |= FF_STOP_COMPILE; }
void _forget(void *_) { g_flags |= FF_FORGET; }

word_t *new_word(int flags) {
  word_t *w = malloc(sizeof(word_t));
  assert(w);
  w->flags = flags;
  w->length = 0;
  w->referenced_by = 0;
  return w;
}

word_t *make_primitive(void (*code)(void *), int flags) {
  word_t *w = new_word(flags);
  w->code = code;
  w->param = NULL;
  return w;
}

void add_word(dict_t **d, char *name, word_t *word, bool alloc_name) {
  dict_t *ent = malloc(sizeof(dict_t));
  assert(ent);

  ent->name = name;
  ent->word = word;
  ent->next = *d;
  ent->alloc_name = alloc_name;
  *d = ent;
}

void add_primitive(dict_t **d, char *name, void (*code)(void *), int flags) {
  word_t *w = make_primitive(code, flags);
  add_word(d, name, w, false);
}

int accept_integer(char *buf) {
  int r = 0;
  bool valid = false;
  while (*buf) {
    if (('0' <= *buf) && (*buf <= '9')) {
      r *= 10;
      r += *buf - '0';
      valid = true;
    } else {
      valid = false;
      break;
    }
    buf++;
  }

  return valid ? r : -1;
}

word_t *find_word(dict_t *dict, char *name) {
  while (dict) {
    if (strcmp(dict->name, name) == 0) {
      return dict->word;
    }
    dict = dict->next;
  }
  return NULL;
}

bool delete_word(dict_t **dict, char *name) {
  dict_t **pp = dict;
  dict_t *cur = *dict;
  while (cur) {
    if (strcmp(name, cur->name) == 0) {

      word_t *w = cur->word;
      if (w->flags & WF_MALLOC_PARAM) {
        free(w->param);
      }
      free(w);
      if (cur->alloc_name) {
        free(cur->name);
      }

      *pp = cur->next;
      free(cur);

      return true;
    }
    pp = &(cur->next);
    cur = cur->next;
  }
  return false;
}

#define TOKEN_SIZE (128)

void expect_token(char token[TOKEN_SIZE]) {
  if (fscanf(stdin, "%127s", token) != 1) {
    fprintf(stderr, "Tokens must be <= 127 chars\n");
    exit(-67);
  }
}

void push_word(word_t ***list, word_t *word, int *used, int *capacity) {
  if ((*used) >= (*capacity)) {
    (*capacity) *= 2;
    if ((*capacity) > MAX_WORD_SIZE) {
      fprintf(stderr, "fat word\n");
      exit(-1);
    }
    *list = realloc(*list, sizeof(word_t *) * (*capacity));
    assert(*list);
  }

  (*list)[(*used)] = word;
  *used += 1;
}

int main(int argc, char **argv) {

  setbuf(stdin, NULL);
  setbuf(stdout, NULL);
  setbuf(stderr, NULL);

  char token[TOKEN_SIZE];
  char *compile_name = NULL;
  word_t **compile_def = NULL;
  int compile_word_count = 0;
  int compile_word_capacity = 0;

  int words_created = 0;
  int constants_created = 0;

  bool compiling = false;
  word_t *word_end = make_primitive(_forth_end, 0);

  dict_t *dict = NULL;
  add_primitive(&dict, ":", _col, WF_IMM);
  add_primitive(&dict, ";", _semicolon, WF_IMM);
  add_primitive(&dict, "forget", _forget, WF_IMM);
  add_primitive(&dict, "drop", _drop, 0);
  add_primitive(&dict, "+", _add, 0);
  add_primitive(&dict, "dup", _dup, 0);
  add_primitive(&dict, "2dup", _2dup, 0);
  add_primitive(&dict, "?dup", _qdup, 0);
  add_primitive(&dict, "over", _over, 0);
  add_primitive(&dict, "2over", _2over, 0);
  add_primitive(&dict, "rot", _rot, 0);
  add_primitive(&dict, "swap", _swap, 0);
  add_primitive(&dict, "2swap", _2swap, 0);
  add_primitive(&dict, ".", _dot, 0);
  add_primitive(&dict, ".s", _sdot, 0);

  printf("I heard computers like stacks, so I went forth and made a few\n"
         "It's lacking a few words, maybe you could implement them for me?\n"
         "%p\n",
         dosys);

  while (true) {

    if (g_flags & FF_COMPILE) {
      if (compiling) {
        fprintf(stderr, "already compiling\n");
        exit(-1);
      }
      g_flags &= ~FF_COMPILE;
      if (words_created >= MAX_WORDS) {
        fprintf(stderr, "too many words\n");
        exit(-1);
      }

      expect_token(token);

      compile_name = malloc(strlen(token) + 1);
      assert(compile_name);

      strcpy(compile_name, token);
      compile_word_capacity = 16;
      compile_word_count = 0;
      compile_def = malloc(sizeof(word_t *) * compile_word_capacity);
      assert(compile_def);

      compiling = true;
    }

    if (g_flags & FF_STOP_COMPILE) {
      if (!compiling) {
        panic("already not compiling\n");
      }
      g_flags &= ~FF_STOP_COMPILE;

      compiling = false;
      words_created++;

      push_word(&compile_def, word_end, &compile_word_count,
                &compile_word_capacity);

      word_t *nw = make_primitive((void (*)(void *))docol, WF_MALLOC_PARAM);
      nw->param = compile_def;
      nw->length = compile_word_count;

      add_word(&dict, compile_name, nw, true);
      printf("defined a new word %s\n", compile_name);
    }

    if (g_flags & FF_FORGET) {
      g_flags &= ~FF_FORGET;
      expect_token(token);
      if (delete_word(&dict, token)) {
        printf("forgot word %s\n", token);
      } else {
        printf("word not found\n");
      }
    }

    expect_token(token);

    word_t *word = find_word(dict, token);
    int number = 0;
    if (word == NULL) {
      number = accept_integer(token);
      if (number < 0) {
        panic("you spelled a word incorrectly :(\n");
      }
    }

    if (compiling) {
      if (!word) {
        if (constants_created >= MAX_CONSTANTS) {
          panic("too many constants\n");
        }
        constants_created += 1;
        word = make_primitive((void (*)(void *))docon, WF_MALLOC_PARAM);
        cell *n = malloc(sizeof(cell));
        assert(n);
        *n = number;
        word->param = n;
      }

      if (word->flags & WF_IMM) {
        RESETR;
        word->code(word->param);
      } else {
        word->referenced_by += 1;
        push_word(&compile_def, word, &compile_word_count,
                  &compile_word_capacity);
      }
    } else {

      RESETR;
      if (word) {
        word->code(word->param);
      } else {
        PUSH(number);
      }
    }
  }

  return 0;
}
