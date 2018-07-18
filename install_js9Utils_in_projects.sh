#!/bin/bash


declare -a projects=("/var/www/html/spot6/cat2" "/var/www/html/js9TestFolder" "/var/www/html/scigui/js_dependencies")

js9_dir_to_copy=/opt/prod/js9Utils/sources/.



if [ -z "$1" ]
  then
    printf "** No argument supplied"
    printf "\nPlease provide one of the following projects:\n"
    for i in "${projects[@]}"
    do
      printf "\n"
      echo $i
    done
    printf "\nOr insert 'all' if you want to install js9Utils in all the projects above\n"
    return;
fi



installed=0

if [ "$1" = all ]
then
  for i in "${projects[@]}"
    do
    echo "Installing all js9Utils in project $i"
    mkdir -p "$i"/js9Utils && mkdir -p "$i"/js9Utils/tmp && cp -R  "$js9_dir_to_copy" "$i"/js9Utils
    installed=1
  done
fi

for i in "${projects[@]}"
do
  if [ "$1" = "$i" ]
  then
     echo "Installing js9Utils in $1"
     mkdir -p "$i"/js9Utils && mkdir -p "$i"/js9Utils/tmp && cp -R "$js9_dir_to_copy" "$1"/js9Utils
     installed=1
  fi
done

if [ "$installed" -eq 0  ]
then
  printf "\n$1 does not match any project!\n\nProjects:\n"
  for i in "${projects[@]}"
  do
    printf "\n"
    echo $i
  done
  printf "\nInsert 'all' if you want to install js9Utils in all the projects above\n"
fi
