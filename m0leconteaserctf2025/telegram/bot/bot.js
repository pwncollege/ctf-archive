// puppeteer bot with url in input
const express = require('express');
const puppeteer = require('puppeteer');

const app = express();
app.use(express.json());

const CHALLENGE_HOST = process.env.CHALLENGE_HOST || 'https://challenge:5000';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'REDACTED';
const LOGIN_URL = `${CHALLENGE_HOST}/index?password=${encodeURIComponent(ADMIN_PASSWORD)}`;

async function bot(chat_id) {

  
    const browser = await puppeteer.launch(
        {
            executablePath : '/usr/bin/chromium-browser',
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--ignore-certificate-errors'
            ]
        }
    );

    const page = await browser.newPage();
    
    
    await page.goto(LOGIN_URL);

    
    await page.waitForSelector('#chat_id');
    await page.type('#chat_id', chat_id.toString());
    await page.click('button[type="submit"]');
    
    await page.waitForSelector('a', {timeout: 5000});
    const url = await page.evaluate(() => document.querySelector('a').href);
    console.log(url);
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        await browser.close();

        return false;
    }

    

    await page.click('a');
    await (new Promise(resolve => setTimeout(resolve, 20000)));
    
    await browser.close();
    return true;
   

}

app.post('/bot', async (req, res) => {

    let chat_id = req.body.chat_id;

    if (!chat_id) {
        res.status(400).send('chat_id is required');
        return;
    }

    // check int
    chat_id = parseInt(chat_id);
    if (isNaN(chat_id)) {
        res.status(400).send('chat_id must be an integer');
        return;
    }
    console.log("Loading bot with chat_id: ", chat_id);

    try{
        await bot(chat_id);
    } catch (error) {
       res.send('Bot error');

    }
    

    res.send('Bot finish');
});


app.listen(3000, () => {
    console.log('Server running on port 3000');
});