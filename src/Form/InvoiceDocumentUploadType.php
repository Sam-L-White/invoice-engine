<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

final class InvoiceDocumentUploadType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('document', FileType::class, [
                'label' => 'Invoice document',
                'constraints' => [
                    new NotNull(
                        message: 'Please choose an invoice document.',
                    ),
                    new File(
                        maxSize: '10M',
                        filenameMaxLength: 255,
                        filenameTooLongMessage: 'The filename must be 255 characters or less.',
                        extensions: ['pdf', 'jpg', 'jpeg', 'png'],
                        extensionsMessage: 'Please upload a valid PDF, JPEG or PNG document.',
                    ),
                ],
            ]);
    }
}
