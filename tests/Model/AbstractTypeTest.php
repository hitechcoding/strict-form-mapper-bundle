<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Tests\Model;

use HTC\StrictFormMapper\Form\Extension\StrictFormTypeExtension;
use HTC\StrictFormMapper\Voter\DateTimeVoter;
use Symfony\Component\Form\Test\TypeTestCase;

abstract class AbstractTypeTest extends TypeTestCase
{
    protected function getTypeExtensions(): array
    {
        return [
            new StrictFormTypeExtension([
                new DateTimeVoter(),
            ], null),
        ];
    }
}
