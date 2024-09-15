#!/bin/sh

#get highest tag number
VERSION=`git describe --abbrev=0 --tags`
V=""
if [[ $VERSION =~ "v" ]]; then
    V="v"
fi
#get number parts and increase last one by 1
VNUM1=$(echo "$VERSION" | cut -d"." -f1)
VNUM2=$(echo "$VERSION" | cut -d"." -f2)
VNUM3=$(echo "$VERSION" | cut -d"." -f3)
VNUM1=`echo $VNUM1 | sed 's/v//'`

# Check for #major or #minor in commit message and increment the relevant version number
MAJOR=`git log --format=%B -n 1 HEAD | grep '#major'`
MINOR=`git log --format=%B -n 1 HEAD | grep '#minor'`

if [ "$MAJOR" ]; then
    echo "Update major version"
    VNUM1=$((VNUM1+1))
    VNUM2=0
    VNUM3=0
elif [ "$MINOR" ]; then
    echo "Update minor version"
    VNUM2=$((VNUM2+1))
    VNUM3=0
else
    echo "Update patch version"
    VNUM3=$((VNUM3+1))
fi


#create new tag
NEW_TAG="$V$VNUM1.$VNUM2.$VNUM3"

if [ $NEW_TAG = "..1" ]; then
    NEW_TAG="v0.0.1"
fi

echo "Updating $VERSION to $NEW_TAG"

#get current hash and see if it already has a tag
GIT_COMMIT=`git rev-parse HEAD`
NEEDS_TAG=`git describe --contains $GIT_COMMIT 2>/dev/null`

#only tag if no tag already (would be better if the git describe command above could have a silent option)
if [ -z "$NEEDS_TAG" ]; then
    echo "Tagged with $NEW_TAG (Ignoring fatal:cannot describe - this means commit is untagged) "
    git tag $NEW_TAG --force
    # Use "oauth2" as user. For example for CI_PROJECT_URL=https://gitlab.com/acme/my-project
    #   set origin to https://oauth2:wSHnMvSmYXtTfXtqRMxs@gitlab.com/acme/my-project.git
    #
    git remote add tag-origin https://oauth2:${GITLAB_ACCESS_TOKEN}@gitlab.com/${CI_PROJECT_PATH}.git
    git tag -a "$NEW_TAG" -m "CI/CD auto tagged release: $NEW_TAG"

    # Don't trigger pipeline again:
    # -o ci.skip is not well known Gitlab Git option which allows skipping new CI.
    # Without ci.skip option CI would be triggered recursively by tag push.
    #
    git push tag-origin "$NEW_TAG" --force -o ci.skip
    git push --tags
    git remote -v
else
    echo "Already a tag on this commit"
fi