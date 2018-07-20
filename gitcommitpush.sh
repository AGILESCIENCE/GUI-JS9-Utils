#!/bin/bash

if [ $# -eq 0 ]
  then
    echo "Please, insert commit message"
    return
fi

git add .
git commit -m "$1" --author="Leonardo Baroncelli <leonardo.baroncelli26@gmail.com>"
git push origin master
git log --graph --pretty=format:'%Cred%h%Creset -%C(yellow)%d%Creset %s %Cgreen(%cr) %C(bold blue)<%an>%Creset' --abbrev-commit
