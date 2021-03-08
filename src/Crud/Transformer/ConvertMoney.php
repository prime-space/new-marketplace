<?php namespace App\Crud\Transformer;

use Ewll\CrudBundle\ReadViewCompiler\Transformer\TransformerInitializerAbstract;

class ConvertMoney extends TransformerInitializerAbstract
{
    private $isView;

    public function __construct(string $fieldName, bool $isView = false)
    {
        parent::__construct($fieldName);
        $this->isView = $isView;
    }

    public function isView(): bool
    {
        return $this->isView;
    }
}
