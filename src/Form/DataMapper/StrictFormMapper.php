<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Form\DataMapper;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormError;
use TypeError;
use function iterator_to_array;

class StrictFormMapper implements DataMapperInterface
{
    public function __construct()
    {
    }

    public function mapDataToForms($data, $forms): void
    {
        $forms = iterator_to_array($forms);
        foreach ($forms as $form) {
            if ($reader = $form->getConfig()->getOption('get_value')) {
                try {
                    $value = $reader($data);
                    $form->setData($value);
                } catch (TypeError $e) {
                    $form->setData(null);
                }
            } else {
                dd(123);
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$data): void
    {
        $forms = iterator_to_array($forms);
        foreach ($forms as $form) {
            $config = $form->getConfig();
            if ($writer = $config->getOption('update_value')) {
                try {
                    $writer($data, $form->getData());
                } catch (TypeError $e) {
                    $errorMessage = $config->getOption('write_error_message');
                        $form->addError(new FormError($errorMessage, null, [], null, $e));
                }
            }
        }
    }
}
