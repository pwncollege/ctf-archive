# Copyright 2020-2022 Google LLC
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     https://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
FROM python:3.8.17-alpine as chroot

RUN mkdir -p /challenge

WORKDIR /challenge

RUN python3.8 -m pip install RestrictedPython==5.0

COPY flag.txt /
COPY run.sh /challenge
COPY main.py /challenge

FROM gcr.io/kctf-docker/challenge@sha256:d884e54146b71baf91603d5b73e563eaffc5a42d494b1e32341a5f76363060fb

COPY --from=chroot / /chroot

COPY nsjail.cfg /challenge/

CMD kctf_setup && \
  kctf_drop_privs \
  socat \
  TCP-LISTEN:1337,reuseaddr,fork \
  EXEC:"kctf_pow nsjail --config /challenge/nsjail.cfg -- /challenge/run.sh"
