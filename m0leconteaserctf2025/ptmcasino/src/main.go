package main

import (
	"context"
	"crypto/rand"
	"database/sql"
	"fmt"
	"html/template"
	"log"
	"math/big"
	"net/http"
	"os"
	"strconv"

	"github.com/go-chi/chi/v5"
	"github.com/gorilla/sessions"
	_ "github.com/mattn/go-sqlite3"
)

var sessSecret = os.Getenv("SECRET_KEY")

var (
	db          *sql.DB
	store       = sessions.NewCookieStore([]byte(sessSecret))
	sessionName = "session"
	templates   *template.Template
)

func main() {
	fmt.Println("Secret key: ", sessSecret)
	var err error
	db, err = sql.Open("sqlite3", "./roulette.db")
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	createTables()

	templates = template.Must(template.ParseFiles("templates/base.html", "templates/roulette.html", "templates/home.html", "templates/login.html", "templates/flag.html"))

	router := chi.NewRouter()

	router.Get("/", homeHandler)
	router.Get("/login", loginHandler)
	router.Post("/register", registerHandlerSubmit)
	router.Post("/login", loginHandlerSubmit)
	router.With(authMiddleware).Get("/play", playHandler)
	router.With(authMiddleware).Post("/play", playHandlerSubmit)
	router.With(authMiddleware).Get("/flag", flagHandler)

	http.ListenAndServe(":3000", router)
}

func createTables() {
	createUserTable := `CREATE TABLE IF NOT EXISTS users (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		username TEXT NOT NULL UNIQUE,
		password TEXT NOT NULL
	);`

	createWalletTable := `CREATE TABLE IF NOT EXISTS wallet (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		user_id INTEGER,
		balance INTEGER,
		FOREIGN KEY(user_id) REFERENCES users(id)
	);`

	createGameTable := `CREATE TABLE IF NOT EXISTS game_history (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		user_id INTEGER,
		result TEXT,
		amount INTEGER,
		win BOOLEAN,
		bet TEXT,
		FOREIGN KEY(user_id) REFERENCES users(id)
	);`

	if _, err := db.Exec(createUserTable); err != nil {
		log.Fatal(err)
	}

	if _, err := db.Exec(createGameTable); err != nil {
		log.Fatal(err)
	}

	if _, err := db.Exec(createWalletTable); err != nil {
		log.Fatal(err)
	}
}

type User struct {
	Username string `json:"username"`
	Password string `json:"password"`
}

type contextKey string

const (
	usernameKey contextKey = "username"
	balanceKey  contextKey = "balance"
	useridKey   contextKey = "user_id"
)

func loginHandler(w http.ResponseWriter, r *http.Request) {
	msg := r.URL.Query().Get("msg")
	err := r.URL.Query().Get("err")

	renderTemplate(w, "login.html", map[string]interface{}{
		"Msg": msg,
		"Err": err,
	})
}

func registerHandlerSubmit(w http.ResponseWriter, r *http.Request) {
	var user User
	err := r.ParseForm()

	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	user.Username = r.FormValue("username")
	user.Password = r.FormValue("password")

	if user.Username == "" || user.Password == "" {
		http.Redirect(w, r, "/login?err=Username+or+password+cannot+be+empty", http.StatusSeeOther)
		return
	}

	if len(user.Username) < 5 || len(user.Password) < 5 {
		http.Redirect(w, r, "/login?err=Username+or+password+must+be+at+least+5+characters", http.StatusSeeOther)
		return
	}

	result, err := db.Exec("INSERT INTO users (username, password) VALUES (?, ?)", user.Username, user.Password)
	if err != nil {
		http.Redirect(w, r, "/login?err=Username+already+exists", http.StatusSeeOther)
		return
	}

	userID, err := result.LastInsertId()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	_, err = db.Exec("INSERT INTO wallet (user_id, balance) VALUES (?, ?)", userID, 100)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	http.Redirect(w, r, "/login?msg=New+user+created", http.StatusSeeOther)
}

func loginHandlerSubmit(w http.ResponseWriter, r *http.Request) {
	var user User
	err := r.ParseForm()

	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	user.Username = r.FormValue("username")
	user.Password = r.FormValue("password")

	var id int
	err = db.QueryRow("SELECT id FROM users WHERE username = ? AND password = ?", user.Username, user.Password).Scan(&id)
	if err != nil {
		http.Redirect(w, r, "/login?err=Invalid+username+or+password", http.StatusSeeOther)
		return
	}

	session, _ := store.Get(r, sessionName)
	session.Values["user_id"] = id
	session.Save(r, w)

	http.Redirect(w, r, "/", http.StatusSeeOther)
}

func getUserData(userID int) (username string, balance int, err error) {
	err = db.QueryRow("SELECT username FROM users WHERE id = ?", userID).Scan(&username)
	if err != nil {
		return "", 0, err
	}

	err = db.QueryRow("SELECT balance FROM wallet WHERE user_id = ?", userID).Scan(&balance)
	if err != nil {
		return "", 0, err
	}

	return username, balance, nil
}

func homeHandler(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, sessionName)
	if session.Values["user_id"] != nil {
		username, balance, err := getUserData(session.Values["user_id"].(int))
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		renderTemplate(w, "home.html", map[string]interface{}{
			"Username": username,
			"Balance":  balance,
		})
		return
	}

	renderTemplate(w, "home.html", map[string]interface{}{})
}

func authMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		session, _ := store.Get(r, sessionName)

		if session.Values["user_id"] == nil {
			http.Error(w, "Forbidden", http.StatusForbidden)
			return
		}

		username, balance, err := getUserData(session.Values["user_id"].(int))
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		ctx := r.Context()
		ctx = context.WithValue(ctx, usernameKey, username)
		ctx = context.WithValue(ctx, balanceKey, balance)
		ctx = context.WithValue(ctx, useridKey, session.Values["user_id"])

		next.ServeHTTP(w, r.WithContext(ctx))
	})
}

func playHandler(w http.ResponseWriter, r *http.Request) {
	renderTemplate(w, "roulette.html", map[string]interface{}{
		"Username": r.Context().Value(usernameKey),
		"Balance":  r.Context().Value(balanceKey),
	})
}

func playHandlerSubmit(w http.ResponseWriter, r *http.Request) {
	err := r.ParseForm()

	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	bet := r.FormValue("bet")
	amountStr := r.FormValue("amount")

	amount, err := strconv.Atoi(amountStr)
	if err != nil {
		http.Redirect(w, r, "/play?err=Invalid+amount", http.StatusSeeOther)
		return
	}

	if amount <= 0 {
		http.Redirect(w, r, "/play?err=Invalid+amount", http.StatusSeeOther)
		return
	}

	balance := r.Context().Value(balanceKey).(int)

	if amount > balance {
		http.Redirect(w, r, "/play?err=Insufficient+balance", http.StatusSeeOther)
		return
	}

	numberb, err := rand.Int(rand.Reader, big.NewInt(37))

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	number := int(numberb.Int64())
	result := strconv.Itoa(number)

	win := false
	balance -= amount

	if bet == result {
		win = true
		amount *= 36
	}

	color := "GREEN"

	if number == 1 || number == 3 || number == 5 || number == 7 || number == 9 || number == 12 || number == 14 || number == 16 || number == 18 || number == 19 || number == 21 || number == 23 || number == 25 || number == 27 || number == 30 || number == 32 || number == 34 || number == 36 {
		color = "RED"
	} else if number == 2 || number == 4 || number == 6 || number == 8 || number == 10 || number == 11 || number == 13 || number == 15 || number == 17 || number == 20 || number == 22 || number == 24 || number == 26 || number == 28 || number == 29 || number == 31 || number == 33 || number == 35 {
		color = "BLACK"
	}

	if (bet == "RED" && color == "RED") || (bet == "BLACK" && color == "BLACK") {
		win = true
		amount *= 2
	}

	parity := ""

	if number%2 == 0 && number != 0 {
		parity = "EVEN"
	} else if number%2 != 0 {
		parity = "ODD"
	}

	if (bet == "EVEN" && parity == "EVEN") || (bet == "ODD" && parity == "ODD") {
		win = true
		amount *= 2
	}

	if (bet == "1-18" && number >= 1 && number <= 18) || (bet == "19-36" && number >= 19 && number <= 36) {
		win = true
		amount *= 2
	}

	if win {
		balance += amount
	}

	_, err = db.Exec("UPDATE wallet SET balance = ? WHERE user_id = ?", balance, r.Context().Value(useridKey))
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	_, err = db.Exec("INSERT INTO game_history (user_id, result, amount, win, bet) VALUES (?, ?, ?, ?, ?)", r.Context().Value("user_id"), result, amount, win, bet)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	renderTemplate(w, "roulette.html", map[string]interface{}{
		"Result":   result,
		"Win":      win,
		"Amount":   amount,
		"Username": r.Context().Value(usernameKey),
		"Balance":  balance,
	})
}

/*func flagHandler(w http.ResponseWriter, r *http.Request) {
	balance := r.Context().Value(balanceKey).(int)
	if balance >= 1000000000000 {
		flag := os.Getenv("FLAG")
		renderTemplate(w, "flag.html", map[string]interface{}{
			"Flag":     flag,
			"Username": r.Context().Value(usernameKey),
			"Balance":  balance,
		})
		return
	}
	renderTemplate(w, "flag.html", map[string]interface{}{
		"Username": r.Context().Value(usernameKey),
		"Balance":  balance,
	})
}
*/

func flagHandler(w http.ResponseWriter, r *http.Request) {
    balance := r.Context().Value(balanceKey).(int)
    if balance >= 1000000000000 {
        flag, err := os.ReadFile("/flag")
        if err != nil {
            http.Error(w, "Could not read the flag", http.StatusInternalServerError)
            return
        }

        renderTemplate(w, "flag.html", map[string]interface{}{
            "Flag":     string(flag), // Convert []byte to string
            "Username": r.Context().Value(usernameKey),
            "Balance":  balance,
        })
        return
    }

    renderTemplate(w, "flag.html", map[string]interface{}{
        "Username": r.Context().Value(usernameKey),
        "Balance":  balance,
    })
}


func renderTemplate(w http.ResponseWriter, tmpl string, data interface{}) {
	w.Header().Set("Content-Type", "text/html")

	err := templates.ExecuteTemplate(w, tmpl, data)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}
