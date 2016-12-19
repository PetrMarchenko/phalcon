<?php

namespace Shark\Module\User\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Email;
use Phalcon\Forms\Element\Password;

class LoginForm extends Form
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

        $password = new Password(
            "password",
            [
                'class' => "form-control input-md",
                'placeholder' => "password"
            ]
        );
        $password->setLabel('Password');
        $this->add($password);

    }
}