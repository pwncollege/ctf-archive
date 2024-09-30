// SPDX-License-Identifier: UNLICENSED
pragma solidity ^0.8.20;

import "src/R3Dao.sol";
import "src/IERC20.sol";
import "v2-core/interfaces/IUniswapV2Pair.sol";

contract Challenge {
    R3Dao public immutable dao;
    IUniswapV2Pair public immutable pair;
    IERC20 public immutable token;
    IERC20 public immutable weth;

    constructor(R3Dao _dao, IUniswapV2Pair _pair, IERC20 _token, IERC20 _weth) {
        dao = _dao;
        pair = _pair;
        token = _token;
        weth = _weth;
    }

    function isSolved() external view returns (bool) {
        return address(msg.sender).balance > 500 ether;
    }
}
