// James Simmons
// 3/21/2019
// "Time Warp" CTF challenge for SunshineCTF 2019

#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <string.h>

#define NUM_LOOPS 300
#define GUESS_RANGE 1000
#define QUOTES 0

void do_challenge(void);
void printRandMessage(int expected);
void win(void);
void give_flag(void);
void lose(void);

void do_challenge(void)
{
	int expected = -1, response, i, start = time(NULL), elapsed;

	srand(301570435);

	printf("I'm going to give you some numbers between 0 and %d.\n", GUESS_RANGE - 1);
	printf("Repeat them back to me in 30 seconds or less!\n");

	for (i = 0; i < NUM_LOOPS; i++)
	{
		scanf("%d", &response);
		expected = rand() % GUESS_RANGE;
		printf("%d\n", expected);
		elapsed = time(NULL) - start;

		if (response != expected || elapsed > 30)
			break;
		if (i < NUM_LOOPS - 1)
			printRandMessage(expected);
	}

	if (i >= NUM_LOOPS)
		win();
	else
		lose();
}

void printRandMessage(int expected)
{
	int message = (expected + time(NULL)) % 10;

	switch (message)
	{
		case 0:
			printf("N1ce 0ne! Do it ag4in!\n");
			break;
		case 1:
			printf("It's w0rking!\n");
			break;
		case 2:
			printf("Keep it up! The anoma1y is subsiding!\n");
			break;
		case 3:
			printf("How ar3 you doing th1s?!\n");
			break;
		case 4:
			printf("This is h0nestly impressive!\n");
			break;
		case 5:
			printf("Icr3dible!\n");
			break;
		case 6:
			printf("Am4z1ng!\n");
			break;
		case 7:
			printf("G3tting cl0ser!\n");
			break;
		case 8:
			printf("Alm0st there!\n");
			break;
		case 9:
			printf("Gre4t j0b!\n");
			break;
	}

}

void win(void)
{
	printf("Wow! You did it!\n");
	printf("As reward for fixing the timestream, here's the flag:\n");
	give_flag();
}

void give_flag(void) {
	FILE* fp = fopen("flag.txt", "r");
	if(!fp) {
		printf("Failed to open flag.txt, please contact an admin!\n");
		exit(EXIT_FAILURE);
	}
	
	char line[200];
	if(!fgets(line, sizeof(line), fp)) {
		printf("Failed to read flag.txt contents, please contact an admin!\n");
	}
	
	fclose(fp);
	
	// Strip trailing newline if present
	char* newline = strchr(line, '\n');
	if(newline) {
		*newline = '\0';
	}
	
	// Print flag
	printf("%s\n", line);
}

void lose(void)
{
	int message = (rand() + time(NULL))  % 10;

	switch (message)
	{
		case 0:
			printf("Uh 0h, th4t's n0t it!\n");
			break;
		case 1:
			printf("N0t qu1te!\n");
			break;
		case 2:
			printf("0h de4r, not aga1n!\n");
			break;
		case 3:
			printf("Why 1s th15 h4pp3n1ng?!\n");
			break;
		case 4:
			printf("H3r3 w3 g0 aga1n!\n");
			break;
		case 5:
			printf("00f, th4t can't b3 g00d!\n");
			break;
		case 6:
			printf("Y0u kn0w wh4t y0u're do1ng, right?!\n");
			break;
		case 7:
			printf("G3tting c0lder!\n");
			break;
		case 8:
			printf("Wh00ps!\n");
			break;
		case 9:
			printf("0h, w3ll!\n");
			break;
		}
}

int main(void)
{
	do_challenge();
	return 0;
}
