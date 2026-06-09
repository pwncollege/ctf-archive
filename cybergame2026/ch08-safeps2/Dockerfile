FROM mcr.microsoft.com/powershell:latest

ARG TEST_FLAG=SK-CERT{{rehost_test_6f3d2b19}}

RUN apt-get update \
    && apt-get install -y --no-install-recommends socat \
    && rm -rf /var/lib/apt/lists/* \
    && printf '%s\n' "$TEST_FLAG" > /flag

COPY script.ps1 /srv/script.ps1

EXPOSE 6666

CMD ["sh", "-lc", "exec socat -d -d TCP-LISTEN:6666,reuseaddr,fork EXEC:\"pwsh -NoLogo -NoProfile -File /srv/script.ps1\",pty,rawer,echo=0,stderr,setsid,sigint,sane"]
