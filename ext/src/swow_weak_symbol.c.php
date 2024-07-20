<?php

declare(strict_types=1);

?>
/*
  +--------------------------------------------------------------------------+
  | Swow                                                                     |
  +--------------------------------------------------------------------------+
  | Licensed under the Apache License, Version 2.0 (the "License");          |
  | you may not use this file except in compliance with the License.         |
  | You may obtain a copy of the License at                                  |
  | http://www.apache.org/licenses/LICENSE-2.0                               |
  | Unless required by applicable law or agreed to in writing, software      |
  | distributed under the License is distributed on an "AS IS" BASIS,        |
  | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. |
  | See the License for the specific language governing permissions and      |
  | limitations under the License. See accompanying LICENSE file.            |
  +--------------------------------------------------------------------------+
  | Author: Yun Dou <dixyes@gmail.com>                                       |
  +--------------------------------------------------------------------------+
 */
// weak symbol reference by dl
// this file is generated by swow_weak_symbol.c.php

#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>
#include <stddef.h>
#include <stdbool.h>

#ifdef __GNUC__
#include <dlfcn.h>
#endif

#include "php_version.h"
#include "php.h"

#include "swow.h"
<?php

function weak(string $name, string $ret, string $argsSign, string $argsPassthruSign): void
{
    $return = $ret === 'void' ? "swow_{$name}_resolved({$argsPassthruSign})" : "return swow_{$name}_resolved({$argsPassthruSign})";
    echo <<<C
// weak function pointer for {$name}
#ifdef CAT_OS_WIN
// extern {$ret} {$name}({$argsSign});
# pragma comment(linker, "/alternatename:{$name}=swow_{$name}_redirect")
#else
__attribute__((weak, alias("swow_{$name}_redirect"))) extern {$ret} {$name}({$argsSign});
#endif
// resolved function holder
{$ret} (*swow_{$name}_resolved)({$argsSign});
// resolver for {$name}
{$ret} swow_{$name}_resolver({$argsSign}) {
    swow_{$name}_resolved = ({$ret} (*)({$argsSign}))DL_FETCH_SYMBOL(DL_FROM_HANDLE, "{$name}");

    if (swow_{$name}_resolved == NULL) {
#if defined(DL_ERROR)
        fprintf(stderr, "failed resolve {$name}: %s\\n", DL_ERROR());
#elif defined(CAT_OS_WIN)
        fprintf(stderr, "failed resolve {$name}: %08x\\n", (unsigned int)GetLastError());
#else
        fprintf(stderr, "failed resolve {$name}\\n",());
#endif
        abort();
    }

    {$return};
}
{$ret} (*swow_{$name}_resolved)({$argsSign}) = swow_{$name}_resolver;
{$ret} swow_{$name}_redirect({$argsSign}) {
    {$return};
}


C;
}

function weaks(string $signs): void
{
    // dammy signature parser
    foreach (explode("\n", $signs) as $line) {
        preg_match('/^(?P<ret_name>[^(]+)\((?P<args>.+)\);$/', $line, $match);
        if (!$match) {
            echo "{$line}\n";
            continue;
        }

        $ret_name = $match['ret_name'];
        $argsSign = $match['args'];
        preg_match('/^(?P<type>.*[*\s]+)(?P<name>.+)$/', $ret_name, $match);
        $ret = trim($match['type']) ?: 'void';
        $name = trim($match['name']);

        if ($argsSign === 'void') {
            $argsPassthruSign = '';
        } else {
            $argsPassthru = [];
            foreach (explode(',', $argsSign) as $argSign) {
                preg_match('/^(?P<type>.*[*\s]+)(?P<name>.+)$/', $argSign, $match);
                // $argType = trim($match['$type']);
                $argName = trim($match['name']);
                $argsPassthru[] = $argName;
            }

            $argsPassthruSign = implode(', ', $argsPassthru);
        }

        weak($name, $ret, $argsSign, $argsPassthruSign);
    }
}

weaks(<<<'C'
#ifdef CAT_HAVE_PQ

#ifdef CAT_OS_WIN
# define DL_FROM_HANDLE GetModuleHandleA("libpq.dll")
#else
# define DL_FROM_HANDLE NULL
#endif // CAT_OS_WIN
int  PQbackendPID(const void *conn);
void PQclear(void *res);
char *PQcmdTuples(void *res);
int PQconnectPoll(void *conn);
void *PQconnectStart(const char *conninfo);
int  PQconsumeInput(void *conn);
char *PQerrorMessage(const void *conn);
unsigned char *PQescapeByteaConn(void *conn, const unsigned char *from, size_t from_length, size_t *to_length);
size_t PQescapeStringConn(void *conn, char *to, const char *from, size_t length, int *error);
void PQfinish(void *conn);
int  PQflush(void *conn);
int  PQfmod(const void *res, int field_num);
char *PQfname(const void *res, int field_num);
void PQfreemem(void *ptr);
int  PQfsize(const void *res, int field_num);
unsigned int  PQftable(const void *res, int field_num);
unsigned int  PQftype(const void *res, int field_num);
int  PQgetCopyData(void *conn, char **buffer, int async);
int  PQgetisnull(const void *res, int tup_num, int field_num);
int  PQgetlength(const void *res, int tup_num, int field_num);
void *PQgetResult(void *conn);
char *PQgetvalue(const void *res, int tup_num, int field_num);
int  PQlibVersion(void);
int  PQnfields(const void *res);
int  PQntuples(const void *res);
void *PQnotifies(void *conn);
unsigned int  PQoidValue(const void *res);
const char *PQparameterStatus(const void *conn, const char *paramName);
int  PQprotocolVersion(const void *conn);
int  PQputCopyData(void *conn, const char *buffer, int nbytes);
int  PQputCopyEnd(void *conn, const char *errormsg);
void PQreset(void *conn);
int  PQresetStart(void *conn);
char *PQresultErrorField(const void *res, int fieldcode);
int PQresultStatus(const void *res);
int  PQsendPrepare(void *conn, const char *stmtName, const char *query, int nParams, const unsigned int *paramTypes);
int  PQsendQuery(void *conn, const char *query);
int  PQsendQueryParams(void *conn, const char *command, int nParams,  const unsigned int *paramTypes, const char *const *paramValues, const int *paramLengths, const int *paramFormats, int resultFormat);
int  PQsendQueryPrepared(void *conn, const char *stmtName, int nParams, const char *const *paramValues, const int *paramLengths, const int *paramFormats, int resultFormat);
int  PQsetnonblocking(void *conn, int arg);
void *PQsetNoticeProcessor(void *conn, void *proc, void *arg);
int  PQsocket(const void *conn);
int  PQstatus(const void *conn);
int PQtransactionStatus(const void *conn);
unsigned char *PQunescapeBytea(const unsigned char *strtext, size_t *retbuflen);

int	lo_open(void *conn, unsigned int lobjId, int mode);
int	lo_close(void *conn, int fd);
int	lo_read(void *conn, int fd, char *buf, size_t len);
int	lo_write(void *conn, int fd, const char *buf, size_t len);
int	lo_lseek(void *conn, int fd, int offset, int whence);
long long lo_lseek64(void *conn, int fd, long long offset, int whence);
unsigned int lo_creat(void *conn, int mode);
int	lo_unlink(void *conn, unsigned int lobjId);

#undef DL_FROM_HANDLE
#ifdef CAT_OS_WIN
# define str(x) _str(x)
# define _str(x) #x
# if defined(ZTS) && ZTS
#  define DL_FROM_HANDLE GetModuleHandleA("php" str(PHP_VERSION_MAJOR) "ts.dll")
# else
#  define DL_FROM_HANDLE GetModuleHandleA("php" str(PHP_VERSION_MAJOR) ".dll")
# endif
#else
# define DL_FROM_HANDLE NULL
#endif
bool pdo_get_bool_param(bool *bval, void *value);
void pdo_handle_error(void *dbh, void *stmt);
#if PHP_VERSION_ID < 80100
int pdo_parse_params(void *stmt, void *inquery, size_t inquery_len, void *outquery, void *outquery_len);
#else
int pdo_parse_params(void *stmt, void *inquery, void *outquery);
#endif // PHP_VERSION_ID < 80100
void pdo_throw_exception(unsigned int driver_errcode, char *driver_errmsg, void *pdo_error);
int php_pdo_register_driver(const void *driver);
void php_pdo_unregister_driver(const void *driver);

#endif // CAT_HAVE_PQ
C);
?>
