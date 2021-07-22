<?php

namespace Activity\Form;

use DateTime;
use Exception;
use Laminas\Form\Fieldset;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Mvc\I18n\Translator;
use Laminas\Validator\Callback;

class SignupList extends Fieldset implements InputFilterProviderInterface
{
    private Translator $translator;

    public function __construct(Translator $translator)
    {
        parent::__construct('signuplist');
        $this->setHydrator(new ClassMethodsHydrator(false))
            ->setObject(new \Activity\Model\SignupList());

        $this->add(
            [
                'name' => 'name',
                'attributes' => [
                    'type' => 'text',
                ],
                'options' => [
                    'label' => $translator->translate('Name'),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'nameEn',
                'attributes' => [
                    'type' => 'text',
                ],
                'options' => [
                    'label' => $translator->translate('Name'),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'openDate',
                'type' => 'datetime',
                'options' => [
                    'format' => 'Y/m/d H:i',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'closeDate',
                'type' => 'datetime',
                'options' => [
                    'format' => 'Y/m/d H:i',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'onlyGEWIS',
                'type' => 'Laminas\Form\Element\Checkbox',
                'options' => [
                    'checked_value' => 1,
                    'unchecked_value' => 0,
                ],
                'attributes' => [
                    'value' => 1,
                ],
            ]
        );

        $this->add(
            [
                'name' => 'displaySubscribedNumber',
                'type' => 'Laminas\Form\Element\Checkbox',
                'options' => [
                    'checked_value' => 1,
                    'unchecked_value' => 0,
                ],
            ]
        );

        $this->add(
            [
                'name' => 'fields',
                'type' => 'Laminas\Form\Element\Collection',
                'options' => [
                    'count' => 0,
                    'should_create_template' => true,
                    'template_placeholder' => '__signuplist_field__',
                    'allow_add' => true,
                    'target_element' => new SignupListField($translator),
                ],
            ]
        );
        $this->translator = $translator;
    }

    /**
     * Check if a certain date is before the closing date of the SignupList.
     *
     * @param string $value
     * @param array $context
     *
     * @return bool
     */
    public static function beforeCloseDate($value, $context = [])
    {
        try {
            $thisTime = new DateTime($value);
            $closeTime = isset($context['closeDate']) ? new DateTime($context['closeDate']) : new DateTime('now');

            return $thisTime < $closeTime;
        } catch (Exception $e) {
            // An exception is an indication that one of the times was not valid
            return false;
        }
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'name' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 100,
                        ],
                    ],
                ],
            ],
            'nameEn' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 100,
                        ],
                    ],
                ],
            ],
            'openDate' => [
                'required' => true,
                // TODO: Move to an actual InputFilter
                // The validator below does not work, as the $context in
                // Activity\Form\Activity::beforeBeginTime is the context
                // of this FieldSet and not the parent form.
                //
                // This means that the `beginTime`-index does not exist and
                // as a result any `closeDate` in the future does not validate
                // correctly.
                //
                // An separate InputFilter should be made for the parent form
                // to validate any and all child forms.
                //
                'validators' => [
                    //[
                    //    'name' => \Laminas\Validator\Callback::class,
                    //    'options' => [
                    //        'messages' => [
                    //            \Laminas\Validator\Callback::INVALID_VALUE =>
                    //                $this->translator->translate('The sign-up list opening date and time must be before the activity starts.'),
                    //        ],
                    //        'callback' => ['Activity\Form\Activity', 'beforeBeginTime'],
                    //    ],
                    //],
                    [
                        'name' => Callback::class,
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => $this->translator->translate(
                                    'The sign-up list opening date and time must be before the sign-up list closes.'
                                ),
                            ],
                            'callback' => [$this, 'beforeCloseDate'],
                        ],
                    ],
                ],
            ],
            'closeDate' => [
                'required' => true,
                // TODO: Move to an actual InputFilter
                // The validator below does not work, as the $context in
                // Activity\Form\Activity::beforeBeginTime is the context
                // of this FieldSet and not the parent form.
                //
                // This means that the `beginTime`-index does not exist and
                // as a result any `closeDate` in the future does not validate
                // correctly.
                //
                // An separate InputFilter should be made for the parent form
                // to validate any and all child forms.
                //
                //'validators' => [
                //    [
                //        'name' => \Laminas\Validator\Callback::class,
                //        'options' => [
                //            'messages' => [
                //                \Laminas\Validator\Callback::INVALID_VALUE =>
                //                    $this->translator->translate('The sign-up list closing date and time must be before the activity starts.'),
                //            ],
                //            'callback' => ['Activity\Form\Activity', 'beforeBeginTime'],
                //        ],
                //    ],
                //],
            ],
        ];
    }
}