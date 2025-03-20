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
  | Author: Twosee <twosee@php.net>                                          |
  +--------------------------------------------------------------------------+
 */

#ifndef SWOW_HTTP2_H
#define SWOW_HTTP2_H
#ifdef __cplusplus
extern "C" {
#endif

#include "swow.h"
#include "swow_http.h"

#include "cat_http.h"
#include "nghttp2/nghttp2.h"

extern SWOW_API zend_class_entry *swow_http2_http2_ce;
extern SWOW_API zend_class_entry *swow_http2_frame_type_ce;
extern SWOW_API zend_class_entry *swow_http2_error_ce;
extern SWOW_API zend_class_entry *swow_http2_settings_ce;

extern SWOW_API zend_class_entry *swow_http2_stream_ce;
extern SWOW_API zend_object_handlers swow_http2_stream_handlers;

extern SWOW_API zend_class_entry *swow_http2_session_ce;
extern SWOW_API zend_object_handlers swow_http2_session_handlers;
extern SWOW_API zend_class_entry *swow_http2_session_exception_ce;

/* HTTP/2 Stream */
typedef struct swow_http2_stream_s {
    nghttp2_stream *stream;
    zend_object std;
} swow_http2_stream_t;

/* HTTP/2 Session */
typedef struct swow_http2_session_s {
    nghttp2_session *session;
    zend_object std;
} swow_http2_session_t;

/* loader */
zend_result swow_http2_module_init(INIT_FUNC_ARGS);

/* helper */
static zend_always_inline swow_http2_stream_t *swow_http2_stream_get_from_object(zend_object *object)
{
    return cat_container_of(object, swow_http2_stream_t, std);
}

static zend_always_inline swow_http2_session_t *swow_http2_session_get_from_object(zend_object *object)
{
    return cat_container_of(object, swow_http2_session_t, std);
}

#ifdef __cplusplus
}
#endif
#endif /* SWOW_HTTP2_H */