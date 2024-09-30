FROM golang:1.23

WORKDIR /app

COPY /challenge/src/go.mod /challenge/src/go.sum ./
RUN go mod download && go mod verify

COPY /challenge/src/ .
RUN go build -v -o /usr/local/bin/app ./...

CMD ["app"]
