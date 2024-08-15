from random import shuffle

flag = open("/flag", "rt").read()

cases = []

print(
"""
module fsmir (
	input              clk   ,
	input        [7:0] di    ,
	output logic [7:0] c     ,
	output             solved
);

	initial c = 8'b0;

	assign solved = c == 8'd59;

	always @(posedge clk) begin
		c <= 8'b0;

		case (c)
"""
)

for i in range(len(flag)):
	c = "			8'{}: if((di ^ c) == 8'{}) c <= 8'{};".format(
		bin(i)[1:],
		bin(ord(flag[i]) ^ i)[1:],
		bin(i+1)[1:]
		)
	cases.append(c)

shuffle(cases)

print("\n".join(cases))

print(
"""
			default   : c <= 8'b0;
		endcase
	end
endmodule

"""
	)
