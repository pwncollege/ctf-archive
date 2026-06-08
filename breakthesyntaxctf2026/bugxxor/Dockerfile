FROM python:3.11-slim

WORKDIR /app

RUN python -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .
RUN python manage.py collectstatic --noinput

# Default test flag baked into the image. Replace /flag at deploy time
# (e.g. via the commented compose volume) with the real flag.
RUN cp flag /flag

ARG ADMIN_PASSWORD=6bf789ed-6388-4cc6-863a-70ccc335dcbd
RUN python manage.py migrate --run-syncdb && python manage.py seed

EXPOSE 8000

CMD ["gunicorn", "bugxxor.wsgi:application", "--bind", "0.0.0.0:8000"]
