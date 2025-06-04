<div class="matrix-background" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1;"></div>
<canvas id="matrixCanvas" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1;"></canvas>

<style>
    body {
      background: #000000;
      color: #ffffff;
    }
    .fancy-header {
      font-size: 1.5rem;
      color: rgb(248, 199, 68);
      text-shadow: 0 0 10px rgb(248, 199, 68);
    }
</style>

<script>
  (function () {
    const canvas = document.getElementById('matrixCanvas');
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    const characters = "アァイィウヴエェオカガキギクグケゲコゴサザシジスズセゼソゾタダチッヂヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモヤユヨラリルレロワヲンABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    const fontSize = 16;
    const columns = canvas.width / fontSize;
    const drops = Array(Math.floor(columns)).fill(1);

    function draw() {
      ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = "#0F0";
      ctx.font = fontSize + "px monospace";

      for (let i = 0; i < drops.length; i++) {
        const text = characters.charAt(Math.floor(Math.random() * characters.length));
        ctx.fillText(text, i * fontSize, drops[i] * fontSize);
        if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
          drops[i] = 0;
        }

        drops[i]++;
      }
    }

    setInterval(draw, 33);
  })();
</script>

<h1 class="fancy-header"> Welcome to the CTF Archive! This is a comprehensive collection of challenges from past Capture The Flag competitions. </h1>

<p> This is an open source archive and we welcome contributions.

The link to the github repo: https://github.com/pwncollege/ctf-archive

These modules serve as a resource for cybersecurity enthusiasts, providing easy access to preserved challenges that have been featured in previous CTF events. Whether you are looking to hone your skills, prepare for upcoming competitions, or simply explore the rich history of CTF challenges, this archive offers a robust platform for your endeavors.

Explore the CTF Archive, where you can access, analyze, and learn from some of the most intriguing and educational challenges the cybersecurity community has encountered. 

Happy learning and hacking!

----
**NOTE: This module is an archive of amazing work done by heroes of the CTF community, not an active competitive event!** Credit for the challenges goes to their individual authors, which we will strive to properly attribute wherever possible. This dojo's "scoreboard" is meant for you to track your own progress, not as a comparison against others.

----
**Other CTF preservation efforts!**
- The [CryptoHack CTF Archive](https://cryptohack.org/challenges/ctf-archive/) maintains runnable cryptography challenges from past CTFs!
- [Sajjadium's CTF Archives](https://github.com/sajjadium/ctf-archives) and [r3kapig's Notion](https://r3kapig-not1on.notion.site/Index-docs-format-09007cb92ef649838d8057a64f0d99dc) preserve challenge files from prior CTFs.
- You can play a lot of OOO's DEF CON CTF challenges at [archive.ooo](https://archive.ooo/).
- [CTFtime](https://ctftime.org) saves the metadata of the world's CTFs!

</p>
