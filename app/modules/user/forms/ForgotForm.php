<?php

namespace Shark\Module\User\Forms;

use Phalcon\Forms\Form;
use Phalcon\Validation\Validator\Email;
use Phalcon\Forms\Element\Text;


class ForgotForm extends Form
{
    public function initialize()
    {
        $email = new Text(
            "email",
            [
                'class' => "form-control input-md",
                'placeholder' => "email"
            ]
        );
        $email->setLabel('Email');
        $email->addValidator(
            new Email(
                [
                    "message" => "The email is not valid",
                ]
            )
        );
        $this->add($email);
    }
}