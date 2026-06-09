FROM mcr.microsoft.com/powershell:latest

ARG TEST_FLAG="SK-CERT{{rehost_test_q7m4k2}}"

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends socat; \
    rm -rf /var/lib/apt/lists/*; \
    printf '%s\n' "$TEST_FLAG" > /flag

COPY script.ps1 /srv/script.ps1

EXPOSE 6969

CMD ["sh", "-lc", "exec socat -d -d TCP-LISTEN:6969,reuseaddr,fork EXEC:\"pwsh -NoLogo -NoProfile -File /srv/script.ps1\",pty,rawer,echo=0,stderr,setsid,sigint,sane"]
