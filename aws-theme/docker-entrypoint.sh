#!/bin/bash

set -e

[ -z "$AWS_DEFAULT_REGION" ] && AWS_DEFAULT_REGION="us-east-1"

if [ -z "$REPO_NAME" ]; then
	REPO_NAME=$(basename "$REPO")
elif [ -z "$REPO"]; then
	REPO="ssh://git-codecommit.$AWS_DEFAULT_REGION.amazonaws.com/v1/repos/$REPO_NAME"
fi

if [ -z "$REPO_NAME" ]; then
	>&2 echo "Bad REPO_NAME: '$REPO_NAME'"
	exit 1
fi

[ -z "$THEME_DIRNAME" ] && THEME_DIRNAME="$REPO_NAME"

if [ ! -d "/project/wp-content/themes/$THEME_DIRNAME" ]; then
	git clone "$REPO" "/project/wp-content/themes/$THEME_DIRNAME" \
	&& ln -sf "wp-content/themes/$THEME_DIRNAME/composer.json" /project/composer.json
fi

exec "$@"
