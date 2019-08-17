<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Form\DataMapper;

use HTC\StrictFormMapper\Contract\ValueVoterInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;
use Traversable;
use TypeError;
use function iterator_to_array;
use function strpos;
use function array_search;

class StrictFormMapper implements DataMapperInterface
{
    private $defaultMapper;

    /** @var iterable|ValueVoterInterface[] */
    private $voters;

    private $translator;

    public function __construct(DataMapperInterface $defaultMapper, $voters, ?TranslatorInterface $translator)
    {
        $this->defaultMapper = $defaultMapper;
        $this->voters = $voters;
        $this->translator = $translator;
    }

    public function mapDataToForms($data, $forms): void
    {
        /** @var FormInterface[]|Traversable $unmappedForms */
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

    public function mapFormsToData($forms, &$data): void
    {
        /** @var FormInterface[]|Traversable $unmappedForms */
        $unmappedForms = [];

        foreach ($forms as $form) {
            if (!$this->writeFormValueToData($form, $data)) {
                $unmappedForms[] = $form;
            }
        }

        $this->defaultMapper->mapFormsToData($unmappedForms, $data);
    }

    /**
     * Try to write value from form to data.
     * If 'update_value' is not defined, returns false indicating that default mapper should give it a try.
     */
    private function writeFormValueToData(FormInterface $form, &$data): bool
    {
        $config = $form->getConfig();
        /** @var callable|null $reader */
        $reader = $config->getOption('get_value');
        if (!$reader) {
            return false;
        }

        $updater = $config->getOption('update_value');
        $adder = $config->getOption('add_value');
        $remover = $config->getOption('remove_value');

        $isMultiple = $config->getOption('multiple');

        try {
            $originalValues = $reader($data);
        } catch (TypeError $e) {
            $originalValues = $isMultiple ? [] : null;
        }

        $submittedValue = $form->getData();
        try {
            if ($updater) {
                $updater($submittedValue, $data);
            } else {
                $addedValues = $this->getExtraValues($originalValues, $submittedValue);
                $removedValues = $this->getExtraValues($submittedValue, $originalValues);

                foreach ($addedValues as $value) {
                    $adder($value, $data);
                }
                foreach ($removedValues as $value) {
                    $remover($value, $data);
                }
            }
        } catch (TypeError $e) {
            // Second argument is typehinted data object.
            // We are not interested if exception happens on it; it means 'factory' failed and it is parent-level error message.
            if (false !== strpos($e->getMessage(), 'Argument 2 passed to')) {
                return true;
            }

            // if there is NotNull constraint on this field, we don't need custom error message; Symfony will take care of it
            if (null === $submittedValue && $this->doesFormHaveNotNullConstraint($config)) {
                return true;
            }

            $errorMessage = $config->getOption('write_error_message');
            if ($errorMessage) {
                $translatedMessage = $this->translator ? $this->translator->trans($errorMessage) : $errorMessage;
                $form->addError(new FormError($translatedMessage, null, [], null, $e));
            }
        }

        return true;
    }

    private function getExtraValues(iterable $originalValues, array $submittedValues): array
    {
        if ($originalValues instanceof Traversable) {
            $originalValues = iterator_to_array($originalValues, true);
        }

        $extraValues = [];
        foreach ($submittedValues as $key => $value) {
            $searchKey = array_search($value, $originalValues, true);

            if (false === $searchKey || $key !== $searchKey || !$this->isEqual($submittedValues[$searchKey], $value)) {
                $extraValues[$key] = $value;
            }
        }

        return $extraValues;
    }

    private function isEqual($first, $second): bool
    {
        return $first === $second;
    }

    private function doesFormHaveNotNullConstraint(FormConfigInterface $config): bool
    {
        $constraints = $config->getOption('constraints');
        foreach ($constraints as $constraint) {
            if ($constraint instanceof NotNull) {
                return true;
            }
        }

        return false;
    }
}
