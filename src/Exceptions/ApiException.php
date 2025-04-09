<?php

namespace Wilfreedi\ApiFinder\Exceptions;

use Throwable;

/**
 * Базовое исключение для ошибок API
 * Выбрасывается при общих ошибках API, ошибках сервера агрегатора,
 * невалидных ответах или других проблемах, не связанных с аутентификацией.
 */
class ApiException extends \RuntimeException
{
    /**
     * Дополнительные данные или контекст ошибки (например, тело ответа).
     * @var mixed|null
     */
    protected $context;

    /**
     * Создает экземпляр ApiException.
     *
     * @param string $message Сообщение об ошибке.
     * @param int $code HTTP статус-код или внутренний код ошибки (0 если неизвестно).
     * @param Throwable|null $previous Предыдущее исключение (для цепочки).
     * @param mixed|null $context Дополнительный контекст ошибки.
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, $context = null) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Возвращает дополнительный контекст ошибки.
     *
     * @return mixed|null
     */
    public function getContext() {
        return $this->context;
    }
}