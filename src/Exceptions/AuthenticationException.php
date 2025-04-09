<?php

namespace Wilfreedi\ApiFinder\Exceptions;

/**
 * Исключение для ошибок аутентификации или авторизации с API
 * Обычно выбрасывается при получении HTTP статусов 401 (Unauthorized) или 403 (Forbidden).
 */
class AuthenticationException extends ApiException {}