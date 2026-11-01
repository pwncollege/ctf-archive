from flask import Flask, session, redirect, url_for, request
import random
from datetime import datetime
from PIL import Image

app = Flask(__name__, static_url_path='', static_folder='static/')
app.secret_key = '20f75e46-4d3a-452d-ae10-96d1b45d2428'

@app.route('/')
@app.route('/instructions')
def instructions():
    return app.send_static_file("instructions.html")

@app.route('/scantron.png')
def scantron():
    return app.send_static_file("scantron.png")

@app.route("/exam", methods=["GET"])
def get_exam():
    # Initialize exam section if it is uninitialized 
    if "exam_section" not in session.keys():
        session["exam_section"] = 1

    # If you've solved 10 exams, you get the flag :)
    if session["exam_section"] >= 10:
        with open("flag.txt", "r") as fp:
            return fp.read().strip()

    # Generate the quesions, and store the list of questions and solutionn
    (exam, solutions) = generate_exam()
    session["solutions"] = solutions
    # store time when exam was started
    session["start_time"] = datetime.now()
    # return how many solves the user current has + the next exam
    exam = "<h1>Exam Section {}</h1>".format(session["exam_section"]) + exam
    return exam

@app.route("/exam", methods=["POST"])
def submit_exam():
    time_delta = ((datetime.now() - session["start_time"].replace(tzinfo=None)).seconds)
    # If it has been > 4 seconds since the exam was given to the user, they fail
    if time_delta > 4:
        session["exam_section"] = 1
        return 'Too slow!, <a href="/exam">Try again<a>'

    # check to make sure file was properly included
    if 'file' not in request.files:
        return 'Please upload your scantron!, <a href="/exam">Try again<a>'
    f = request.files['file']
    if f.filename == '':
        return 'Please upload your scantron!, <a href="/exam">Try again<a>'

    # if they passed the exam, increase their examm_section value by one and redirect them to get a new exam
    if score_exam(f, session["solutions"]):
        session["exam_section"] += 1
        return redirect(url_for("get_exam"))

    # if exam was incorrect, start them over at 1 and let them know
    session["exam_section"] = 1
    return 'Wrong!, <a href="/exam">Try again<a>'

# Same as normal get_exam but doesn't ever give flag / doesn't keep track of progress
@app.route("/practice", methods=["GET"])
def practice_get_exam():
    (exam, solutions) = generate_exam()
    session["solutions"] = solutions
    exam = "<h1>Practice Exam</h1>"+ exam
    return exam

# same as normal submit_exam but doesn't automatically redirect you, and doesn't keep track of # of solves
@app.route("/practice", methods=["POST"])
def practice_submit_exam():
    if 'file' not in request.files:
        return 'Please upload your scantron!, <a href="/exam">Try again<a>'
    f = request.files['file']
    if f.filename == '':
        return 'Please upload your scantron!, <a href="/exam">Try again<a>'

    if score_exam(f, session["solutions"]):
        return 'Correct, good job!, <a href="/practice">Try again<a>'
    # if exam was incorrect
    return 'Wrong!, <a href="/practice">Try again<a>'


# Building all the html because I cbf to use a templating engine 
# I'm sorry to anyone reading this
def generate_exam():
    solutions = []
    # start html ordered list
    exam = '<ol type="1">\n'

    # Generate 20 questions
    for n in range(20):
        # generate individual quesiton string
        prob = "{} {} {}".format(random.randrange(1,200), 
                                 random.choice(["+","-","*","/"]), 
                                 random.randrange(1,200)) 
        # add problem to ordered list in html
        exam += "<li>{}</li>\n".format(prob)

        # choose what the answer for the problem will be (A-D = 0 - 3)
        # keep track of all answers in solutions array
        answer = random.randrange(4)
        solutions.append(answer)

        # Add ordered list for all multiple choice options
        exam += '<ol type="A">\n'
        # generate multiple choice options
        for i in range(4):
            # right answer
            if i == answer:
                a = eval(prob.replace("/","//"))
            # random wrong answers
            else:
                a = random.randrange(20000)
            # place choice in html
            exam += '<li>{}</li>\n'.format(a)
        # ened options list
        exam += '</ol>\n'

    # end quesitons list
    exam += "</ol>\n"
    # add form to submit filled out scantron
    exam += '''
   <h1>Upload Solution</h1>
    <form method=post enctype=multipart/form-data>
      <input type=file name=file>
      <input type=submit value=Upload>
    </form>
    '''
    return (exam, solutions)

def score_exam(f, solutions):
    # x and y for question ! bubble A
    startx = 360 
    starty = 460
    # difference between two questions
    height_diff = 90
    # difference between two bubbles
    width_diff = 70

    # verify submission is a valid image with valid dimensions
    try:
        scantron = Image.open(f)
        scantron = scantron.convert('RGB')
        scantron.verify()
        width, height = scantron.size
        if (width != 1397) or (height != 1738):
            raise Exception
    except:
        return False

    # iterate through each question
    for n in range(20):
        if n == 10:
            startx += 490
        y = starty + (n%10)*(height_diff)
        # iterate over each bubble to check that only the correct one is filled
        for i in range(5):
            x = startx + (i*width_diff)
            pixel = scantron.getpixel((x,y))
            if solutions[n] == i:
                if pixel != (0,0,0):
                    return False
            else:
                if pixel == (0,0,0):
                    return False
    # If every question passes the check, they pass the exam
    return True


if __name__ == '__main__':
    app.run(debug=False, host='0.0.0.0')
