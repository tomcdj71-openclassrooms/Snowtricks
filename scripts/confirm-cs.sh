#!/bin/bash
read -p "Do you want to run the fix? [y/N] " ans
if [ "${ans,,}" == "y" ]; then
    make qa-cs-fixer
fi
