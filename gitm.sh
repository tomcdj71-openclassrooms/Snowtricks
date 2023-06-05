#!/bin/bash

# Check if a branch name was supplied
if [ -z "$1" ]
then
    echo "Error: Please provide a feature branch name."
    exit 1
fi

# Define the development branch
develop="develop"

# Check out the feature branch
git checkout $1

# Stash any unsaved work
git stash

# Check out the branch we want to merge into
git checkout $develop

# Pull latest changes
git pull origin $develop

# Go back to our feature branch
git checkout $1

# Apply any stashed work
git stash pop

# Rebase our branch
git rebase $develop

# Go back to the branch we want to merge into
git checkout $develop

# Merge our feature branch into it
git merge $1

# Push changes to remote
git push origin $develop

# ...

# Check if the remote branch exists
exists=$(git ls-remote --heads origin $1)

# If it exists, delete our local and remote feature branch
if [ -n "$exists" ]; then
    git branch -D $1
    git push origin --delete $1
else
    echo "The branch does not exist on the remote repository. Deleting it locally."
    git branch -D $1
fi


