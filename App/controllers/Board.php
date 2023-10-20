<?php
namespace App\controllers;

use App\core\View;
use App\core\Controller;
use App\models\BoardModel;


class Board extends Controller
{
    public function __construct()
    {
        $this->model = new BoardModel();        
        $this->view = new View();
    }

    public function index()
    {        
        if(empty($_SESSION['user_id'])){
            header("Location: /");
        }

        $this->pageData['name'] = $this->model->getUserData($_SESSION['user_id'])['name'];
        $this->pageData['role'] = $this->model->getUserData($_SESSION['user_id'])['role'];
        $this->pageData['js'] = "/js/board.js";

        if ($this->pageData['role'] == 'Администратор') {

            $this->pageData['activeUser'] = $this->model->getActiveUsers($_SESSION['user_id']);
            $this->pageData['inactiveUser'] = $this->model->getInactiveUsers($_SESSION['user_id']);
            $this->pageData['title'] = "Панель Управление Cистемой";
            $this->view->render('board.phtml', 'template.phtml', $this->pageData);

        } else if ($this->pageData['role'] == 'Вебмастер') {

            $this->pageData['offersFree'] = $this->model->offersFree($_SESSION['user_id']);
            $this->pageData['offersInwork'] = $this->model->offersInwork($_SESSION['user_id']);
            $this->pageData['title'] = "Управление Заказами Рекламодателей";
            $this->view->render('board.phtml', 'template.phtml', $this->pageData);
        
        } else if ($this->pageData['role'] == 'Рекламодатель') {
            
            $this->pageData['activeOffers'] = $this->model->activeOffers($_SESSION['user_id']);
            $this->pageData['inactiveOffers'] = $this->model->inactiveOffers($_SESSION['user_id']);
            $this->pageData['allOffers'] = $this->model->inactiveOffers($_SESSION['user_id']);
            $this->pageData['allOffers'] = array_merge($this->pageData['allOffers'], $this->model->activeOffers($_SESSION['user_id']));
            $this->pageData['title'] = "Управление Рекламными Компаниями";
            $this->view->render('board.phtml', 'template.phtml', $this->pageData);
        }     
    }

    public function profile()
    {
        // для тестов
    }

    public function redirect()
    {
        $id_user = $_GET['dev'];
        $id_offer = $_GET['offer'];      
        if($this->model->interceptor($id_user, $id_offer)) {
            $link = $this->model->originalLink($id_offer);
            header("Location: $link");
        } else {
            header('Location: /NotRedirect'); 
        }
    }
    ///////////////////////////////
    // кнопки для Администратора //
    ///////////////////////////////
    // создание пользователя
    public function createUser()
    {
        $login = trim(strtolower($_POST['email']));
        $password = trim($_POST['password']);
        $password2 = trim($_POST['password2']);
        $role = $_POST['role'];

        if($password === $password2){
            $message = $this->model->regUser($login, $password, $role);

            $dataMessage = [
                'message' => $message
            ];

            $dataUserArrays = [
                'active' => $this->model->getActiveUsers($_SESSION['user_id']),
                'inactive' => $this->model->getInactiveUsers($_SESSION['user_id'])
            ];

            $dataUser = $dataMessage + $dataUserArrays;
            $jsonData = json_encode($dataUser);
            echo $jsonData;

        } else {

            $message = "Пароли не совпадают!";

            $dataMessage = [
                'message' => $message
            ];

            $jsonData = json_encode($dataMessage);
            echo $jsonData;
        } 
    }
    
    // блокировка пользователя
    public function banUser()
    {
        $id_user = $_POST['id_user'];
        $this->model->banUser($id_user);

        $message = 'Пользователь успешно заблокирован!';

        $dataMessage = [
            'message' => $message
        ];

        $dataUserArrays = [
            'active' => $this->model->getActiveUsers($_SESSION['user_id']),
            'inactive' => $this->model->getInactiveUsers($_SESSION['user_id'])
        ];

        $dataUser = $dataMessage + $dataUserArrays;
        $jsonData = json_encode($dataUser);
        echo $jsonData;
    }

    // разблокировка пользователя
    public function unbanUser()
    {
        $id_user = $_POST['id_user'];
        $this->model->unbanUser($id_user);
        
        $message = 'Пользователь успешно разблокирован!';
        
        $dataMessage = [
            'message' => $message
        ];

        $dataUserArrays = [
            'active' => $this->model->getActiveUsers($_SESSION['user_id']),
            'inactive' => $this->model->getInactiveUsers($_SESSION['user_id'])
        ];

        $dataUser = $dataMessage + $dataUserArrays;
        $jsonData = json_encode($dataUser);
        echo $jsonData;
    }

    // получение статистики системы
    public function statsSys()
    {
        $oneDate = $_POST['date_start'];
        $twoDate = $_POST['date_end'];

        $timestamp1 = strtotime($oneDate);
        $timestamp2 = strtotime($twoDate);

        if ($timestamp1 > $timestamp2) {
            $temp = $oneDate;
            $oneDate = $twoDate;
            $twoDate = $temp;
        }

        $message = $this->model->statsSystem($oneDate, $twoDate);
        $jsonData = json_encode($message);
        echo $jsonData;
    }

    ///////////////////////////
    // кнопки для Вебмастера //
    ///////////////////////////
    // подписка за оффер
    public function subscribeOffer()
    {
        $id_user = $_POST['user_id'];
        $offer = $_POST['offer'];

        $this->model->subscribe($id_user, $offer);       

        $message = 'Вы подписались на заказ!';

        $dataMessage = [
            'message' => $message
        ];

        $dataUserArrays = [
            'offersFree' => $this->model->offersFree($id_user),
            'offersInwork' => $this->model->offersInwork($id_user),
            'link' => $this->model->offerLink($id_user, $offer)
        ];

        $dataUser = $dataMessage + $dataUserArrays;
        $jsonData = json_encode($dataUser);
        echo $jsonData;
    }

    // отписка от оффера
    public function unsubscribeOffer()
    {
        $id_user = $_POST['user_id'];
        $offer = $_POST['offer'];

        $this->model->unsubscribe($id_user, $offer);       

        $message = 'Вы отписались от заказа!';

        $dataMessage = [
            'message' => $message
        ];

        $dataUserArrays = [
            'offersFree' => $this->model->offersFree($id_user),
            'offersInwork' => $this->model->offersInwork($id_user)
        ];

        $dataUser = $dataMessage + $dataUserArrays;
        $jsonData = json_encode($dataUser);
        echo $jsonData;
    }

    // получение ссылки для вебмастера по выбранному офферу
    public function getLink()
    {
        $id_user = $_POST['user_id'];
        $offer = $_POST['offer'];      
        
        $dataLink = [
            'link' => $this->model->offerLink($id_user, $offer)
        ];
        
        $jsonData = json_encode($dataLink);
        echo $jsonData;
    }

    // статистика для вебмастера
    public function statsDev()
    {
        $id_user = $_POST['user_id'];
        $oneDate = $_POST['date_start'];
        $twoDate = $_POST['date_end'];        

        $timestamp1 = strtotime($oneDate);
        $timestamp2 = strtotime($twoDate);

        if ($timestamp1 > $timestamp2) {
            $temp = $oneDate;
            $oneDate = $twoDate;
            $twoDate = $temp;
        }

        $message = $this->model->statsDev($oneDate, $twoDate, $id_user);
        $jsonData = json_encode($message);
        echo $jsonData;
    }

    //////////////////////////////
    // кнопки для Рекламодателя //
    //////////////////////////////
    // кнопка создания оффера
    public function createOffer()
    {        
        $name = $_POST['name'];
        $topic = $_POST['topic'];
        $cost = $_POST['cost'];
        $url = $_POST['url'];
        $id_user = $_POST['id_user'];

        $this->model->newOffer($name, $topic, $cost, $url, $id_user);

        $message = 'Вы успешно создали заказ!';

        $dataMessage = [
            'message' => $message
        ];

        $allOffers = $this->model->inactiveOffers($id_user);
        $allOffers = array_merge($allOffers, $this->model->activeOffers($id_user));

        $dataUserArrays = [
            'activeOffers' => $this->model->activeOffers($id_user),
            'allOffers' => $allOffers         
        ];

        $dataUser = $dataMessage + $dataUserArrays;
        $jsonData = json_encode($dataUser);
        echo $jsonData;
    }

    // перемещение в список активных офферов
    public function activationOffer()
    {                
        $id_offer = $_POST['id_offer'];
        $this->model->activationOffer($id_offer);

        $message = 'Данный заказ теперь активен!';

        $dataMessage = [
            'message' => $message
        ];

        $dataUser = $dataMessage;
        $jsonData = json_encode($dataUser);
        echo $jsonData;
    }

    // перемещение в список не активных офферов
    public function deactivationOffer()
    {         
        $id_offer = $_POST['id_offer'];
        $this->model->deactivationOffer($id_offer);

        $message = 'Данный заказ теперь не активен!';

        $dataMessage = [
            'message' => $message
        ];

        $dataUser = $dataMessage;
        $jsonData = json_encode($dataUser);
        echo $jsonData;
    }

    // статистика рекламодателя
    public function statsAdv()
    {
        $id_offer = $_POST['id_offer'];
        $oneDate = $_POST['date_start'];
        $twoDate = $_POST['date_end'];        

        $timestamp1 = strtotime($oneDate);
        $timestamp2 = strtotime($twoDate);

        if ($timestamp1 > $timestamp2) {
            $temp = $oneDate;
            $oneDate = $twoDate;
            $twoDate = $temp;
        }

        $message = $this->model->statsAdv($oneDate, $twoDate, $id_offer);
        $jsonData = json_encode($message);
        echo $jsonData;
    }

    // выход из системы
    public function logout()
    {
        session_destroy();
        header("Location: /");
    }
}