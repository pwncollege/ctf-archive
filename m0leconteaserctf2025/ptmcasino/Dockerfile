FROM golang:1.23

WORKDIR /app

COPY src/go.mod src/go.sum ./
RUN go mod download && go mod verify

COPY src/ .
RUN go build -v -o /usr/local/bin/app ./...

CMD ["app"]