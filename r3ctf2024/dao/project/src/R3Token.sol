// SPDX-License-Identifier: UNLICENSED
pragma solidity ^0.8.20;

import "./IERC20.sol";

contract R3Token is IERC20 {
    event Transfer(address indexed from, address indexed to, uint256 value);
    event Approval(
        address indexed owner, address indexed spender, uint256 value
    );

    uint256 public totalSupply;
    mapping(address => uint256) public balanceOf;
    mapping(address => mapping(address => uint256)) public allowance;
    string public name;
    string public symbol;
    uint8 public decimals;
    address public owner;

    struct Checkpoint {
        uint256 fromBlock;
        uint256 value;
    }
    mapping(address => Checkpoint[]) public balanceCheckpoints;

    modifier onlyOwner() {
        require(msg.sender == owner, "R3Token: not owner");
        _;
    }

    function transferOwnership(address newOwner) external onlyOwner {
        owner = newOwner;
    }

    constructor() {
        name = "R3Token";
        symbol = "R3";
        decimals = 18;
        owner = msg.sender;

        //mint
        totalSupply = 1_000_000 ether;
        balanceOf[msg.sender] = totalSupply;
    }

    function _transfer(address from, address to, uint256 amount) internal {
        balanceOf[from] -= amount;
        balanceOf[to] += amount;
        balanceCheckpoints[from].push(Checkpoint(block.number, balanceOf[from]));
        balanceCheckpoints[to].push(Checkpoint(block.number, balanceOf[to]));
        emit Transfer(from, to, amount);
    }

    function transfer(address recipient, uint256 amount)
        external
        returns (bool)
    {
        _transfer(msg.sender, recipient, amount);
        return true;
    }

    function approve(address spender, uint256 amount) external returns (bool) {
        allowance[msg.sender][spender] = amount;
        emit Approval(msg.sender, spender, amount);
        return true;
    }

    function transferFrom(address sender, address recipient, uint256 amount)
        external
        returns (bool)
    {
        allowance[sender][msg.sender] -= amount;
        _transfer(sender, recipient, amount);
        return true;
    }

    function getPriorVotes(address account, uint256 blockNumber)
        external
        view
        returns (uint256)
    {
        Checkpoint[] storage checkpoints = balanceCheckpoints[account];
        if (checkpoints.length == 0 || blockNumber < checkpoints[0].fromBlock) {
            return 0;
        }
        if (blockNumber >= checkpoints[checkpoints.length - 1].fromBlock) {
            return checkpoints[checkpoints.length - 1].value;
        }
        uint256 min = 0;
        uint256 max = checkpoints.length - 1;
        while (max > min) {
            uint256 mid = (max + min + 1) / 2;
            if (checkpoints[mid].fromBlock == blockNumber) {
                return checkpoints[mid].value;
            }
            if (checkpoints[mid].fromBlock < blockNumber) {
                min = mid;
            } else {
                max = mid - 1;
            }
        }
        return checkpoints[min].value;
    }
}