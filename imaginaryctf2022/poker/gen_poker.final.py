#!/usr/bin/exec-suid -- /usr/bin/python3 -I

import random
from math import ceil, prod
from operator import lshift, rshift
from string import ascii_uppercase, ascii_lowercase

HEARTS = "🂱🂲🂳🂴🂵🂶🂷🂸🂹🂺🂻🂽🂾"
SPADES = "🂡🂢🂣🂤🂥🂦🂧🂨🂩🂪🂫🂭🂮"
DIAMONDS = "🃁🃂🃃🃄🃅🃆🃇🃈🃉🃊🃋🃍🃎"
CLUBS = "🃑🃒🃓🃔🃕🃖🃗🃘🃙🃚🃛🃝🃞"
DECK = SPADES+HEARTS+DIAMONDS+CLUBS  # Bridge Ordering of a Deck
DECK_SIZE = 52
ALPHABET52 = ascii_uppercase + "abcdefghijklmnop_rstuvw{y}"

CARDS_PER_DEAL = 25
DEALS_PER_PUZZLE = 750
assert CARDS_PER_DEAL % 2 == 1
MAX_DEAL = prod(x for x in range(DECK_SIZE - CARDS_PER_DEAL + 1, DECK_SIZE + 1))
DEAL_BITS = MAX_DEAL.bit_length()

INTS_PER_DEAL = ceil(DEAL_BITS / 32)
MT_STATE_SIZE = 624
INT_MASK = 0xFFFFFFFF

############### Puzzle Solution Code ###############
def unxorshift(x, operator, shift, mask=INT_MASK):
    res = x
    for i in range(32):
        res = x ^ (operator(res, shift) & mask)
    return res

def untemper(random_int):
    random_int = unxorshift(random_int, rshift, 18)
    random_int = unxorshift(random_int, lshift, 15, 0xefc60000)
    random_int = unxorshift(random_int, lshift, 7, 0x9d2c5680)
    random_int = unxorshift(random_int, rshift, 11)
    return random_int

def temper(state_int):
    state_int ^= (state_int >> 11)
    state_int ^= (state_int << 7) & 0x9d2c5680
    state_int ^= (state_int << 15) & 0xefc60000
    state_int ^= (state_int >> 18)
    return state_int

def next_partial(i, i1):
    y = (i & 0x80000000) + (i1 & 0x7fffffff) 
    next = y >> 1
    if (y & 1) == 1:
        next ^= 0x9908b0df
    return next

def next_value(i, i1, i397):
    return next_partial(i, i1) ^ i397

def generate_next(current_state, next_state):
    accurate_predictions = 0
    for i in range(MT_STATE_SIZE):
        state_i1 = current_state[i + 1] if i + 1 < MT_STATE_SIZE else next_state[(i + 1) % MT_STATE_SIZE]
        state_i397 = current_state[i + 397] if i + 397 < MT_STATE_SIZE else next_state[(i + 397) % MT_STATE_SIZE]
        if current_state[i] and state_i1 and state_i397:
            next = next_value(current_state[i], state_i1, state_i397)
            if not next_state[i]:
                next_state[i] = next
            elif next_state[i] == next:
                accurate_predictions += 1
            else:
                print(f"MISMATCH: {i} - {current_state[i]} {state_i1} {state_i397} : {next} {next_state[i]}") 
                raise ValueError
    print(f"=== {accurate_predictions} Successful Predictions")
    return next_state

def backfill(current_state, next_state):
    for i in range(MT_STATE_SIZE):
        state_i1 = current_state[i + 1] if i + 1 < MT_STATE_SIZE else next_state[(i + 1) % MT_STATE_SIZE]
        if current_state[i] and state_i1 and next_state[i]:
            if i + 397 < MT_STATE_SIZE and not current_state[i + 397]:
                current_state[i + 397] = next_state[i] ^ next_partial(current_state[i], state_i1)
                print(f"---Backfill {i + 397}: {current_state[i + 397]}")
            elif i + 397 >= MT_STATE_SIZE and not next_state[(i + 397) % MT_STATE_SIZE]:
                next_state[(i + 397) % MT_STATE_SIZE] = next_state[i] ^ next_partial(current_state[i], state_i1)
                print(f"---Backfill {(i + 397) % MT_STATE_SIZE}: {next_state[(i + 397) % MT_STATE_SIZE]}")
    return current_state, next_state

def resolve_shuffle_possibilities(states, possibles):
    for i, deal_list in possibles.items():
        deal_index = i % INTS_PER_DEAL
        current_state = states[i // MT_STATE_SIZE][i % MT_STATE_SIZE]
        if i in possibles and current_state and len(possibles[i]) > 1:
            print(f"==== Resolve {i} {deal_index}: {possibles[i]} - {current_state}")
            for value in deal_list:
                computed = untemper(value >> (deal_index * 32) & INT_MASK)
                if computed == current_state:
                    for j, shift in enumerate(range(0, 32 * (INTS_PER_DEAL - 1), 32)):
                        offset = i - deal_index + j
                        if offset in possibles:
                            possibles[offset] = [untemper(value >> shift & INT_MASK)]
    for i in list(possibles.keys()):
        if i in possibles and len(possibles[i]) == 1:
            states[i // MT_STATE_SIZE][i % MT_STATE_SIZE] = possibles.pop(i)[0]  
    return states, possibles

def convert_deal_to_int(deal):
    total = 0
    for i in range(CARDS_PER_DEAL - 1, -1, -1):
        total = total * (DECK_SIZE - i) + [card for card in DECK if card not in deal[:i]].index(deal[i])
    return total

def load_state_arrays_from_deals(deals):
    rotations = ceil(len(deals) * INTS_PER_DEAL / MT_STATE_SIZE)
    state = [[None]*MT_STATE_SIZE for _ in range(rotations)]
    possibles = {}
    index = 0
    for deal in deals:
        shuffle = convert_deal_to_int(deal)
        if shuffle + MAX_DEAL >= (1 << DEAL_BITS) - 1:
            for i, shift in enumerate(range(0, 32 * (INTS_PER_DEAL - 1), 32)):
                state[(index + i) // MT_STATE_SIZE][(index + i) % MT_STATE_SIZE] = untemper(shuffle >> shift & INT_MASK)
        else:
            for i in range(0, INTS_PER_DEAL - 1):
                possibles[index + i] = []
                for possible_shuffle in range(shuffle, (1 << DEAL_BITS) - 1, MAX_DEAL):
                    possibles[index + i] += [possible_shuffle]
        index += INTS_PER_DEAL
    return state, possibles

def process_cards_file():
    deals = []
    with open("cards.txt", "r") as poker_file:
        for line in poker_file:
            cards_in_line = "".join([card for card in line if card in DECK])
            if "1: " == line[:3]: deals.append(cards_in_line)
            if "Table:" == line[:6]: deals[-1] += cards_in_line
    return deals

def solve_puzzle():
    deals = process_cards_file()
    print(f"Last Provided Deal... Rotation: {(len(deals) * INTS_PER_DEAL) // MT_STATE_SIZE} - State: {(len(deals) * INTS_PER_DEAL) % MT_STATE_SIZE}")
    states, possibles = load_state_arrays_from_deals(deals)

    has_changes = True
    while has_changes:
        has_changes = False
        print("..... Predicting missing values .....")
        for i in range((len(deals) * INTS_PER_DEAL) // MT_STATE_SIZE):
            current_nones = states[i].count(None) + states[i + 1].count(None)
            states[i], states[i + 1] = backfill(states[i], states[i + 1])
            states[i + 1] = generate_next(states[i], states[i + 1])
            has_changes = has_changes or not (current_nones == (states[i].count(None) + states[i + 1].count(None)))
        states, possibles = resolve_shuffle_possibilities(states, possibles)
    for i in range(len(deals) * INTS_PER_DEAL, (len(deals) * INTS_PER_DEAL) + 10):
        print("Future Value {}: {}".format(i, temper(states[i // MT_STATE_SIZE][i % MT_STATE_SIZE])))

        

############### Proof of Concept Code ###############

def verify_how_rand_retrieves_bits_from_MT(fixed):
    # MAX_123 = (1 << 123) - 1
    print("------ Random Bit Generations")
    random.seed(fixed)
    value = random.random()
    state = random.getstate()
    print(f"{state[0]} {state[1][:12]} {state[1][MT_STATE_SIZE]} {state[2]}")
    print(f"{(state[1][0])} {(state[1][1])} {(state[1][2])} {(state[1][3])}")
    print(f"{temper(state[1][0])} {temper(state[1][1])} {temper(state[1][2])} {temper(state[1][3])}")
    random.seed(fixed)
    print("DEAL23: {0:0123b}".format(random.randrange(MAX_DEAL)))
    random.seed(fixed)
    print("123:    {0:0123b}".format(random.getrandbits(123)))
    random.seed(fixed)
    print("   {0:0128b}".format(random.getrandbits(128)))
    random.seed(fixed)
    deal = random.getrandbits(DEAL_BITS) % MAX_DEAL
    print("Deal:   {0:0123b}\n      {0}".format(deal))
    state_word = deal &0xFFFFFFFF
    print("Int 1:" + (" " * 93) + "{0:032b} {0}".format(state_word))
    state_word = deal>>32 &0xFFFFFFFF
    print("Int 2:" + (" " * 61) + "{0:032b} {0}".format(state_word))
    state_word = deal>>64 &0xFFFFFFFF
    print("Int 3:" + (" " * 29) + "{0:032b} {0}".format(state_word))
    state_word = deal>>96 &0x07FFFFFF
    print("27 bit: " + "{0:027b} {0}".format(state_word))
    random.seed(fixed)
    print(f"{random.getrandbits(32):032b} {random.getrandbits(32):032b} {random.getrandbits(32):032b} {random.getrandbits(32):032b}")
    random.seed(fixed)
    print(f"{untemper(random.getrandbits(32))} {untemper(random.getrandbits(32))} {untemper(random.getrandbits(32))} {untemper(random.getrandbits(32))}")

def verify_back_solve():
    source_array = []
    for i in range(MT_STATE_SIZE * 2):
        source_array.append(untemper(random.getrandbits(32)))
    back_generate = back_generate_624(source_array[MT_STATE_SIZE:])
    for i in range(MT_STATE_SIZE):
        assert back_generate[i] == source_array[i]

def cards_from_text(string):
    return string.translate(string.maketrans(ALPHABET52, DECK))

def back_generate_624(source624):
    assert len(source624) == MT_STATE_SIZE
    mt_array = [None]*MT_STATE_SIZE + source624
    for i in range(MT_STATE_SIZE - 1, -1, -1):
        highbit = mt_array[i + 624] ^ mt_array[i + 397]
        highbit = (highbit ^ 0x9908b0df) << 1 | 1 if highbit >> 31 else highbit << 1
        low31 = mt_array[i + 623] ^ mt_array[i + 396]
        low31 = (low31 ^ 0x9908b0df) << 1 | 1 if low31 >> 31 else low31 << 1
        mt_array[i] = (highbit & 0x80000000) | (low31 & 0x7FFFFFFF)
    return mt_array
 
def generate_initial_state_for_text(goal_string):
    needed_states = []
    for substring in [goal_string[start:start + CARDS_PER_DEAL] for start in range(0, len(goal_string), CARDS_PER_DEAL)]:
        assert len(set(substring)) == len(substring), f"Duplicate character found in: {substring}"
        assert len(set(substring) & set(ALPHABET52)) == len(substring), f"Character not in Alphabet: {substring}"
        if len(substring) < CARDS_PER_DEAL:
            substring += "".join(set(ALPHABET52) - set(substring) - set('_}{'))[:CARDS_PER_DEAL - len(substring)]
        cards = cards_from_text(substring)
        needed_int = convert_deal_to_int(cards)
        for shift in range(0, 32 * INTS_PER_DEAL, 32):
            needed_states += [untemper(needed_int >> shift & INT_MASK)]
        print(f"{substring} : {cards} - {needed_int}")
    print(needed_states)
    return needed_states

def create_initial_state(needed_states):
    final_array = [random.getrandbits(32) for _ in range( MT_STATE_SIZE - len(needed_states))] + needed_states
    for i in range(DEALS_PER_PUZZLE * INTS_PER_DEAL, 0, -MT_STATE_SIZE):
        new_array = back_generate_624(final_array[:MT_STATE_SIZE])
        final_array = new_array + final_array[MT_STATE_SIZE:]
    end_of_initial_state = len(final_array) - (DEALS_PER_PUZZLE * INTS_PER_DEAL) - len(needed_states)
    initial_state_array = final_array[end_of_initial_state - MT_STATE_SIZE:end_of_initial_state]
    return (3, tuple(initial_state_array+[MT_STATE_SIZE]), None)


############### Puzzle Creation Code ###############

def deal_game():
    shuffle = random.getrandbits(DEAL_BITS) % MAX_DEAL
    # print(f"{shuffle}: {shuffle + MAX_DEAL > (1 << DEAL_BITS) - 1} {shuffle &0xFFFFFFFF}:{untemper(shuffle &0xFFFFFFFF)} {shuffle>>32 &0xFFFFFFFF}:{untemper(shuffle>>32 &0xFFFFFFFF)} {shuffle>>64 &0xFFFFFFFF}:{untemper(shuffle>>64 &0xFFFFFFFF)}")
    deck = list(DECK)
    deal = ""
    while shuffle > 0:
        deal += deck.pop(shuffle % len(deck))
        shuffle //= len(deck) + 1
    while len(deal) < CARDS_PER_DEAL:
        deal += deck.pop(0)
    return deal

def print_puzzle():
    with open("cards.txt", "w") as cards_file:
        for i in range(DEALS_PER_PUZZLE):
            table = deal_game()
            cards_file.write(f"Game {i+1}:\n")
            for i in range((CARDS_PER_DEAL - 5) // 2):
                cards_file.write(f"{i + 1}: {table[i * 2]}{table[i * 2 + 1]}  ")
            cards_file.write("\nTable: {}{}{} {} {}\n\n".format(*table[CARDS_PER_DEAL - 5:]))

if __name__ == "__main__":
    verify_how_rand_retrieves_bits_from_MT(1)
    verify_back_solve()
    flag = open("/flag_poker", "r").read().strip()
    needed_states = generate_initial_state_for_text(flag)
    seed_state = create_initial_state(needed_states)

    random.setstate(seed_state)
    print("Generating cards.txt....")
    print_puzzle()
    for i in range(10):
        r = random.getrandbits(32)
        print(f"Try to predict: {i + 1} - {r} {untemper(r)}")
    print("Solving for games in cards.txt....")
    solve_puzzle()


