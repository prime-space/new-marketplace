<?php namespace App\Form\DataTransformer;

use Ewll\DBBundle\Repository\RepositoryProvider;
use App\Entity\Customer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CustomerToEmailTransformer implements DataTransformerInterface
{
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function transform($customer)
    {
        /** @var Customer|null $customer */
        if (null === $customer) {
            return null;
        }

        return $customer->email;
    }

    public function reverseTransform($email)
    {
        if (null === $email) {
            return null;
        }

        $customer = $this->repositoryProvider->get(Customer::class)->findOneBy(['email' => $email]);

        if (null === $customer) {
            $failure = new TransformationFailedException('Customer not found');
            $failure->setInvalidMessage('customer.email-have-not-paid-orders');

            throw $failure;
        }

        return $customer;
    }
}
