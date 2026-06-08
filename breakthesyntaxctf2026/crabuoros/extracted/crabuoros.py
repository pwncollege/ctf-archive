import curses
import random
import crab


def snake(stdscr):
    curses.curs_set(0)
    stdscr.nodelay(True)
    stdscr.timeout(120)

    h, w = stdscr.getmaxyx()
    snake = [(h // 2, w // 2)]
    direction = (0, 1)
    food = (random.randint(1, h - 2), random.randint(1, w - 2))
    score = 0

    while True:
        stdscr.clear()

        # Draw border
        stdscr.border()

        # Draw food
        stdscr.addch(food[0], food[1], "*")

        # Draw snake
        for i, (y, x) in enumerate(snake):
            if i == 0:
                stdscr.addstr(y, x, "🦀")
            else:
                stdscr.addch(y, x, "o")

        # Score
        stdscr.addstr(0, 2, f" Score: {score} ")

        stdscr.refresh()

        # Input
        key = stdscr.getch()
        if key == curses.KEY_UP and direction != (1, 0):
            direction = (-1, 0)
        elif key == curses.KEY_DOWN and direction != (-1, 0):
            direction = (1, 0)
        elif key == curses.KEY_LEFT and direction != (0, 1):
            direction = (0, -1)
        elif key == curses.KEY_RIGHT and direction != (0, -1):
            direction = (0, 1)
        elif key == ord("q"):
            break

        # Move
        new_head = (snake[0][0] + direction[0], snake[0][1] + direction[1])

        # Collision with wall or self
        if (
            new_head[0] <= 0
            or new_head[0] >= h - 1
            or new_head[1] <= 0
            or new_head[1] >= w - 1
            or new_head in snake
        ):
            stdscr.addstr(h // 2, w // 2 - 5, "GAME OVER!")
            stdscr.nodelay(False)
            stdscr.getch()
            break

        snake.insert(0, new_head)

        if new_head == food:
            score += 1
            food = (random.randint(1, h - 2), random.randint(1, w - 2))
        else:
            snake.pop()


if __name__ == "__main__":
    if crab.check_serial_code(input("Please enter a serial key: ")):
        curses.wrapper(snake)
    else:
        print("Incorrect")
