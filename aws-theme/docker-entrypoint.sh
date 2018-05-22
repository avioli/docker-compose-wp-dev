#!/bin/bash

set -e

[ -z "$AWS_DEFAULT_REGION" ] && AWS_DEFAULT_REGION="us-east-1"

if [ -z "$REPO_NAME" ]; then
	REPO_NAME=$(basename "$REPO")
elif [ -z "$REPO"]; then
	REPO="ssh://git-codecommit.$AWS_DEFAULT_REGION.amazonaws.com/v1/repos/$REPO_NAME"
fi

if [ -z "$REPO_NAME" -a -z "$THEME_DIRNAME" ]; then
	>&2 echo "No REPO_NAME or THEME_DIRNAME"
	exit 1
fi

[ -z "$THEME_DIRNAME" ] && THEME_DIRNAME="$REPO_NAME"

PROJECT_ROOT=/project
OUTPATH=$PROJECT_ROOT

if [ -n "$BASE_PATH" ]; then
    OUTPATH="$OUTPATH/$BASE_PATH/$THEME_DIRNAME"
else
    OUTPATH="$OUTPATH/$THEME_DIRNAME"
fi

if [ ! -d "$OUTPATH" -o ! -d "$OUTPATH/.git" ]; then
    ARGS=

    [ "$RECURSIVE" != "0" ] && ARGS="--recursive"

	git clone $ARGS "$REPO" "$OUTPATH"
fi

if [ -z "$CWD" ]; then
    COMPOSER_FILE="$OUTPATH/composer.json"
    test -f "$COMPOSER_FILE" && ln -sf "$COMPOSER_FILE" $PROJECT_ROOT/composer.json
else
    cd "$OUTPATH"
fi

exec "$@"
