<?php

namespace PandawanTechnology\Neo4jBundle\Form\Type;

use GraphAware\Neo4j\Client\Client as Neo4jClient;
use PandawanTechnology\Neo4jBundle\Form\ChoiceList\IdReader;
use PandawanTechnology\Neo4jBundle\Form\ChoiceList\Neo4jChoiceLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Neo4jType extends AbstractType
{
    /**
     * @var Neo4jClient
     */
    protected $neo4jClient;

    /**
     * @var ChoiceListFactoryInterface
     */
    protected $choiceListFactory;

    /**
     * @var IdReader
     */
    protected $idReader;

    /**
     * Creates the field name for a choice.
     *
     * This method is used to generate field names if the underlying object has
     * a single-column integer ID. In that case, the value of the field is
     * the ID of the object. That ID is also used as field name.
     *
     * @param object     $choice The object.
     * @param int|string $key    The choice key.
     * @param string     $value  The choice value. Corresponds to the object's
     *                           ID here.
     *
     * @return string The field name.
     *
     * @internal This method is public to be usable as callback. It should not
     *           be used in user code.
     */
    public static function createChoiceName($choice, $key, $value)
    {
        return str_replace('-', '_', (string) $value);
    }

    /**
     * @param Neo4jClient                $neo4jClient
     * @param PropertyAccessorInterface  $propertyAccessor
     * @param ChoiceListFactoryInterface $choiceListFactory
     */
    public function __construct(
        Neo4jClient $neo4jClient,
        PropertyAccessorInterface $propertyAccessor = null,
        ChoiceListFactoryInterface $choiceListFactory = null
    ) {
        $this->neo4jClient = $neo4jClient;
        $this->choiceListFactory = $choiceListFactory ?: new CachingFactoryDecorator(
            new PropertyAccessDecorator(
                new DefaultChoiceListFactory(),
                $propertyAccessor
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choiceLoader = function (Options $options) {
            if (null !== $options['choices']) {
                return;
            }

            return new Neo4jChoiceLoader(
                $this->neo4jClient,
                $this->choiceListFactory,
                $options['statement'],
                $options['item_alias'],
                $options['id_reader']
            );
        };

        $idReaderNormalizer = function () {
            if (!$this->idReader) {
                $this->idReader = new IdReader();
            }

            return $this->idReader;
        };

        $choiceValue = function (Options $options) {
            return [$options['id_reader'], 'getIdValue'];
        };

        $resolver->setRequired([
            'statement',
            'item_alias',
            'choice_label',
        ]);

        $resolver->setDefaults([
            'expanded' => false,
            'multiple' => false,
            'choice_translation_domain' => false,
            'choices' => null,
            'choice_loader' => $choiceLoader,
            'choice_name' => [__CLASS__, 'createChoiceName'],
            'choice_value' => $choiceValue,
            'id_reader' => null, // internal
        ]);

        $resolver->setAllowedTypes('statement', 'string');
        $resolver->setAllowedTypes('item_alias', 'string');
        $resolver->setAllowedTypes('choice_label', 'string');

        $resolver->setNormalizer('id_reader', $idReaderNormalizer);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
    }
}
