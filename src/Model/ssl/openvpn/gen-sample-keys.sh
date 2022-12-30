#!/bin/sh
#
# Run this script to set up a test CA, and test key-certificate pair for a
# server, and various clients.
#
# Copyright (C) 2014 Steffan Karger <steffan@karger.me>
set -eu

command -v openssl >/dev/null 2>&1 || { echo >&2 "Unable to find openssl. Please make sure openssl is installed and in your path."; exit 1; }

if [ ! -f openssl.cnf ]
then
    echo "Please run this script from the sample directory"
    exit 1
fi

# Generate static key for tls-auth (or static key mode)
#$(dirname ${0})/../../src/openvpn/openvpn --genkey --secret ta.key

# Create required directories and files
mkdir -p sample-ca
rm -f sample-ca/index.txt
touch sample-ca/index.txt
# (?) Ideally, check the serial in the last crt and increment
echo "01" > sample-ca/serial

# -enddate is 10 years from startdate
days=3650

# Generate CA key and cert
openssl req -new -newkey rsa:2048 -days $days -nodes -x509 \
    -extensions easyrsa_ca -keyout sample-ca/ca.key -out sample-ca/ca.crt \
    -subj "/C=TR/ST=Antalya/L=Serik/O=ComixWall/OU=OpenVPN/CN=example.org/emailAddress=sonertari@gmail.com" \
    -config openssl.cnf

# Create server key and cert
openssl req -new -nodes -config openssl.cnf -extensions server \
    -keyout sample-ca/server.key -out sample-ca/server.csr \
    -subj "/C=TR/ST=Antalya/L=Serik/O=ComixWall/OU=OpenVPN Server/CN=example.org/emailAddress=sonertari@gmail.com"
openssl ca -batch -config openssl.cnf -extensions server \
    -out sample-ca/server.crt -in sample-ca/server.csr

# Create client key and cert
openssl req -new -nodes -config openssl.cnf \
    -keyout sample-ca/client.key -out sample-ca/client.csr \
    -subj "/C=TR/ST=Antalya/L=Serik/O=ComixWall/OU=OpenVPN Client/CN=example.org/emailAddress=sonertari@gmail.com"
openssl ca -batch -config openssl.cnf \
    -out sample-ca/client.crt -in sample-ca/client.csr

# Copy keys and certs to working directory
cp sample-ca/*.key .
cp sample-ca/*.crt .
