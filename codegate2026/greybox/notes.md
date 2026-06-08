# Greybox Notes

- `prob` is a 64-byte checker that loads `./target` and executes an 8-iteration ARX transform over 16 little-endian `uint32` input words.
- The transform is a double-round built from quarterrounds with rotate constants `7, 9, 13, 18`.
- The quarterround schedule is:
  - `(0, 4, 8, 12)`
  - `(1, 5, 9, 13)`
  - `(2, 6, 10, 14)`
  - `(3, 7, 11, 15)`
  - `(0, 5, 10, 15)`
  - `(1, 6, 11, 12)`
  - `(2, 7, 8, 13)`
  - `(3, 4, 9, 14)`
- After 8 double-rounds, the state is compared against 16 embedded constants. Inverting those rounds yields the accepted 64-byte input.
- Local verification:
  - `printf '4h!_C0ngr47u147i0ns!_L37_m3_kn0w_why_7his_gr3y_b0x_d03s_n07_3nd!' | ./prob`
  - Output: `Sucess!` and `Flag is codegate2026{4h!_C0ngr47u147i0ns!_L37_m3_kn0w_why_7his_gr3y_b0x_d03s_n07_3nd!}`
