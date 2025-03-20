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

#include "swow_http2.h"
#include "swow_buffer.h"
#include "swow_socket.h"
#include "swow_errno.h"

SWOW_API zend_class_entry *swow_http2_http2_ce;
SWOW_API zend_class_entry *swow_http2_frame_type_ce;
SWOW_API zend_class_entry *swow_http2_error_ce;
SWOW_API zend_class_entry *swow_http2_settings_ce;

SWOW_API zend_class_entry *swow_http2_stream_ce;
SWOW_API zend_object_handlers swow_http2_stream_handlers;

SWOW_API zend_class_entry *swow_http2_session_ce;
SWOW_API zend_object_handlers swow_http2_session_handlers;
SWOW_API zend_class_entry *swow_http2_session_exception_ce;

/* HTTP/2 Frame Type */
static const zend_function_entry swow_http2_frame_type_methods[] = {
    PHP_FE_END
};

/* HTTP/2 Error */
static const zend_function_entry swow_http2_error_methods[] = {
    PHP_FE_END
};

/* HTTP/2 Settings */
static const zend_function_entry swow_http2_settings_methods[] = {
    PHP_FE_END
};

/* HTTP/2 Stream */
static zend_object *swow_http2_stream_create_object(zend_class_entry *ce)
{
    swow_http2_stream_t *s_stream = swow_object_alloc(swow_http2_stream_t, ce, swow_http2_stream_handlers);
    s_stream->stream = NULL;
    return &s_stream->std;
}

static void swow_http2_stream_free_object(zend_object *object)
{
    swow_http2_stream_t *s_stream = swow_http2_stream_get_from_object(object);
    /* nghttp2_stream is managed by nghttp2_session, no need to free here */
    zend_object_std_dtor(&s_stream->std);
}

static const zend_function_entry swow_http2_stream_methods[] = {
    PHP_FE_END
};

/* HTTP/2 Session */
static zend_object *swow_http2_session_create_object(zend_class_entry *ce)
{
    swow_http2_session_t *s_session = swow_object_alloc(swow_http2_session_t, ce, swow_http2_session_handlers);
    s_session->session = NULL;
    return &s_session->std;
}

static void swow_http2_session_free_object(zend_object *object)
{
    swow_http2_session_t *s_session = swow_http2_session_get_from_object(object);
    if (s_session->session != NULL) {
        nghttp2_session_del(s_session->session);
        s_session->session = NULL;
    }
    zend_object_std_dtor(&s_session->std);
}

static const zend_function_entry swow_http2_session_methods[] = {
    PHP_FE_END
};

/* HTTP/2 */
static const zend_function_entry swow_http2_http2_methods[] = {
    PHP_FE_END
};

zend_result swow_http2_module_init(INIT_FUNC_ARGS)
{
    /* HTTP/2 Frame Type */
    swow_http2_frame_type_ce = swow_register_internal_class(
        "Swow\\Http2\\FrameType", NULL, swow_http2_frame_type_methods,
        NULL, ZEND_ACC_FINAL | ZEND_ACC_NO_USER_ARGINFO
    );
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("DATA"), NGHTTP2_DATA);
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("HEADERS"), NGHTTP2_HEADERS);
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("PRIORITY"), NGHTTP2_PRIORITY);
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("RST_STREAM"), NGHTTP2_RST_STREAM);
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("SETTINGS"), NGHTTP2_SETTINGS);
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("PUSH_PROMISE"), NGHTTP2_PUSH_PROMISE);
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("PING"), NGHTTP2_PING);
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("GOAWAY"), NGHTTP2_GOAWAY);
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("WINDOW_UPDATE"), NGHTTP2_WINDOW_UPDATE);
    zend_declare_class_constant_long(swow_http2_frame_type_ce, ZEND_STRL("CONTINUATION"), NGHTTP2_CONTINUATION);

    /* HTTP/2 Error */
    swow_http2_error_ce = swow_register_internal_class(
        "Swow\\Http2\\Error", NULL, swow_http2_error_methods,
        NULL, ZEND_ACC_FINAL | ZEND_ACC_NO_USER_ARGINFO
    );
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("NO_ERROR"), NGHTTP2_NO_ERROR);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("PROTOCOL_ERROR"), NGHTTP2_PROTOCOL_ERROR);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("INTERNAL_ERROR"), NGHTTP2_INTERNAL_ERROR);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("FLOW_CONTROL_ERROR"), NGHTTP2_FLOW_CONTROL_ERROR);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("SETTINGS_TIMEOUT"), NGHTTP2_SETTINGS_TIMEOUT);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("STREAM_CLOSED"), NGHTTP2_STREAM_CLOSED);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("FRAME_SIZE_ERROR"), NGHTTP2_FRAME_SIZE_ERROR);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("REFUSED_STREAM"), NGHTTP2_REFUSED_STREAM);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("CANCEL"), NGHTTP2_CANCEL);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("COMPRESSION_ERROR"), NGHTTP2_COMPRESSION_ERROR);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("CONNECT_ERROR"), NGHTTP2_CONNECT_ERROR);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("ENHANCE_YOUR_CALM"), NGHTTP2_ENHANCE_YOUR_CALM);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("INADEQUATE_SECURITY"), NGHTTP2_INADEQUATE_SECURITY);
    zend_declare_class_constant_long(swow_http2_error_ce, ZEND_STRL("HTTP_1_1_REQUIRED"), NGHTTP2_HTTP_1_1_REQUIRED);

    /* HTTP/2 Settings */
    swow_http2_settings_ce = swow_register_internal_class(
        "Swow\\Http2\\Settings", NULL, swow_http2_settings_methods,
        NULL, ZEND_ACC_FINAL | ZEND_ACC_NO_USER_ARGINFO
    );
    zend_declare_class_constant_long(swow_http2_settings_ce, ZEND_STRL("HEADER_TABLE_SIZE"), NGHTTP2_SETTINGS_HEADER_TABLE_SIZE);
    zend_declare_class_constant_long(swow_http2_settings_ce, ZEND_STRL("ENABLE_PUSH"), NGHTTP2_SETTINGS_ENABLE_PUSH);
    zend_declare_class_constant_long(swow_http2_settings_ce, ZEND_STRL("MAX_CONCURRENT_STREAMS"), NGHTTP2_SETTINGS_MAX_CONCURRENT_STREAMS);
    zend_declare_class_constant_long(swow_http2_settings_ce, ZEND_STRL("INITIAL_WINDOW_SIZE"), NGHTTP2_SETTINGS_INITIAL_WINDOW_SIZE);
    zend_declare_class_constant_long(swow_http2_settings_ce, ZEND_STRL("MAX_FRAME_SIZE"), NGHTTP2_SETTINGS_MAX_FRAME_SIZE);
    zend_declare_class_constant_long(swow_http2_settings_ce, ZEND_STRL("MAX_HEADER_LIST_SIZE"), NGHTTP2_SETTINGS_MAX_HEADER_LIST_SIZE);
    zend_declare_class_constant_long(swow_http2_settings_ce, ZEND_STRL("ENABLE_CONNECT_PROTOCOL"), NGHTTP2_SETTINGS_ENABLE_CONNECT_PROTOCOL);

    /* HTTP/2 Stream */
    swow_http2_stream_ce = swow_register_internal_class(
        "Swow\\Http2\\Stream", NULL, swow_http2_stream_methods,
        swow_http2_stream_create_object, 0
    );
    memcpy(&swow_http2_stream_handlers, &std_object_handlers, sizeof(zend_object_handlers));
    swow_http2_stream_handlers.free_obj = swow_http2_stream_free_object;
    swow_http2_stream_handlers.offset = XtOffsetOf(swow_http2_stream_t, std);

    /* HTTP/2 Session */
    swow_http2_session_ce = swow_register_internal_class(
        "Swow\\Http2\\Session", NULL, swow_http2_session_methods,
        swow_http2_session_create_object, 0
    );
    memcpy(&swow_http2_session_handlers, &std_object_handlers, sizeof(zend_object_handlers));
    swow_http2_session_handlers.free_obj = swow_http2_session_free_object;
    swow_http2_session_handlers.offset = XtOffsetOf(swow_http2_session_t, std);

    /* HTTP/2 Session Exception */
    swow_http2_session_exception_ce = swow_register_internal_class(
        "Swow\\Http2\\SessionException", swow_exception_ce, NULL, NULL, 0
    );

    /* HTTP/2 */
    swow_http2_http2_ce = swow_register_internal_class(
        "Swow\\Http2\\Http2", NULL, swow_http2_http2_methods,
        NULL, ZEND_ACC_FINAL | ZEND_ACC_NO_USER_ARGINFO
    );
    zend_declare_class_constant_stringl(swow_http2_http2_ce, ZEND_STRL("PREFACE"), ZEND_STRL(NGHTTP2_CLIENT_MAGIC));
    zend_declare_class_constant_long(swow_http2_http2_ce, ZEND_STRL("DEFAULT_HEADER_TABLE_SIZE"), NGHTTP2_DEFAULT_HEADER_TABLE_SIZE);
    zend_declare_class_constant_long(swow_http2_http2_ce, ZEND_STRL("DEFAULT_ENABLE_PUSH"), NGHTTP2_DEFAULT_ENABLE_PUSH);
    zend_declare_class_constant_long(swow_http2_http2_ce, ZEND_STRL("DEFAULT_MAX_CONCURRENT_STREAMS"), NGHTTP2_DEFAULT_MAX_CONCURRENT_STREAMS);
    zend_declare_class_constant_long(swow_http2_http2_ce, ZEND_STRL("DEFAULT_INITIAL_WINDOW_SIZE"), NGHTTP2_DEFAULT_INITIAL_WINDOW_SIZE);
    zend_declare_class_constant_long(swow_http2_http2_ce, ZEND_STRL("DEFAULT_MAX_FRAME_SIZE"), NGHTTP2_DEFAULT_MAX_FRAME_SIZE);
    zend_declare_class_constant_long(swow_http2_http2_ce, ZEND_STRL("DEFAULT_MAX_HEADER_LIST_SIZE"), NGHTTP2_DEFAULT_MAX_HEADER_LIST_SIZE);

    return SUCCESS;
}