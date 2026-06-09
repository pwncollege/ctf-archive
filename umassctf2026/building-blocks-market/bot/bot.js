const http = require('http');
const querystring = require('querystring');
const puppeteer = require('puppeteer');

const BACKEND_URL = process.env.BACKEND_URL || 'http://backend:5001';
const ADMIN_USERNAME = process.env.ADMIN_USERNAME;
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD;
const BOT_PORT = process.env.BOT_PORT || 3000;

let adminSession = null;

async function loginAdmin() {
    return new Promise((resolve) => {
        const loginData = querystring.stringify({
            'username': ADMIN_USERNAME,
            'password': ADMIN_PASSWORD
        });
        
        const url = new URL(BACKEND_URL);
        const options = {
            hostname: url.hostname,
            port: url.port || 5001,
            path: '/login',
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Content-Length': Buffer.byteLength(loginData)
            }
        };
        
        const req = http.request(options, (res) => {
            let data = '';
            const setCookieHeaders = res.headers['set-cookie'] || [];
            
            let sessionCookie = null;
            for (const cookieHeader of setCookieHeaders) {
                if (cookieHeader.includes('session=')) {
                    const match = cookieHeader.match(/session=([^;]+)/);
                    if (match) {
                        sessionCookie = `session=${match[1]}`;
                        break;
                    }
                }
            }
            
            res.on('data', chunk => { data += chunk; });
            res.on('end', () => {
                if (sessionCookie) {
                    adminSession = sessionCookie;
                    resolve(true);
                } else {
                    resolve(false);
                }
            });
        });
        
        req.on('error', (error) => {
            resolve(false);
        });
        
        req.setTimeout(5000, () => {
            req.destroy();
            resolve(false);
        });
        
        req.write(loginData);
        req.end();
    });
}


async function ensureAdminSession() {
    if (adminSession) {
        return true;
    }

    const loggedIn = await loginAdmin();
    if (!loggedIn) {
        return false;
    }

    return true;
}


async function visitURL(targetUrl) {
    try {
        const browser = await puppeteer.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-features=SameSiteByDefaultCookies,CookiesWithoutSameSiteMustBeSecure']
        });
        const page = await browser.newPage();
        
        if (adminSession) {
            await page.setCookie({
                name: 'session',
                value: adminSession.split('=')[1],
                url: BACKEND_URL,
                httpOnly: true,
                secure: false
            });
        }

        const response = await page.goto(targetUrl, {
            waitUntil: 'networkidle2',
            timeout: 30000
        });

    await new Promise(resolve => setTimeout(resolve, 5000));
        await page.close();
        await browser.close();
        return true;
    } catch (e) {
        return false;
    }
}

function startBotServer() {
    const server = http.createServer(async (req, res) => {
        if (req.method === 'POST' && req.url === '/visit') {
            let body = '';
            
            req.on('data', chunk => {
                body += chunk.toString();
            });
            
            req.on('end', async () => {
                try {
                    const data = JSON.parse(body);
                    const targetUrl = data.url;
                    
                    if (!targetUrl) {
                        res.writeHead(400, { 'Content-Type': 'application/json' });
                        res.end(JSON.stringify({ error: 'URL is required' }));
                        return;
                    }

                    const hasSession = await ensureAdminSession();
                    if (!hasSession) {
                        res.writeHead(500, { 'Content-Type': 'application/json' });
                        res.end(JSON.stringify({ error: 'Failed to authenticate as admin' }));
                        return;
                    }

                    visitURL(targetUrl).then(success => {
                    });

                    res.writeHead(202, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ 
                        success: true, 
                        message: 'Visit request accepted',
                        url: targetUrl
                    }));
                } catch (e) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: e.message }));
                }
            });
            return;
        }
        res.writeHead(404, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: 'Not found' }));
    });
    
    server.listen(BOT_PORT, () => {
    });
}

if (require.main === module) {
    (async () => {

        startBotServer();

        process.on('SIGINT', () => {
            process.exit(0);
        });
    })().catch(err => {
        process.exit(1);
    });
}