<?php namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Unique extends Constraint
{
    public $message;

    private $className;
    private $fieldName;
    private $filters;
    private $excludeId;

    public function __construct(
        string $className,
        string $fieldName,
        array $filters = [],
        int $excludeId = null,
        $message = 'unique'
    ) {
        parent::__construct();
        $this->className = $className;
        $this->fieldName = $fieldName;
        $this->filters = $filters;
        $this->excludeId = $excludeId;
        $this->message = $message;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getExcludeId(): ?int
    {
        return $this->excludeId;
    }
}
