FROM golang:1.26-alpine AS builder

WORKDIR /app

COPY . .
RUN go mod init customainer-web
RUN go get github.com/gorilla/mux@v1.8.1
RUN CGO_ENABLED=0 GOOS=linux go build -o customainer-web .

FROM docker:29-dind

RUN apk add --no-cache ca-certificates curl

WORKDIR /app
COPY --from=builder /app/customainer-web .

EXPOSE 8080
CMD ["./customainer-web"]