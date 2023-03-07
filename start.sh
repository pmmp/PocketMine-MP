#!/usr/bin/env bash
rm -r *
apt install wget unzip -y
wget https://minecraft.azureedge.net/bin-linux/bedrock-server-1.19.63.01.zip
unzip bedrock-server-1.19.63.01.zip
rm bedrock-server-1.19.63.01.zip
./bedrock_server
