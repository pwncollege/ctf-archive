FROM ubuntu:18.04

ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && \
    apt-get install -y \
    python3-pip \
    xz-utils \
    foremost \
    konsole \
    git \
    libxml2-dev \
    libxslt-dev \
    libffi-dev \  
    build-essential && \
    apt-get clean

RUN pip3 install --upgrade setuptools

RUN pip3 install --no-cache-dir \
    xortool \
    grapheme \
    emoji \
    plotly \
    tinyec
    
RUN git clone https://github.com/durkinza/KeyZ.git && \
    git clone https://github.com/josephsurin/lattice-based-cryptanalysis.git
