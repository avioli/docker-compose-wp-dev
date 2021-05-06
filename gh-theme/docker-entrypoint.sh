#!/bin/bash

set -e

[ -z "$ACCOUNT" ] && ACCOUNT="ionata"

if [ -z "$REPO_NAME" ]; then
	REPO_NAME=$(basename "$REPO")
elif [ -z "$REPO"]; then
	REPO="https://github.com/$ACCOUNT/$REPO_NAME"
fi

if [ -z "$REPO_NAME" -a -z "$THEME_DIRNAME" ]; then
	>&2 echo "No REPO_NAME or THEME_DIRNAME"
	exit 1
fi

[ -z "$THEME_DIRNAME" ] && THEME_DIRNAME="$REPO_NAME"

PROJECT_ROOT=/project
THEME_PATH="$THEME_DIRNAME"
if [ -n "$BASE_PATH" ]; then
    THEME_PATH="$BASE_PATH/$THEME_PATH"
fi
OUT_PATH="$PROJECT_ROOT/$THEME_PATH"

# Clone repo
if [ ! -d "$OUT_PATH" -o ! -d "$OUT_PATH/.git" ]; then
    ARGS=
    [ "$RECURSIVE" != "0" ] && ARGS="--recursive"
	git clone $ARGS "$REPO" "$OUT_PATH"
fi

# Install composer-aws s3 installer
if [ "$USE_IONATA_COMPOSER" == "true" ]; then
    if ! composer global config repositories.composer-aws >/dev/null 2>&1; then
        composer global config repositories.composer-aws git ssh://git-codecommit.ap-southeast-2.amazonaws.com/v1/repos/composer-plugin-aws
        composer global require ionata/composer-aws
    fi
fi

# Symlink composer file
if [ -z "$CWD" ]; then
    COMPOSER_FILE="$THEME_PATH/composer.json"
    test -f "$COMPOSER_FILE" && ln -sf "$COMPOSER_FILE" $PROJECT_ROOT/composer.json
else
    cd "$OUT_PATH"
fi

exec "$@"
