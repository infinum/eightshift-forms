#!/usr/bin/env bash

set -euo pipefail
set -x # debugging purposes

# Arguments
# $1 - current_path variable (root)

mkdir -p ./eightshift-forms/

# awk solution taken from https://stackoverflow.com/a/66832595/629127
ignore_list=(".DS_Store" "node_modules" ".git" ".github" "bin" ".storybook" "assets" "storybook" "tests" ".editorconfig" ".eslintignore" ".eslintrc" ".gitignore" ".stylelintrc" "babel.config.js" "composer.json" "composer.lock" "package.json" "package-lock.json" "phpcs.xml.dist" "phpstan.neon" "phpstan.neon.dist" "postcss.config.js" "webpack.config.js" "CODE_OF_CONDUCT.md" "codeception.yml" "travis.yml" "eightshift-forms")

# Exclude the files and folders we don't want to keep
for element in *; do
    if [[ ! "${ignore_list[*]}" =~ $element ]]; then
        cp -pR "$element" './eightshift-forms/'"$element";
    fi
done
