#!/usr/bin/exec-suid -- /usr/bin/python3

import random

flag = open("/flag", "rt").read()

max_id = 512-1
rand_start = len(flag)+1

alphabet = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!#$%&()*+,-./:;<=>?@[]^_`{|}~"

def saturate_sigma(sig):
	return dict({x: random.randint(rand_start,max_id) for x in alphabet}, **sig)

class Node(object):
	next_id = 0
	def __init__(self, sig={}):
		self.sigma = saturate_sigma(sig)
		self.id = Node.next_id
		Node.next_id += 1

nodes = []

# Initial state
nodes.append(Node({ flag[0] : 1 }))

# Create the flag chain
for c in flag[1:]:
	nodes.append(Node({ c : Node.next_id+1 }))

# Add random nodes, with possible cycles but never connected to "flag path"
while len(nodes) <= max_id:
	nodes.append(Node())

hlp = list(range(1,max_id+1))
random.shuffle(hlp)
scramble = [0] + hlp

def serialise(node):
	s1 = "			9'{} : case(di)\n".format(bin(scramble[node.id])[1:])
	s2 = "\n".join(["				8'{}: c <= 9'{};".format(
			bin(ord(x))[1:], bin(scramble[y])[1:])
		for x,y in node.sigma.items()])
	s3 = "\n				default: c <= 9'b0;"
	s4 = "\n			endcase"
	print(s1+s2+s3+s4)


print(
"""module fsmir2 (
	input        clk   ,
	input  [7:0] di    ,
	output       solved
);

	logic [8:0] c = 9'b0;

	assign solved = c == 9'{};

	always @(posedge clk) begin
		c <= 9'b0;

		case (c)
""".format(bin(scramble[len(flag)])[1:])

	)

for n in nodes:
	serialise(n)


print(
"""
		endcase
	end
endmodule
"""
	)
