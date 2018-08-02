<?php

namespace ScyLabs\NeptuneBundle\Form;

use ScyLabs\NeptuneBundle\Entity\Element;
use ScyLabs\NeptuneBundle\Entity\Page;
use ScyLabs\NeptuneBundle\Entity\PageType;
use ScyLabs\NeptuneBundle\Entity\ElementType;
use ScyLabs\NeptuneBundle\Repository\PageRepository;

use ScyLabs\NeptuneBundle\Repository\PageTypeRepository;
use ScyLabs\NeptuneBundle\Repository\ElementTypeRepository;
use Doctrine\ORM\Mapping\Entity;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;
class ElementForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name',TextType::class,[
                'label'=>'Nom de la Element'
            ]);

        if($options['action'] !== null){
            $builder->setAction($options['action']);
        }
        $builder->add('type',EntityType::class,[
            'label'         => 'Type de Element',
            'class'         => ElementType::class,
            'choice_label'  => 'title',
            'query_builder' =>  function(ElementTypeRepository $r){
                return $r->createQueryBuilder('t')
                    ->where('t.remove = 0');
            },
        ])
            ->add('submit',SubmitType::class,[
                'label' => 'Envoyer'
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA,function(FormEvent $event){
            $Element = $event->getData();

            if(null === $Element){
                return;
            }

            if($Element->getPage() !== null){
                $event->getForm()->add('page',HiddenType::class,[
                    'property_path' => 'page.name',
                ]);
            }
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'action' => null,
            'data_class' => Element::class,
        ])
        ;
    }
}
