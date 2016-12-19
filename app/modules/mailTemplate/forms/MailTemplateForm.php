<?php

namespace Shark\Module\MailTemplate\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\StringLength;

class MailTemplateForm extends Form
{

    public function initialize()
    {
        $name = new Text(
            "name",
            [
                'class' => "form-control input-md",
                'placeholder' => "name"
            ]
        );
        $name->setLabel('Name');
        $name->addValidator(
            new StringLength(
                [
                    "max"            => 50,
                    "min"            => 2,
                    "messageMaximum" => "Maximum value is 50",
                    "messageMinimum" => "Minimum value is 2",
                ]
            )
        );
        $this->add($name);


        $subject = new Text(
            "subject",
            [
                'class' => "form-control input-md",
                'placeholder' => "name"
            ]
        );
        $subject->setLabel('Subject');
        $subject->addValidator(
            new StringLength(
                [
                    "max"            => 50,
                    "min"            => 2,
                    "messageMaximum" => "Maximum value is 50",
                    "messageMinimum" => "Minimum value is 2",
                ]
            )
        );
        $this->add($subject);

        $body = new TextArea(
            "body",
            [
                'class' => "form-control input-md",
                'placeholder' => "name"
            ]
        );
        $body->setLabel('Body');
        $body->addValidator(
            new StringLength(
                [
                    "max"            => 50,
                    "min"            => 2,
                    "messageMaximum" => "Maximum value is 50",
                    "messageMinimum" => "Minimum value is 2",
                ]
            )
        );
        $this->add($body);
    }
}