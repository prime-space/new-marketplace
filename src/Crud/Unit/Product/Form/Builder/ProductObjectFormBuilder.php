<?php namespace App\Crud\Unit\Product\Form\Builder;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Validator\Constraints as Assert;

class ProductObjectFormBuilder
{
    public function build(FormBuilderInterface $builder): void
    {
        $builder
            ->add('data', FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 5000]),
                ]
            ]);
    }
}
