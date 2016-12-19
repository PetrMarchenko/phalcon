<?php

namespace Shark\Module\MailTemplate\Controller;

use Shark\Library\Controller\ControllerBase;
use Shark\Module\MailTemplate\Forms\MailTemplateForm;
use Shark\Module\MailTemplate\Grid\MailTemplateGrid;
use Shark\Module\MailTemplate\Models\MailTemplates;
use Shark\Library\Exception\SharkServerErrorException;
/**
 * Class IndexController
 *  @RoutePrefix("/mail")
 */
class ManagementController extends ControllerBase
{
    /**
     * Index action
     *
     * @Route("/", "name" = "mail_template_show")
     */
    public function indexAction()
    {
        \Phalcon\Tag::prependTitle('Mail template management | ');
        $this->gridDefaults['sortBy'] = 'mailTemplates.id';
        $this->gridDefaults['sortDir'] = 'asc';

        $grid = new MailTemplateGrid();
        $params = $this->getGridParams();
        $grid->setQueryParams($params);


        if ($this->request->isAjax() || $this->request->get('export')) {
            return $this->paginate($grid);
        }
        $this->view->grid = $grid;
    }

    /**
     * Edit action
     *
     * @Route("/edit/{id}", "name" = "mail_template_edit")
     */
    public function editAction($id)
    {
        $mailTemplate = MailTemplates::findFirstById($id);
        if (!$mailTemplate) {
            throw new SharkServerErrorException('\'This template do not found.\'');
        }

        $mailTemplateForm = new MailTemplateForm($mailTemplate);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $mailTemplateForm->bind($post, $mailTemplate);
            if ($mailTemplateForm->isValid()) {
                $mailTemplate->save();
                $this->flash->success('\'Template has already saved.\'');
                return $this->response->redirect(['for' => 'mail_template_edit']);
            }
        }

        $this->view->form = $mailTemplateForm;
    }
}