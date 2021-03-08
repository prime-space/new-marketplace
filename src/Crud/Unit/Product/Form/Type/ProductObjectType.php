<?php namespace App\Crud\Unit\Product\Form\Type;

use App\Crud\Unit\Product\Form\Builder\ProductObjectFormBuilder;
use App\Entity\ProductObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $productObjectFormBuilder = new ProductObjectFormBuilder();
        $productObjectFormBuilder->build($builder);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductObject::class,
        ]);
    }
}
