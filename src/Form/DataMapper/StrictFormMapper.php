<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Form\DataMapper;

use function strpos;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormError;
use TypeError;

class StrictFormMapper implements DataMapperInterface
{
    private $defaultMapper;

    public function __construct(DataMapperInterface $defaultMapper)
    {
        $this->defaultMapper = $defaultMapper;
    }

    public function mapDataToForms($data, $forms): void
    {
        $unmappedForms = [];

        foreach ($forms as $form) {
            $reader = $form->getConfig()->getOption('get_value');
            if (!$reader) {
                $unmappedForms[] = $form;
            } else {
                try {
                    $value = $reader($data);
                    $form->setData($value);
                } catch (TypeError $e) {
                    $form->setData(null);
                }
            }
        }

        $this->defaultMapper->mapDataToForms($data, $unmappedForms);
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$data): void
    {
        $unmappedForms = [];

        foreach ($forms as $form) {
            $config = $form->getConfig();
            $writer = $config->getOption('update_value');
            if (!$writer) {
                $unmappedForms[] = $form;
            } else {
                try {
                    $writer($form->getData(), $data);
                } catch (TypeError $e) {
                    // Second argument is typehinted data object.
                    // We are not interested if exception happens on it; it means 'factory' failed and it is at top-level error message.
                    if (strpos($e->getMessage(), 'Argument 2') === false) {
                        $errorMessage = $config->getOption('write_error_message');
                        if ($errorMessage) {
                            $form->addError(new FormError($errorMessage, null, [], null, $e));
                        }
                    }
                }
            }
        }

        $this->defaultMapper->mapFormsToData($unmappedForms, $data);
    }
}
