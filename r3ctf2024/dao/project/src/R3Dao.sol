// SPDX-License-Identifier: UNLICENSED
pragma solidity ^0.8.20;

import "./R3Token.sol";

contract R3Dao {
    R3Token public token;
    
    struct Proposal {
        address proposer;
        address recipient;
        bytes32 payloadHash;
        uint256 votes;
        uint256 beginBlock;
        mapping(address => bool) voted;
        bool executed;
    }
    Proposal[] public proposals;

    constructor(R3Token _token) {
        token = _token;
    }

    function propose(address recipient, bytes32 payloadHash) external returns (uint256) {
        uint256 proposalIndex = proposals.length;
        proposals.push();
        Proposal storage proposal = proposals[proposalIndex];
        proposal.proposer = msg.sender;
        proposal.recipient = recipient;
        proposal.payloadHash = payloadHash;
        proposal.beginBlock = block.number - 1;
        return proposalIndex;
    }

    function vote(uint256 proposalIndex) external {
        Proposal storage proposal = proposals[proposalIndex];
        require(!proposal.executed, "R3Dao: proposal executed");
        require(!proposal.voted[msg.sender], "R3Dao: already voted");
        uint power = token.getPriorVotes(msg.sender, proposal.beginBlock);
        require(power > 0, "R3Dao: no voting power");
        proposal.voted[msg.sender] = true;
        proposal.votes += power;
    }

    function execute(uint256 proposalIndex, bytes calldata payload) external {
        Proposal storage proposal = proposals[proposalIndex];
        require(!proposal.executed, "R3Dao: proposal executed");
        require(proposal.votes > token.totalSupply() / 2, "R3Dao: insufficient votes");
        proposal.executed = true;
        (bool success, ) = proposal.recipient.delegatecall(payload);
        require(success, "R3Dao: call failed");
    }
}