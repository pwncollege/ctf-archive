from flask import Flask, make_response, render_template, request
from secrets import token_hex
import datetime

from captcha import captcha_challenge, captcha_verify, token_new, token_payload, token_inc
from lookup import LookupEntry, lookup_challenge, lookup_cleanup, is_revoked, revoke_token

from function import F
from igen import IGen
from png import tex_to_b64

from secret import FLAG

app = Flask(__name__)

DISCARD = 0.05
CHALLENGE_MAX_AGE = 8
TOKEN_MAX_AGE = 600


@app.get('/')
def home():
    lookup_cleanup()

    while True:
        func = F.complex()
        integral = IGen(func)
        if abs(integral.solve()) > DISCARD:
            break

    lookup = token_hex(32)
    expiry = datetime.datetime.now(datetime.UTC) + datetime.timedelta(seconds=CHALLENGE_MAX_AGE)
    challenge_jwt = captcha_challenge(lookup, expiry)
    
    lookup_challenge[lookup] = LookupEntry(integral, expiry)
    current_token = request.cookies.get('token', token_new())

    app.logger.info(f"\n-->Challenge = {lookup}\
                    \n-->Expiry = {expiry}\
                    \n-->Integral = {integral.latex()}\
                    \n-->Solution = \033[0;31m{integral.solve()}\033[0m")

    response = make_response(render_template('captcha.html', equation=tex_to_b64(integral.latex())))
    response.set_cookie('challenge', challenge_jwt, max_age=CHALLENGE_MAX_AGE)
    response.set_cookie('token', current_token, max_age=TOKEN_MAX_AGE)
    
    return response

@app.post('/')
def captcha():
    lookup_cleanup()
    
    token_jwt = request.cookies.get('token')
    
    if is_revoked(token_jwt):
        response = make_response("This session has been invalidated.", 401)
        response.set_cookie('token', token_new(), max_age=TOKEN_MAX_AGE)
        return response
    
    challenge_jwt = request.cookies.get('challenge')
    ans = request.form.get('answer', '0')
    
    app.logger.info(f"\n<-- Received answer: {ans}\
                    \n<-- Token JWT: {token_jwt}\
                    \n<-- COUNT: {token_payload(token_jwt).get('count', 'N/A')}")
    
    try:
        answer = float(ans)
    except ValueError:  
        revoke_token(token_jwt)
        return "Nope", 400

    if not challenge_jwt:
        return "No challenge token found. Please try again.", 400
    
    payload = token_payload(token_jwt)
    if captcha_verify(challenge_jwt, answer):
        current_challenge_id = token_payload(challenge_jwt).get("challenge")
        if not current_challenge_id:
            return "Invalid challenge token. Please try again.", 400
        
        if payload.get('last_id') == current_challenge_id:
            return "Challenge already solved with this token.", 400

        new_token = token_inc(payload, current_challenge_id)
        
        if payload.get('count', 0) == 3:
            response = make_response(f"Correct! You are human. Here is your flag: {FLAG}")
            response.set_cookie('token', new_token, max_age=TOKEN_MAX_AGE)
            return response
        else:
            response = make_response(f"Success! Count: {payload['count'] + 1}")
            response.set_cookie('token', new_token, max_age=TOKEN_MAX_AGE)
            return response
    else:
        revoke_token(token_jwt)
        return "Incorrect answer or token expired. Please try again.", 400

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)