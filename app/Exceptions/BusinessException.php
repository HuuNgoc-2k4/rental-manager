<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class BusinessException extends Exception
{
    /**
     * Mã lỗi nghiệp vụ (tùy chọn) để FE nhận biết case cụ thể mà không so khớp chuỗi message.
     */
    protected ?string $errorCode;

    /**
     * @param string $message Text hiển thị thẳng cho người dùng
     * @param string|null $errorCode Mã lỗi nghiệp vụ để FE rẽ nhánh (vd 'blocked_account')
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', ?string $errorCode = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }

    /**
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }
}
