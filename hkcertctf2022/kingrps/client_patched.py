import hashlib
import os
import base64
import subprocess

import game_pb2

ROUNDS = 128

move_map = {
    'rock': game_pb2.Move.ROCK,
    'paper': game_pb2.Move.PAPER,
    'scissors': game_pb2.Move.SCISSORS
}

class RPSServer:
    def __init__(self):
        """Start the chall.py process and interact via stdin/stdout."""
        self.proc = subprocess.Popen(
            ["python3", "chall.py"],
            stdin=subprocess.PIPE,
            stdout=subprocess.PIPE,
            stderr=subprocess.DEVNULL, 
            text=False 
        )

    def __send(self, message):
        """Send a base64-encoded message via stdin."""
        encoded_message = base64.b64encode(message.SerializeToString()) + b'\n'
        self.proc.stdin.write(encoded_message)
        self.proc.stdin.flush()

    def __recv(self, msg_class):
        """Read and decode a base64-encoded message from stdout."""
        data = self.proc.stdout.readline().strip() 
        msg = msg_class()
        msg.ParseFromString(base64.b64decode(data))
        return msg

    def play(self, move_client):
        """Execute a single round."""
        server_init_message = self.__recv(game_pb2.ServerRoundInitMessage)
        nonce_server = server_init_message.nonce
        nonce_client = os.urandom(16)

        client_move_message = game_pb2.ClientMoveMessage(
            nonce_client=nonce_client,
            nonce_server=nonce_server,
            move=move_client
        )
        move_hash = hashlib.md5(client_move_message.SerializeToString()).digest()
        client_round_init_message = game_pb2.ClientRoundInitMessage(
            nonce=nonce_client,
            hash=move_hash
        )

        self.__send(client_round_init_message)
        server_move_message = self.__recv(game_pb2.ServerMoveMessage)
        self.__send(client_move_message)
        server_round_final_message = self.__recv(game_pb2.ServerRoundFinalMessage)
        move_server = game_pb2.Move.Name(server_move_message.move).lower()
        winner = game_pb2.Player.Name(server_round_final_message.winner).lower()

        return move_server, winner

    def final_message(self):
        """Retrieve the final message after all rounds."""
        server_final_message = self.__recv(game_pb2.ServerFinalMessage)
        return server_final_message.message

def main():
    srv = RPSServer()

    for _ in range(ROUNDS):
        move_client = None
        while move_client not in ['rock', 'paper', 'scissors']:
            move_client = input('> ').strip().lower()
        move_server, winner = srv.play(move_map[move_client])

        if winner != 'tie':
            print(f'You played {move_client}, server played {move_server}. Winner: {winner}.')
        else:
            print(f'You played {move_client}, server played {move_server}. Its a tie.')

    print(srv.final_message())

if __name__ == '__main__':
    main()

