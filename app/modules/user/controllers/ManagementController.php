<?php

namespace Shark\Module\User\Controller;

use Shark\Library\Controller\ControllerBase;
use Shark\Module\User\Forms\UsersForm;
use Shark\Module\User\Grid\UserManager;
use Shark\Module\User\Models\Users;
use Shark\Module\MailTemplate\Models\MailTemplates;
use Phalcon\Security\Random;

/**
 * Class ManagementController
 * @RoutePrefix("/user")
 */
class ManagementController extends ControllerBase
{

    /**
     * @Route("/", "name" = "user_show")
     */
    public function indexAction()
    {
        \Phalcon\Tag::prependTitle('User management | ');
        $this->gridDefaults['sortBy'] = 'user.id';
        $this->gridDefaults['sortDir'] = 'asc';

        $grid = new UserManager();
        $params = $this->getGridParams();
        $grid->setQueryParams($params);


        if ($this->request->isAjax() || $this->request->get('export')) {
            return $this->paginate($grid);
        }
        $this->view->grid = $grid;
    }

    /**
     * @Route("/edit/{id}", "name" = "user_edit")
     */
    public function editAction($id)
    {
        $user = Users::findFirstById($id);
        if (!$user) {
            $this->flash->error('\'This user does not found.\'');
            return $this->response->redirect(['for' => 'user_show']);
        }
        $user->roleId = $user->usersRoles->roleId;
        $userForm = new UsersForm($user);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (!$userForm->isValid($post)) {
                return $this->view->form = $userForm;
            }

            $this->dbMaster->begin();
            $userEdit = Users::edit($post, $user);
            if (!$userEdit) {
                $this->dbMaster->rollback();
                return $this->view->form = $userForm;
            }

            $this->dbMaster->commit();
            $this->flash->success('\'This user successfully updated.\'');
            return $this->response->redirect(['for' => 'user_show']);
        }

        $this->view->form = $userForm;
        $this->view->userId = $id;
    }

    /**
     * @Route("/create", "name" = "user_create")
     */
    public function createAction()
    {
        $userForm = new UsersForm();

        if ($this->request->isPost()) {

            $post = $this->request->getPost();
            if (!$userForm->isValid($post)) {
                return $this->view->form = $userForm;
            }

            $random = new Random();
            $password = $random->base64Safe(4);
            $post['password'] = $password;

            /**
             * Start transaction.
             */
            $this->dbMaster->begin();
            $users = Users::created($post);
            if (!$users) {
                /**
                 * Rollback transaction.
                 */
                $this->dbMaster->rollback();
                return $this->view->form = $userForm;
            }

            try {
                /**
                 * @var MailTemplates $templates
                 */
                $templates = MailTemplates::findFirstByKey('REGISTRATION');
                $templates->replace([
                    'name' => $users->name,
                    'password' => $password
                ]);

                $mailer = \Phalcon\DI::getDefault()->getShared('mailer');
                $config = \Phalcon\DI::getDefault()->getShared('config');
                $message = new \Swift_Message();
                $message->setSubject($templates->subject);
                $message->setBody($templates->body);
                $message->setContentType("text/html");
                $message->setTo($users->email);
                $message->setFrom($config->mail->from);
                $mailer->send($message);

                /**
                 * Commit transaction.
                 */
                $this->dbMaster->commit();
                $this->flash->success('\'This user successfully created.\'');
                return $this->response->redirect();
            } catch (\Exception $e) {
                $this->dbMaster->rollback();
                $this->flash->error('\'User does not create.\'');
            }
        }
        $this->view->form = $userForm;
    }

    /**
     * @Route("/blocked/{id}", "name" = "user_blocked")
     */
    public function blockedAction($id)
    {
        $user = Users::findFirstById($id);
        if (!$user) {
            $this->flash->error('\'This user does not blocked.\'');
            return $this->response->redirect(['for' => 'user_show']);
        }
        $user->status = Users::STATUS_BLOCKED;
        $user->save();
        $this->flash->success('\'This user successfully blocked.\'');
        return $this->response->redirect(['for' => 'user_show']);
    }
}