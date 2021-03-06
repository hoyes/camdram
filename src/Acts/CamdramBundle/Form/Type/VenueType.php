<?php

namespace Acts\CamdramBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VenueType
 *
 * The form that's presented when a user adds/edits a venue
 */
class VenueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('short_name')
            ->add('college', CollegeType::class)
            ->add('description')
            ->add('address')
            ->add('location', MapLocationType::class)
            ->add('facebook_id', FacebookLinkType::class)
            ->add('twitter_id', TwitterLinkType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acts\CamdramBundle\Entity\Venue'
        ));
    }
}
