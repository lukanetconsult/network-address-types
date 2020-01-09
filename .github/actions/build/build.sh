#!/bin/sh

set -x

echo "::set-output name=context_dir::"$(pwd)
composer install --no-progress --no-interaction --no-ansi --prefer-dist --no-suggest || exit 1

run_tests() {
    if [ "$1" = 'true' ]; then
        composer run-script test -- --colors=always --coverage-clover ./clover.xml || return 1
    else
        composer run-script test -- --colors=always || return 1
    fi
}

RESULT=2

case "$1" in
    test)
        run_tests "$2"
        RESULT=$?
        ;;

    analyse)
        composer run-script analyse -- --no-progress
        RESULT=$?
        ;;

    cs)
        composer run-script cs-check
        RESULT=$?
        ;;

    *)
        echo "::error Unknown build stage '$1'"
        RESULT=4
        ;;
esac

echo "::set-output name=build_status::$RESULT"
exit $RESULT;
