#!/bin/bash
set -euo pipefail

#get highest tag number
VERSION=$(git describe --abbrev=0 --tags 2>/dev/null || echo "v0.0.0")
TARGET_REF="${CI_COMMIT_SHA:-HEAD}"
MASTER_COMMIT_MESSAGE=$(git log --format=%B -n 1 "$TARGET_REF")
RELEASE_NOTES="$MASTER_COMMIT_MESSAGE"

while [[ $# -gt 0 ]]; do
    case "$1" in
        --message)
            shift
            RELEASE_NOTES="${1:-$MASTER_COMMIT_MESSAGE}"
            ;;
        --message-file)
            shift
            if [[ -z "${1:-}" || ! -r "$1" ]]; then
                echo "--message-file requires a readable file path" >&2
                exit 1
            fi
            RELEASE_NOTES="$(cat "$1")"
            ;;
        *)
            echo "Unknown argument: $1" >&2
            exit 1
            ;;
    esac
    shift
done

V=""
if [[ $VERSION =~ "v" ]]; then
    V="v"
fi
#get number parts and increase last one by 1
VNUM1=$(echo "$VERSION" | cut -d"." -f1)
VNUM2=$(echo "$VERSION" | cut -d"." -f2)
VNUM3=$(echo "$VERSION" | cut -d"." -f3)
VNUM1=$(echo "$VNUM1" | sed 's/v//')

# Check for #major or #minor in the master commit message and increment the relevant version number
if printf "%s" "$MASTER_COMMIT_MESSAGE" | grep -q '#major'; then
    echo "Update major version"
    VNUM1=$((VNUM1+1))
    VNUM2=0
    VNUM3=0
elif printf "%s" "$MASTER_COMMIT_MESSAGE" | grep -q '#minor'; then
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

if [ -z "$RELEASE_NOTES" ]; then
    RELEASE_NOTES="$MASTER_COMMIT_MESSAGE"
fi

if [ -z "$RELEASE_NOTES" ]; then
    RELEASE_NOTES="Release $NEW_TAG"
fi

#get current hash and see if it already has a tag
GIT_COMMIT=$(git rev-parse "$TARGET_REF")
NEEDS_TAG=$(git tag --points-at "$GIT_COMMIT")

#only tag if no tag already (would be better if the git describe command above could have a silent option)
if [ -z "$NEEDS_TAG" ]; then
    echo "Tagged with $NEW_TAG"
    # Use "oauth2" as user. For example for CI_PROJECT_URL=https://gitlab.com/acme/my-project
    #   set origin to https://oauth2:wSHnMvSmYXtTfXtqRMxs@gitlab.com/acme/my-project.git
    #
    if [ -n "${GITLAB_ACCESS_TOKEN:-}" ] && [ -n "${CI_PROJECT_PATH:-}" ]; then
        git remote remove tag-origin >/dev/null 2>&1 || true
        git remote add tag-origin https://oauth2:${GITLAB_ACCESS_TOKEN}@gitlab.com/${CI_PROJECT_PATH}.git
    elif [ -n "${CI:-}" ]; then
        echo "GITLAB_ACCESS_TOKEN and CI_PROJECT_PATH are required in CI to push tags" >&2
        exit 1
    fi

    git tag -a "$NEW_TAG" -m "$RELEASE_NOTES" --force "$GIT_COMMIT"

    # Don't trigger pipeline again:
    # -o ci.skip is not well known Gitlab Git option which allows skipping new CI.
    # Without ci.skip option CI would be triggered recursively by tag push.
    #
    if git remote get-url tag-origin >/dev/null 2>&1; then
        git push tag-origin "$NEW_TAG" --force -o ci.skip
    else
        git push origin "$NEW_TAG" --force -o ci.skip
    fi

    git remote -v
else
    echo "Already a tag on this commit"
fi