#!/bin/bash

while [ true ]; do
	su -l $USER -c "socat -dd TCP4-LISTEN:9000,fork,reuseaddr EXEC:'/home/$USER/$USER',pty,echo=0,rawer,iexten=0"
done;
