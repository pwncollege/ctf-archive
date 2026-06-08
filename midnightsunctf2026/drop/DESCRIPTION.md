The program fills a fixed RX-mapped region with a `rand()` stream seeded from the current time, reads a short payload into a second page, then pivots the stack into your input and returns. Find ROP gadgets in the deterministic random region and drop your own code.

---
**Author:** Midnight Sun CTF 2026 Team
