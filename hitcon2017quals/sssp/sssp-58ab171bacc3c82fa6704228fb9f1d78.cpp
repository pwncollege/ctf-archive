#include <bits/stdc++.h>
#include <unistd.h>
using namespace std;

typedef __int128_t Num;

string red(const string &s) {
    return "\e[1;31m"s + s + "\e[0m";
}

string green(const string &s) {
    return "\e[1;32m"s + s + "\e[0m";
}

istream& operator>>(istream &is, Num &x) {
    string s;
    is >> s;
    auto p = s.data();
    bool neg = false;
    if (*p && *p == '-') {
        neg = true;
        p++;
    }
    x = 0;
    while (*p) x = x * 10 + *p++ - '0';
    if (neg) x = -x;
    return is;
}

ostream& operator<<(ostream &os, Num x) {
    if (x == 0) return os << "0";
    if (x < 0) {
        os << '-';
        x = -x;
    }
    string s;
    while (x != 0) {
        s.push_back(x % 10 + '0');
        x /= 10;
    }
    reverse(begin(s), end(s));
    return os << s;
}

template<class T>
struct Rng {
    T r;

    Rng() {
        array<typename T::result_type, T::state_size> data;
        random_device rd;
        generate(begin(data), end(data), ref(rd));
        auto seq = seed_seq(begin(data), end(data));
        r = T{seq};
    }

    Num operator()(int n, bool sign = false) {
        Num val = 0;
        while (n > 0) {
            int w = min(n, 32);
            auto mask = ~0u >> (32 - w);
            val = (val << w) | (r() & mask);
            n -= w;
        }
        if (sign && r() % 2 == 1) val = -val;
        return val;
    }
};

Rng<mt19937> rnd;

template<class T>
bool is_subseq(const vector<T> &a, const vector<T> &b) {
    for (auto i = begin(a), j = begin(b); i != end(a); i++, j++) {
        while (j != end(b) && *i != *j) j++;
        if (j == end(b)) return false;
    }
    return true;
}

struct Prob {
    Num s;
    vector<Num> a;

    Prob(int n, int m) {
        for (int i = 0; i < n; i++) a.push_back(rnd(m, true));
        s = 0;
        for (int i = 0; i < n; i++) {
            if (rnd(1)) s += a[i];
        }
    }

    bool check(vector<Num> sol) {
        return accumulate(begin(sol), end(sol), Num(0)) == s && is_subseq(sol, a);
    }

    friend ostream& operator<<(ostream &os, const Prob &p) {
        os << p.s << " from";
        for (auto i : p.a) os << ' ' << i;
        return os;
    }
};

void TLE(int) {
    cout << red("Time Limit Exceeded") << endl;
    exit(0);
}

bool run() {
    for (int i = 1; i <= 30; i++) {
        int n = 4 * i + 7;
        int m = min(4 * i + 20, 120);
        assert(log2(n) + m < 127);
        auto prob = Prob(n, m);
        cout << "Prob " << i << ": " << prob << endl;
        alarm(5);
        int l;
        cin >> l;
        if (l < 0 || l > n) return false;
        vector<Num> sol(l);
        for (auto &j : sol) cin >> j;
        alarm(0);
        if (!prob.check(sol)) return false;
    }
    return true;
}

int main() {
    signal(SIGALRM, TLE);
    if (run()) {
        cout << green(getenv("FLAG")) << endl;
    } else {
        cout << red("Wrong Answer") << endl;
    }
    return 0;
}
