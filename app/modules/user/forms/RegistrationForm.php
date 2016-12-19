<?php

namespace Shark\Module\User\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Confirmation;
use Shark\Module\User\Models\Users;

class RegistrationForm extends Form
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
                    "messageMaximum" => "Maximum value is 50.",
                    "messageMinimum" => "Minimum value is 2.",
                ]
            )
        );
        $this->add($name);



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
                    "message" => "The email is not valid.",
                ]
            )
        );
        $email->addValidator(
            new Uniqueness(
                [
                    "model"     => new Users(),
                    "attribute" => "email",
                    "message" => "The email is not uniqueness.",
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

        $passwordRepeat = new Password(
            "passwordRepeat",
            [
                'class' => "form-control input-md",
                'placeholder' => "Repeat password"
            ]
        );
        $passwordRepeat->setLabel('Repeat password');
        $passwordRepeat->addValidator(
            new Confirmation(
                [
                    "message" => [
                        "password" => "Password doesn't match confirmation",
                    ],
                    "with" => [
                        "passwordRepeat" => "password",
                    ],
                ]
            )
        );
        $this->add($passwordRepeat);
    }
}