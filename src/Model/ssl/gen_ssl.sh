# !/bin/sh
# Copyright (C) 2020-2023 Soner Tari <sonertari@gmail.com>

# TODO: Handle errors

if [ -z "$PREFIX" ]; then
	PREFIX=/etc
fi
echo "PREFIX=$PREFIX"

if [ -z "$SET_SERIAL" ]; then
	SET_SERIAL=1
fi
echo "SET_SERIAL=$SET_SERIAL"

install_file() {
	local _filename=$1 _src=$2 _dst=$3 _mod=$4 _own=$5

    _filepath="$_dst/$_filename"
    if [ -f $_filepath ]; then
        cp $_filepath $_filepath.orig && echo "Saved old file as $_filepath.orig"
    fi
    cp "$_src/$_filename" $_filepath
    chmod $_mod $_filepath
    chown $_own $_filepath
}

# This is a workaround in the absence of faketime
origdate=$(date "+%Y%m%d%H%M")

# -startdate is 2 years from now
date "$(($(date "+%Y")-2))$(date "+%m%d%H%M")"

# -enddate is 10 years from startdate (8 years from now)
days=3650

# httpd
cd httpd
openssl genrsa -out ca.key 2048
openssl req -new -nodes -x509 -sha256 -out ca.crt -key ca.key -extensions v3_ca -set_serial $SET_SERIAL -days $days \
    -config httpd_ca.cnf \
    -subj "/C=TR/ST=Antalya/L=Serik/O=ComixWall/OU=UTMFW/CN=example.org/emailAddress=sonertari@gmail.com"

openssl req -new -nodes -sha256 -keyout server.key -out server.csr \
    -config httpd.cnf \
    -subj "/C=TR/ST=Antalya/L=Serik/O=ComixWall/OU=UTMFW/CN=example.org/emailAddress=sonertari@gmail.com"
openssl x509 -req -CA ca.crt -CAkey ca.key -in server.csr -out server.crt -extensions server -set_serial $SET_SERIAL -days $days
cd ..

install_file "server.crt" "httpd" "$PREFIX/ssl" "644" "root:bin"
install_file "server.key" "httpd" "$PREFIX/ssl/private" "644" "root:bin"

# openvpn
cd openvpn
sh gen-sample-keys.sh
# DH param generation takes a long time
#openssl dhparam -out dh2048.pem -2 2048
cd ..

install_file "ca.crt" "openvpn" "$PREFIX/openvpn" "444" "root:wheel"
install_file "client.crt" "openvpn" "$PREFIX/openvpn" "444" "root:wheel"
install_file "client.key" "openvpn" "$PREFIX/openvpn" "400" "root:wheel"
install_file "server.crt" "openvpn" "$PREFIX/openvpn" "444" "root:wheel"
install_file "server.key" "openvpn" "$PREFIX/openvpn" "400" "root:wheel"

# sslproxy
cd sslproxy
openssl genrsa -out ca.key 2048
openssl req -new -nodes -x509 -sha256 -out ca.crt -key ca.key -extensions v3_ca -set_serial $SET_SERIAL -days $days \
    -config sslproxy.cnf \
    -subj "/C=TR/ST=Antalya/L=Serik/O=ComixWall/OU=SSLproxy/CN=example.org/emailAddress=sonertari@gmail.com"
cd ..

install_file "ca.crt" "sslproxy" "$PREFIX/sslproxy" "644" "root:bin"
install_file "ca.key" "sslproxy" "$PREFIX/sslproxy" "644" "root:bin"

# restore orig date
date $origdate
