<?php
/*
 * Copyright REZO ZERO 2014
 *
 *
 * @file DocumentsType.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace RZ\Renzo\CMS\Forms;

use RZ\Renzo\Core\Kernel;
use RZ\Renzo\Core\Entities\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Document selector and uploader form field type.
 */
class DocumentsType extends AbstractType
{
    protected $selectedDocuments;

    /**
     * {@inheritdoc}
     *
     * @param array $documents Array of Document instances
     */
    public function __construct(array $documents)
    {
        $this->selectedDocuments = $documents;
    }

    /**
     * {@inheritdoc}
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $callback = function ($object, ExecutionContextInterface $context) {

            $document = Kernel::getService('em')
                            ->find('RZ\Renzo\Core\Entities\Document', (int) $object);

            // Vérifie si le nom est bidon
            if (null !== $object && null === $document) {
                $context->addViolationAt(
                    null,
                    'Document '.$object.' does not exists',
                    array(),
                    null
                );
            }
        };

        $resolver->setDefaults(array(
            'class' => '\RZ\Renzo\Core\Entities\Document',
            'multiple' => true,
            'property' => 'id',
            'constraints' => array(
                new Callback($callback)
            )
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        /*
         * Inject data as plain documents entities
         */
        $view->vars['data'] = $this->selectedDocuments;
    }
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'documents';
    }
}
