const API_URL = '/api/pack/open';
let currentPokemon = null;

async function navigateTo(page) {
    const canvas = document.getElementById('app-canvas');
    if (!canvas) return;

    canvas.innerHTML = '<div style="color:white; text-align:center;">Loading PokeWorld...</div>';

    try {
        const response = await fetch(`./pages/${page}.html?t=${new Date().getTime()}`);
        
        if (!response.ok) throw new Error(`Page ${page} not found (Status: ${response.status})`);

        const html = await response.text();
        
        if (html.length === 0) {
            console.error("Warning: Received 0 bytes from server for page:", page);
        }

        canvas.innerHTML = html;
        
        updateAuthUI();

        if (page === 'game') console.log("Game Loaded");

        if (page === 'collection') {
            loadUserCollection();
        }

    } catch (error) {
        console.error("Routing Error:", error);
        canvas.innerHTML = `<div style="color:white; text-align:center;">
            <h2>Gotta catch 'em all... but not this page.</h2>
            <p>${error.message}</p>
        </div>`;
    }
}

function updateAuthUI() {
    const authUI = document.getElementById('auth-ui');
    if (!authUI) return;

    const username = localStorage.getItem('username');

    if (username) {
        authUI.innerHTML = `
            <span class="user-display">Trainer: <b>${username}</b></span>
            <button onclick="navigateTo('collection')">My Collection</button>
            <button onclick="logout()">Logout</button>
        `;
    } else {
        authUI.innerHTML = `
            <button class="btn-login" onclick="navigateTo('login')" style="cursor:pointer; margin-right:10px;">Login</button>
            <button class="btn-register" onclick="navigateTo('register')" style="cursor:pointer;">Register</button>
        `;
    }
}

async function getNewPack() {
    const card = document.getElementById('card');
    const img = document.getElementById('pokeImg');
    const hint = document.getElementById('hint');
    const btn = document.getElementById('open-btn');
    
    if (!card || !img) return;

    btn.disabled = true;
    card.classList.remove('revealed'); 
    img.classList.remove('loaded');
    hint.innerText = "Searching Gen I...";

    try {
        const response = await fetch(API_URL);
        const data = await response.json();
        currentPokemon = data; 
        img.src = data.image;

        img.onload = () => {
            document.getElementById('pokeName').innerText = data.name; 
            card.classList.add('active');
            img.classList.add('loaded');
            hint.innerText = "CLICK TO OPEN CARD!";
            btn.disabled = false;
        };
    } catch (error) {
        console.error("Fetch error:", error);
        hint.innerText = "Connection Error!";
        btn.disabled = false;
    }
}

function revealCard() {
    const card = document.getElementById('card');
    const hint = document.getElementById('hint');
    const token = localStorage.getItem('token');
    
    if (card && currentPokemon && card.classList.contains('active') && !card.classList.contains('revealed')) {
        card.classList.add('revealed');
        hint.innerText = `YOU CAUGHT ${currentPokemon.name}!`;

        if (token) {
            saveToCollection(currentPokemon.id, currentPokemon.name);
        } else {
            hint.innerText += " (Log in to save!)";
        }
    }
}

async function saveToCollection(pokemonId, pokemonName) {
    const token = localStorage.getItem('token');
    
    try {
        const response = await fetch('/api/collection/add', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}` 
            },
            body: JSON.stringify({ 
                pokemon_id: pokemonId,
                pokemon_name: pokemonName 
            })
        });

        if (response.ok) {
            const data = await response.json();
            if (data.access_token) {
                localStorage.setItem('token', data.access_token);
                console.log(`Saved ${pokemonName} to JWT!`);
            }
        } else {
            console.error("Failed to add to collection. Status:", response.status);
        }
    } catch (error) {
        console.error("Save Error:", error);
    }
}

async function handleRegister() {
    const user = document.getElementById('reg-user').value;
    const pass = document.getElementById('reg-pass').value;

    if (!user || !pass) {
        alert("Please enter both a username and password.");
        return;
    }

    try {
        const response = await fetch('/api/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: user, password: pass }) 
        });

        const data = await response.json();

        if (response.ok) {
            alert("Success! Account created for Trainer: " + user);
            navigateTo('login');
        } else {
            alert("Registration failed: " + (data.detail || "Unknown error"));
        }
    } catch (error) {
        console.error("Network Error:", error);
        alert("Could not connect to the server.");
    }
}

async function handleLogin() {
    const user = document.getElementById('login-user').value;
    const pass = document.getElementById('login-pass').value;

    const response = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: user, password: pass })
    });

    if (response.ok) {
        const data = await response.json();
        localStorage.setItem('token', data.access_token);
        localStorage.setItem('username', user);
        updateAuthUI();
        navigateTo('home');
    } else {
        alert("Invalid login");
    }
}

function logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('username');
    updateAuthUI();
    navigateTo('home');
}

document.addEventListener('DOMContentLoaded', () => {
    updateAuthUI();
    navigateTo('home');
});

async function loadCollection() {
    const grid = document.getElementById('collection-grid');
    const token = localStorage.getItem('token');

    const response = await fetch('/api/collection', {
        headers: { 'Authorization': `Bearer ${token}` }
    });

    const items = await response.json();
    grid.innerHTML = '';

    items.forEach(item => {
        grid.innerHTML += `
            <div class="mini-card">
                <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/${item.pokemon_id}.png">
                <p>No. ${item.pokemon_id}</p>
            </div>
        `;
    });
}

async function loadUserCollection() {
    const grid = document.getElementById('collection-grid');
    const token = localStorage.getItem('token');
    if (!grid) return;

    let serverData = [];

    try {
        const response = await fetch('/api/collection', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        serverData = await response.json();
    } catch (err) {
        console.error("Fetch error:", err);
    }

    grid.innerHTML = '';

    for (let i = 1; i <= 151; i++) {
        const caughtData = serverData.find(p => p.pokemon_id === i);
        
        const isCollected = !!caughtData;
        const displayName = isCollected ? caughtData.name : "???";

        const imgClass = isCollected ? "" : "locked-pokemon";
        const cardClass = isCollected ? "mini-card collected" : "mini-card";
        const flagClass = (i === 150 && isCollected) ? "mewtwo-flag" : "";
        
        const imgSrc = `https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/${i}.png`;

        grid.innerHTML += `
            <div class="${cardClass} ${flagClass}">
                <span class="poke-number">#${String(i).padStart(3, '0')}</span>
                <img src="${imgSrc}" class="${imgClass}" alt="pokemon">
                <p class="poke-name">${displayName}</p>
            </div>
        `;
    }
}