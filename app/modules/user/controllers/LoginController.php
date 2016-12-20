<?php

namespace Shark\Module\User\Controller;

use Phalcon\Security;
use Shark\Library\Controller\ControllerBase;
use Shark\Library\Exception\SharkNotFoundException;
use Shark\Library\Exception\SharkServerErrorException;
use Shark\Module\MailTemplate\Models\MailTemplates;
use Shark\Module\User\Forms\ChangePasswordForm;
use Shark\Module\User\Forms\ForgotForm;
use Shark\Module\User\Forms\LoginForm;
use Shark\Module\User\Models\Users;
use Shark\Module\User\Models\UsersAuth;
use Shark\Module\User\Forms\RegistrationForm;
use Shark\Module\User\Models\UsersRoles;
use Phalcon\Security\Random;

/**
 * Class LoginController
 * @RoutePrefix("/login")
 */
class LoginController extends ControllerBase
{
    /**
     * @Route("/logout", "name" = "logout")
     */
    public function logoutAction()
    {
        $this->session->destroy();

        $this->flash->success('\'Logout successful.\'');

        return $this->response->redirect(array('for' => 'login'));
    }

    /**
     * @Route("/", "name" = "login")
     */
    public function loginAction()
    {
        $loginForm = new LoginForm();
        $this->view->form = $loginForm;
        $this->response->setStatusCode(401, 'Authorization required.');
    }

    /**
     * @Route("/auth", "name" = "login_auth")
     */
    public function authAction()
    {
        if ($this->request->isPost()) {

            $login = $this->request->getPost('email');
            $password = sha1($this->request->getPost('password'));

            $user = Users::findFirst([
                "email = :login: AND status = :status:",
                "bind" => ['login' => $login, 'status' => Users::STATUS_ACTIVE]
            ]);

            if ($user) {
                $auth = UsersAuth::findFirst([
                    "userId = :userId: AND password = :password:",
                    "bind" => ['userId' => $user->id, 'password' => $password]
                ]);

                if ($auth) {
                    $this->session->set("auth", $user);
                    $this->flash->success('Welcome ' . $user->name);

                    return $this->response->redirect(array('for' => 'home'));
                }

                $this->flash->error('\'Incorrect email or password.\'');

            }
        }

        return $this->dispatcher->forward([
            "controller" => "login",
            "action" => "login"
        ]);
    }

    /**
     * @Route("/registration", "name" = "login_registration")
     */
    public function registrationAction()
    {
        $registrationForm = new RegistrationForm();

        if ($this->request->isPost()) {
            if (!$registrationForm->isValid($this->request->getPost())) {
                return $this->view->form = $registrationForm;
            }

            $this->dbMaster->begin();
            $isRegistration = Users::created($this->request->getPost());
            if (!$isRegistration) {
                $this->dbMaster->rollback();
                $this->flash->error('\'User does not create.\'');
                return $this->view->form = $registrationForm;
            }

            try {
                /**
                 * @var MailTemplates $templates
                 */
                $templates = MailTemplates::findFirstByKey('REGISTRATION');
                if (!$templates) {
                    throw new SharkServerErrorException();
                }

                $link = $this->request->getScheme(). '://' . $this->request->getHttpHost();
                $link .= $this->getDI()->getShared('url')->get([
                    'for' => 'login_active',
                    'key' => sha1($registrationForm->get('password')->getValue())
                ]);

                $templates->replace([
                    'name' => $isRegistration->name,
                    'link' => $link
                ]);

                $mailer = \Phalcon\DI::getDefault()->getShared('mailer');
                $config = \Phalcon\DI::getDefault()->getShared('config');
                $message = new \Swift_Message();
                $message->setSubject($templates->subject);
                $message->setBody($templates->body);
                $message->setContentType("text/html");
                $message->setTo($isRegistration->email);
                $message->setFrom($config->mail->from);

                $mailer->send($message);
                $this->dbMaster->commit();
                $this->flash->success('\'Please, check your email to confirm registration.\'');
                return $this->response->redirect();
            } catch (\Exception $e) {
                $this->dbMaster->rollback();
                $this->flash->error('\'User does not create.\'');
            }
        }

        $this->view->form = $registrationForm;
    }

    /**
     * @Route("/active/{key}", "name" = "login_active")
     */
    public function activeAction($key)
    {
        $usersAuth = UsersAuth::findFirstByPassword($key);
        if (!$usersAuth) {
            throw new SharkServerErrorException('\'This user do not found.\'');
        }

        $user = Users::findFirstById($usersAuth->userId);
        if (!$user) {
            throw new SharkServerErrorException('\'This user do not found.\'');
        }

        if (Users::STATUS_CREATED != $user->status) {
            $this->flash->error('\'This link was used.\'');
            return $this->response->redirect();
        }

        $user->status = Users::STATUS_ACTIVE;
        $user->save();
        $this->flash->success('\'Activation successful.\'');
        return $this->response->redirect();
    }

    /**
     * @Route("/change", "name" = "login_change_password")
     */
    public function changePasswordAction()
    {
        $changePasswordForm = new ChangePasswordForm();
        if ($this->request->isPost()) {
            if (!$changePasswordForm->isValid($this->request->getPost())) {
                return $this->view->form = $changePasswordForm;
            }

            /**
             * @var Users $user
             */
            $user = $this->session->get('auth');
            $usersAuth = $user->usersAuth;
            $password = $usersAuth->password;

            if ($password !== sha1($this->request->getPost('password'))) {
                $this->flash->error('\'Check the old password.\'');
                return $this->view->form = $changePasswordForm;
            }

            $passwordNew = $this->request->getPost('passwordNew');

            $usersAuth->password = sha1($passwordNew);
            if(!$usersAuth->save()) {
                $this->flash->error('\'Password not changed.\'');
                return $this->view->form = $changePasswordForm;
            }

            $this->flash->success('\'Password successful changed.\'');
            return $this->response->redirect();
        }

        $this->view->form = $changePasswordForm;

    }

    /**
     * @Route("/forgot", "name" = "login_forgot")
     */
    public function forgotAction()
    {
        /**
         * @var Users $user
         */
        $user = $this->session->get('auth');
        $usersAuth = UsersAuth::findFirstByUserId($user->id);
        if (!$user) {
            $this->flash->error('\'This user does not found.\'');
            return $this->response->redirect(['for' => 'user_show']);
        }

        $forgotForm = new ForgotForm();

        if ($this->request->isPost()) {
            if ($forgotForm->isValid()) {
                $random = new Random();
                $password = $random->base64Safe(4);
                $usersAuth->password = $password;
                $this->dbMaster->begin();
                $usersAuth->save();

                try {
                    /**
                     * @var MailTemplates $templates
                     */
                    $templates = MailTemplates::findFirstByKey('CHANGE_PASSWORD');
                    if (!$templates) {
                        throw new SharkServerErrorException('\'This template do not found.\'');
                    }
                    $templates->replace([
                        'password' => $password
                    ]);

                    $mailer = \Phalcon\DI::getDefault()->getShared('mailer');
                    $config = \Phalcon\DI::getDefault()->getShared('config');
                    $message = new \Swift_Message();
                    $message->setSubject($templates->subject);
                    $message->setBody($templates->body);
                    $message->setContentType("text/html");
                    $message->setTo($user->email);
                    $message->setFrom($config->mail->from);
                    $mailer->send($message);

                    /**
                     * Commit transaction.
                     */
                    $this->dbMaster->commit();
                    $this->flash->success('\'Please check your email.\'');
                    return $this->response->redirect();
                } catch (\Exception $e) {
                    $this->dbMaster->rollback();
                    $this->flash->error('\'Password does not recover.\'');
                }
            }
        }
        $this->view->form = $forgotForm;
    }
}