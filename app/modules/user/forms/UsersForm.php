<?php

namespace Shark\Module\User\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Uniqueness;
use Shark\Module\User\Models\Users;
use Shark\Module\User\Models\Roles;

class UsersForm extends Form
{

    public function initialize($user)
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
                    "model"     => $user,
                    "attribute" => "email",
                    "message" => "The email is not uniqueness.",
                ]
            )
        );
        $this->add($email);

        $status = new Select(
            "status",
            [
                Users::STATUS_CREATED  => "CREATED",
                Users::STATUS_ACTIVE  => "ACTIVE",
                Users::STATUS_BLOCKED  => "BLOCKED",
            ]
        );
        $status->setLabel('Status');
        $this->add($status);


        $role = new Select(
            "roleId",
            Roles::find(),
            [
                "using"      => [
                    "id",
                    "key",
                ],
            ]
        );
        $role->setLabel('Role');
        $this->add($role);
    }
}