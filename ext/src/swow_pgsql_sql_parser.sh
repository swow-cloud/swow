#!/bin/sh

set -e

# usage:
# at phpdir:
# ./ext/swow/src/swow_pgsql_sql_parser.sh > ext/swow/src/swow_pgsql_sql_parser.c 

if [ ! -f "ext/pdo_pgsql/pgsql_sql_parser.re" ]; then
    echo "#error swow_pgsql_sql_parser.sh should be run in php-src root directory"
    echo "#error swow_pgsql_sql_parser.sh should be run in php-src root directory" >&2
    exit 1
fi

if ! type re2c >/tmp/nonce 2>&1; then
    echo "#error re2c is required to generate swow_pgsql_sql_parser.c"
    echo "#error re2c is required to generate swow_pgsql_sql_parser.c" >&2
    exit 1
fi

commit="$(git rev-list -1 HEAD ext/pdo_pgsql/pgsql_sql_parser.re)"

cat <<EOF
// generated by swow_pgsql_sql_parser.sh
// from ext/pdo_pgsql/pgsql_sql_parser.re
// php/php-src@$commit
EOF

sed 's/ pdo_pgsql_scanner/ swow_pdo_pgsql_scanner/g' < ext/pdo_pgsql/pgsql_sql_parser.re |
re2c --no-generation-date --no-version -i - ||
{ 
    echo "#error re2c failed"
    echo "#error re2c failed" >&2
    exit 1
}
