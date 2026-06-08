import random
from datetime import datetime
import hashlib
import hmac

class DiceGame():
    def __init__(self):
        self.balance = 800
        self.client_seed = "1337awesome"
        self.nonce = 0
        self.running = True
        with open("./serverseed", 'r') as f:
            random.seed(f.read())

    def roll_dice(self) -> int:
        self.server_seed = random.getrandbits(32)
        nonce_client_msg = f"{self.client_seed}-{self.nonce}".encode()
        sig = hmac.new(str(self.server_seed).encode(), nonce_client_msg, hashlib.sha256).hexdigest()
        index = 0
        lucky = int(sig[index*5:index*5+5], 16)
        while (lucky >= 1e6):
            index += 1
            lucky = int(sig[index * 5:index * 5 + 5], 16)
            if (index * 5 + 5 > 129):
                lucky = 9999
                break
        return round((lucky % 1e4) * 1e-2)

    def show_starting_banner(self) -> None:
        print(f"Balance: {self.balance}")
        print("options: ")
        print(" a) view shop ")
        print(" b) gamble ")
        print(" c) set own seed ")
        print(" d) exit ")
    
    def gamble_game(self) -> None:
        print("ULTIMATE BET WIN BIG MONEY!!!")
        wager = float(input(f"Wager per game (min-wager is {self.balance/800}): "))
        if (wager < self.balance/800 or wager > self.balance):
            print("Wager not in range!") 
            return
        games = int(input("Number of games (int): "))
        greed = float(input("Enter your number higher or equal to the roll between 2-98 (prize improves with lower numbers): ")) # win if number is between 0 and n
        if (greed < 2 or greed > 98):
            print("Greed not in range!")
            return
        confirm = input("Do you wish to proceed? (Y/N)")
        if confirm == "Y":
            for i in range(games):
                if (self.balance - wager >= 0):
                    roll = self.roll_dice() 
                    multiplier = (100-1)/(greed)
                    self.balance -= wager
                    reward_value = 0
                    if (greed >= roll):
                        reward_value = wager * multiplier
                    print(f"Game {i:05}: Roll: {roll:02}, Reward: {reward_value}, Nonce: {self.nonce}, Client-Seed: {self.client_seed}, Server-Seed: {self.server_seed}")
                    self.nonce+=1
                    self.balance += reward_value
                else:
                    print(f"Out of Balance...")
                    break
            print(f"Final Balance: {self.balance}")
    
    def game(self) -> None:
        while self.running:
                self.show_starting_banner()
                option = input("> ")
                match option:
                    case "a":
                        print("-- SHOP --")
                        print("a) buy flag : $10000")
                        print("b) exit")
                        choice = input("> ")
                        if (choice == "a") and (self.balance >= 10000):
                            with open("./flag", 'r') as f:
                                print(f.read())
                    case "b":
                        try:
                            self.gamble_game()
                        except Exception as e:
                            print("Error: ", e) 
                    case "c":
                        print(f"current seed: {self.client_seed}")
                        self.client_seed = input("Set custom seed: ")
                    case "d":
                        self.running = False
                        break
                    case _:
                        print("unknown command..")

dicegame : DiceGame = DiceGame()
dicegame.game()
