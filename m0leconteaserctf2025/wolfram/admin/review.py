#!/usr/bin/python3

import os
import sys
import time
import traceback
from urllib.parse import urlparse

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.ui import WebDriverWait

from hashcash import request_payment

host = "walframsigma.local:8080"
# host = "walframsigma.challs.m0lecon.it"

url = input("Hello, what function would you like to share?\n")
print(url, file=sys.stderr, flush=True)
parsed_url = urlparse(url)
if (parsed_url.netloc != host):
    print("Sorry but i don't manage that site. Bye!")
    quit()
if (parsed_url.scheme != "https"):
    if parsed_url.scheme == "http":
        print("What is that? HTTP? Are we in the stone age? We are Secure here. Bye!")
    else:
        print("Naah, we don't use that protocol here. Bye!")
    quit()
print("It seems like a lot of work checking that, but i'll do it for some hashcash...")
if not request_payment():
    quit()
print("Now we're talking.")

print("Let", end=" ", flush=True)
try:
    options = webdriver.firefox.options.Options()
    print("me", end=" ", flush=True)
    options.profile = webdriver.firefox.firefox_profile.FirefoxProfile()
    print("take", end=" ", flush=True)
    options.add_argument("--headless")
    options.set_preference("accept_insecure_certs", True)
    print("a", end=" ", flush=True)
    driver = webdriver.Remote(command_executor=f"http://127.0.0.1:4444/wd/hub", options=options)
    # driver = webdriver.Firefox(options=options)
    print("look", end=" ", flush=True)
    driver.maximize_window()
    print("at", end=" ", flush=True)
    admin_user = os.environ["ADMIN_USER"],
    admin_password = os.environ["ADMIN_PASSWORD"],
    print("that", end="", flush=True)
    # login
    driver.get(f"https://{host}/")
    WebDriverWait(driver, 5).until(expected_conditions.element_to_be_clickable((By.ID, "submit")))
    print(".", end="", flush=True)
    driver.find_element(By.ID, "login")
    e = driver.find_element(By.ID, "username")
    e.clear()
    e.send_keys(admin_user)
    e = driver.find_element(By.ID, "password")
    e.clear()
    e.send_keys(admin_password)
    e.send_keys(Keys.RETURN)
    # e = driver.find_element(By.ID, "submit")
    # e.click()
    WebDriverWait(driver, 5).until(expected_conditions.element_to_be_clickable((By.ID, "draw")))
    print(".", end="", flush=True)
    # login done
    driver.get(url)
    WebDriverWait(driver, 5).until(expected_conditions.element_to_be_clickable((By.ID, "draw")))
    print(".", end="", flush=True)
    time.sleep(5)
    if driver.current_url != url:
        print()
        # print(driver.current_url)
        print("Seems that i could not reach that page, ")
        raise AssertionError()
    print()
    # print("-" * 50)
    # print(driver.current_url)
    # print(driver.page_source)
    # print("-" * 50)
    print("Nice Function!")
except Exception as e:
    print()
    if type(e) is not AssertionError:
        traceback.print_exc(file=sys.stderr)
    print("Seems that there is something wrong there...")
finally:
    try:
        driver.close()
    except Exception:
        pass
    try:
        driver.quit()
    except Exception:
        pass
