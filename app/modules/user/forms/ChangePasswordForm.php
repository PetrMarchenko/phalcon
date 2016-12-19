<?php

namespace Shark\Module\User\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\Confirmation;

class ChangePasswordForm extends Form
{
    public function initialize()
    {
        $password = new Password(
            "password",
            [
                'class' => "form-control input-md",
                'placeholder' => "password"
            ]
        );
        $password->setLabel('Password');
        $this->add($password);

        $passwordNew = new Password(
            "passwordNew",
            [
                'class' => "form-control input-md",
                'placeholder' => "passwordNew"
            ]
        );
        $passwordNew->setLabel('New Password');
        $this->add($passwordNew);

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
                        "password" => "Password doesn't match confirmation.",
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