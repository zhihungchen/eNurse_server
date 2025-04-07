#!/bin/bash

echo "Staging all files except 'db/' and other ignored paths..."

# Add all files except those inside db/ or other excluded paths
find . -type f \
  -not -path "./db/*" \
  -not -path "./.git/*" \
  -not -path "./.env"\
  -exec git add {} +

echo "Staging complete."

# Ask user to commit
read -p "Enter commit message (leave blank to skip committing): " msg
if [ ! -z "$msg" ]; then
  git commit -m "$msg"
  read -p "Do you want to push to GitHub? (y/n): " push_ans
  if [ "$push_ans" = "y" ]; then
    git push
  else
    echo "Push skipped. You can push manually with 'git push'."
  fi
else
  echo "No commit was created. You can commit manually with 'git commit -m \"your message\"'."
fi
