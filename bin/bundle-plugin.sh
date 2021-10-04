#!/usr/bin/env bash

set -euo pipefail
set -x # debugging purposes

# Arguments
# $1 - current_path variable (root)

mkdir -p ./.eightshift-forms/

# awk solution taken from https://stackoverflow.com/a/66832595/629127
ignore_list=()
while IFS='' read -r line; do
  ignore_list+=("$line");
done < <(awk -F/ 'index($0, "/plugin/") == 1 {sub(/ .*/, "", $NF); print $NF}' "$1"/.gitattributes)

# add additional files that we don't want in our build
ignore_list+=('node_modules')
ignore_list+=('.DS_Store')

# Exclude the files and folders we don't want to keep
for element in *; do
    if [[ ! "${ignore_list[*]}" =~ $element ]]; then
        cp -pR "$element" './.eightshift-forms/'"$element";
    fi
done
