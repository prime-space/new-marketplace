<?php namespace App\Form\Type;

use App\Entity\CartItem;
use App\Product\CartConstraintFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartItemFixCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productId', FormType\IntegerType::class, [
                'constraints' => CartConstraintFactory::createForProductIdFixCart($options['cartId'])
            ])
            ->add('amount', FormType\IntegerType::class, [
                'constraints' => CartConstraintFactory::createForAmount(true, 1),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CartItem::class,
            'cartId' => null,
        ]);
    }
}
