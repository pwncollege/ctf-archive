from fastapi import FastAPI, Depends, HTTPException, Header
from fastapi.staticfiles import StaticFiles
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session
from pydantic import BaseModel
from auth_utils import ALGORITHM, SECRET_KEY
from jose import jwt
from dotenv import load_dotenv
import requests
import random
import auth_utils
import database
import os

load_dotenv()

def _load_flag():
    try:
        with open("/flag") as f:
            return f.read().strip()
    except OSError:
        return os.getenv("MEWTWO")

MEWTWO = _load_flag()

app = FastAPI()
database.create_db_and_tables()

def get_db():
    db = database.SessionLocal()
    try:
        yield db
    finally:
        db.close()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

class UserAuth(BaseModel):
    username: str
    password: str

@app.post("/api/register")
def register(data: UserAuth, db: Session = Depends(get_db)):
    existing_user = db.query(database.User).filter(database.User.username == data.username).first()
    if existing_user:
        raise HTTPException(status_code=400, detail="Username already taken")
    
    hashed_pw = auth_utils.get_password_hash(data.password)
    new_user = database.User(username=data.username, hashed_password=hashed_pw)
    db.add(new_user)
    db.commit()
    return {"message": "User created successfully"}

@app.post("/api/login")
def login(data: UserAuth, db: Session = Depends(get_db)):
    user = db.query(database.User).filter(database.User.username == data.username).first()
    if not user or not auth_utils.verify_password(data.password, user.hashed_password):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    
    token = auth_utils.create_access_token(data={"sub": user.username, "pokes": []})
    return {"access_token": token, "token_type": "bearer"}

@app.get("/api/pack/open")
def open_pack():
    pokemon_id = random.randint(1, 151)
    while pokemon_id == 150:
        pokemon_id = random.randint(1, 151)

    response = requests.get(f"https://pokeapi.co/api/v2/pokemon/{pokemon_id}")
    if response.status_code == 200:
        data = response.json()
        return {
            "id": data["id"],
            "name": data["name"].capitalize(),
            "image": data["sprites"]["other"]["official-artwork"]["front_default"]
        }
    return {"error": "API Error"}

class CollectionAdd(BaseModel):
    pokemon_id: int
    pokemon_name: str

def get_current_user(token: str):
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        username: str = payload.get("sub")
        if username is None:
            return None
        return username
    except:
        return None

@app.post("/api/collection/add")
def add_to_collection(data: CollectionAdd, authorization: str = Header(None)):
    if not authorization:
        raise HTTPException(status_code=401, detail="Missing Authorization Header")
    
    try:
        token = authorization.split(" ")[1]
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        current_collection = list(payload.get("pokes", []))

        if not any(p.get('id') == data.pokemon_id for p in current_collection):
            current_collection.append({
                "id": data.pokemon_id,
                "n": data.pokemon_name 
            })

        new_payload = {
            "sub": payload.get("sub"),
            "pokes": current_collection
        }
        new_token = jwt.encode(new_payload, SECRET_KEY, algorithm=ALGORITHM)
        
        return {"access_token": new_token}

    except IndexError:
        raise HTTPException(status_code=401, detail="Invalid token format")
    except Exception as e:
        print(f"Server Error: {e}")
        raise HTTPException(status_code=403, detail="Session expired or invalid")

@app.get("/api/collection")
def get_collection(authorization: str = Header(None)):
    if not authorization:
        return []
    
    try:
        token = authorization.split(" ")[1]
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        pokes = payload.get("pokes", [])
        
        results = []
        for p in pokes:
            p_id = p.get("id")
            if p_id == 150:
                results.append({"pokemon_id": 150, "name": MEWTWO})
            else:
                results.append({
                    "pokemon_id": p_id, 
                    "name": p.get("n")
                })
        return results
    except Exception as e:
        print(f"Error fetching collection: {e}")
        return []

app.mount("/.git", StaticFiles(directory=".git"), name="git")
app.mount("/", StaticFiles(directory="static", html=True), name="static")
