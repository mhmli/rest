#!/usr/bin/env bash

set -o nounset
set -o errexit
#set +e

: ${FUNCTIONAL_TESTS="yes"}
: ${UNIT_TESTS="yes"}

: ${TYPO3_PATH_WEB=""}

: ${typo3DatabaseName="typo3"}
: ${typo3DatabaseHost="127.0.0.1"}
: ${typo3DatabaseUsername="root"}
: ${typo3DatabasePassword="root"}

function print_error() {
    >&2 echo "[ERROR] $@";
}

function get_mysql_client_path {
    if [[ `which mysql > /dev/null` ]]; then
        which mysql;
    elif [[ -x /Applications/MAMP/Library/bin/mysql ]]; then
        echo /Applications/MAMP/Library/bin/mysql;
    else
        return 1;
    fi
}

function get_phpunit_path() {
    if [ -e "$TYPO3_PATH_WEB/bin/phpunit" ]; then
        echo "$TYPO3_PATH_WEB/bin/phpunit";
    elif [ -e "$TYPO3_PATH_WEB/vendor/bin/phpunit" ]; then
        echo "$TYPO3_PATH_WEB/vendor/bin/phpunit";
    else
        print_error "ERROR: Could not find phpunit";
        exit 1;
    fi
}

function check_mysql_credentials {
    `get_mysql_client_path` -u${typo3DatabaseUsername} -p${typo3DatabasePassword} -h${typo3DatabaseHost} -D${typo3DatabaseName} -e "exit" 2> /dev/null;
    if [ $? -ne 0 ]; then
        print_error "Could not connect to MySQL";
        exit 1;
    fi

    php -r '@mysqli_connect("'${typo3DatabaseHost}'", "'${typo3DatabaseUsername}'", "'${typo3DatabasePassword}'", "'${typo3DatabaseName}'") or die(mysqli_connect_error());';
    if [ $? -ne 0 ]; then
        print_error "Could not connect to MySQL";
        exit 1;
    fi
}

function init_database {
    # Export database credentials
    export typo3DatabaseName=${typo3DatabaseName};
    export typo3DatabaseHost=${typo3DatabaseHost};
    export typo3DatabaseUsername=${typo3DatabaseUsername};
    export typo3DatabasePassword=${typo3DatabasePassword};
    echo "Connect to database '$typo3DatabaseName' at '$typo3DatabaseHost' using '$typo3DatabaseUsername' '$typo3DatabasePassword'";
}

function init_typo3 {
	local baseDir=`pwd`;
	if [[ ! -x ${TYPO3_PATH_WEB}/bin/phpunit ]]; then
		cd ${TYPO3_PATH_WEB};
		composer install;
		cd ${baseDir};
	fi
}

function init {
    # Test the environment
    if [ "${TYPO3_PATH_WEB}" == "" ]; then
        print_error "Please set the TYPO3_PATH_WEB environment variable";
        exit 1;
    elif [[ ! -d ${TYPO3_PATH_WEB} ]]; then
        print_error "The defined TYPO3_PATH_WEB does not seem to be a directory";
        exit 1;
    fi;

	init_typo3;
    init_database;
}

function unit_tests {
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        $(get_phpunit_path) --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/UnitTests.xml "$@";
    else
        $(get_phpunit_path) --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/UnitTests.xml ./Tests/Unit "$@";
    fi
}

function functional_tests {
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        $(get_phpunit_path) --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/FunctionalTests.xml "$@";
    else
        $(get_phpunit_path) --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/FunctionalTests.xml ./Tests/Functional "$@";
    fi
}

function main {
    init;
    if [[ "$UNIT_TESTS" == "yes" ]]; then
        echo "Run Unit Tests";
        unit_tests "$@";
    fi

    if [[ "$FUNCTIONAL_TESTS" == "yes" ]]; then
        echo "Run Functional Tests";
        check_mysql_credentials;
        functional_tests "$@";
    fi
}

main $@;
