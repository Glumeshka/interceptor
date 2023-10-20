<?php
namespace App\controllers;

use App\core\Controller;

class NotRedirect extends Controller
{
    public function index()
    {
        $this->pageData['title'] = "Реклама не оплачена!";
        $this->view->render('notRedirect.phtml', 'template.phtml', $this->pageData);
    }
}