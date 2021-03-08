<?php namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * @Annotation
 */
class TreeIsEmpty extends Constraint
{
    public $messages = [
        'subcategory' => 'tree.is-empty.subcategory',
        'elements' => 'tree.is-empty.elements'
    ];
    public $related;

    public function __construct($options = null)
    {
        if (!isset($options['related'])) {
            throw new MissingOptionsException(sprintf('Option "related" must be given for constraint %s', __CLASS__),
                ['related']);
        }

        $this->related = $options['related'];
        if (!class_exists($this->related)) {
            throw new InvalidOptionsException(sprintf('Entity %s not found for constraint %s', $this->related,
                __CLASS__),
                ['related']);
        }

        parent::__construct($options);
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
