<?php

namespace {
    require_once 'vendor/autoload.php';
}

namespace PHPUnit\Framework\Attributes {
    use Attribute;

    if (!class_exists(CoversTrait::class)) {
        #[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
        final readonly class CoversTrait
        {
            private string $className;

            public function __construct(string $className)
            {
                $this->className = $className;
            }

            public function className(): string
            {
                return $this->className;
            }
        }
    }
}
