<?php
namespace App\models;
use App\core\Model;

class BoardModel extends Model
{
    ////////////////
    // Общий блок //
    ////////////////
    // получение данных для преставления в зависимости от ролей
    public function getUserData($id_user)
    {
        $dataUser = [];
        $sql = "SELECT email, role
                FROM users
                WHERE id = :id;";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", intval($id_user), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $user = $res;

        $dataUser['name'] = $user['email'];

        if ($user['role'] == 'webmaster') {
            $dataUser['role'] = 'Вебмастер';

        } else if ($user['role'] == 'advertiser') {
            $dataUser['role'] = 'Рекламодатель';

        } else if ($user['role'] == 'admin') {
            $dataUser['role'] = 'Администратор';

        } else {
            $dataUser['role'] = 'Злоумышленник';
        }

        return $dataUser; // name, role на русском
    }

    // чек активности заказа
    public function isActiveOffer($id_offer)
    {
        $sql = "SELECT active
                FROM offers
                WHERE id = :id_offer;";

        $stmt = $this->db->prepare($sql);        
        $stmt->bindValue(":id_offer", intval($id_offer), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $sub = intval($res[0]);

        if ($sub === 1) {
            return true;
        } else {
            return false;
        }
    }

    // ссылка для редиректа
    public function originalLink($id_offer)
    {
        $sql = "SELECT original_url
                FROM offers
                WHERE id = :id_offer;";

        $stmt = $this->db->prepare($sql);        
        $stmt->bindValue(":id_offer", intval($id_offer), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $link = $res[0];

        return $link;
    }

    // Редиректор для работы со ссылками
    public function interceptor($id_user, $id_offer)
    {        
        $sql = "SELECT COUNT(*) as sub
                FROM subscriptions
                WHERE webmaster_id = :webmaster_id AND offer_id = :offer_id;"; 
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":webmaster_id", intval($id_user), \PDO::PARAM_INT);
        $stmt->bindValue(":offer_id", intval($id_offer), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $sub = intval($res[0]); // ok             

        $sql2 = "INSERT INTO clicklogs (click_time, done, offer_id, webmaster_id)
                 VALUES (CURRENT_TIMESTAMP, :done, :offer_id, :webmaster_id);";

        $stmt2 = $this->db->prepare($sql2);
       
        $stmt2->bindValue(":webmaster_id", intval($id_user), \PDO::PARAM_INT);
        $stmt2->bindValue(":offer_id", intval($id_offer), \PDO::PARAM_INT);

        if ($sub === 0 || !$this->isActiveOffer($id_offer)) {

            $stmt2->bindValue(":done", 0, \PDO::PARAM_INT);
            $stmt2->execute();
            return false;      

        } else if ($sub === 1 && $this->isActiveOffer($id_offer)) {
            
            $stmt2->bindValue(":done", 1, \PDO::PARAM_INT);
            $stmt2->execute();
            return true;
        }
    }

    /////////////////////////////
    // блок для Администратора //
    /////////////////////////////
    // Регистрация нового пользователя
    public function regUser($login, $password, $role)
    {
        $password_hash = password_hash($password, PASSWORD_ARGON2ID);

        $sql = "INSERT INTO users (email, password, role)
                VALUES (:email, :password, :role)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $login, \PDO::PARAM_STR);
        $stmt->bindParam(':password', $password_hash, \PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, \PDO::PARAM_STR);      

        if(!$this->checkUser($login)){            
            $stmt->execute();
            $message = 'Пользователь успешно создан';
            return $message;
        } else {
            $message = "Такой пользователь уже существует!";
            return $message;
        }
    }

    // проверка текущего пользователя
    public function checkUser($login)
    {
        $sql = "SELECT COUNT(*) as count
                FROM users
                WHERE email = :email;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":email", $login, \PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $count = intval($res[0]);

        if($count === 1) {
            return true;
        } else {            
            return false;
        }         
    }

    // получение списка не заблокированных пользователей
    public function getActiveUsers($id_user)
    {
        $sql = "SELECT id, email, role
                FROM users
                WHERE banned = 0 AND id <> :id_user;";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id_user", intval($id_user), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchall(\PDO::FETCH_ASSOC);
        $users = $res;
        return $users; // id, email, role
    }

    // получение списка заблокированных пользователей
    public function getInactiveUsers($id_user)
    {
        $sql = "SELECT id, email, role
                FROM users
                WHERE banned = 1 AND id <> :id_user;";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id_user", intval($id_user), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchall(\PDO::FETCH_ASSOC);
        $users = $res;
        return $users; // id, email, role 
    }

    //блокирование пользователя
    public function banUser($id_user)
    {
        $sql = "UPDATE users
                SET banned = 1
                WHERE id = :id_user;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id_user", intval($id_user), \PDO::PARAM_INT);
        $stmt->execute();
    }

    //разблокирование пользователя
    public function unbanUser($id_user)
    {
        $sql = "UPDATE users
                SET banned = 0
                WHERE id = :id_user;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id_user", intval($id_user), \PDO::PARAM_INT);
        $stmt->execute();
    }
    
    //получение массива для вывода статистики системы за заданный период
    public function statsSystem($oneDate, $twoDate)
    {
        // ловим дату вызова
        $currentTime = date("Y-m-d H:i:s");
        // символичная дата для отлова всего периода работы приложения
        $startData = date("2000-01-01");
        
        $data = [
            'countLink' => $this->countSubscription($oneDate, $twoDate),
            'countDone' => $this->countLinkDone($oneDate, $twoDate),
            'countFauled' => $this->countLinkFailed($oneDate, $twoDate),
            'income' => $this->getTotalClickCost($oneDate, $twoDate),
            'totalIncome' => $this->getTotalClickCost($startData, $currentTime)
        ];
        return $data;
    }

    // служебные для статистики
    // количество переходов которые прошли
    public function countLinkDone($oneDate, $twoDate)
    {
        $endTwoDate = $twoDate . ' 23:59:59';

        $sql = "SELECT COUNT(*) as countdone
                FROM clicklogs
                WHERE done = 1
                AND click_time BETWEEN :oneDate AND :twoDate;";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":oneDate", $oneDate, \PDO::PARAM_STR);
        $stmt->bindValue(":twoDate", $endTwoDate, \PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $countDone = $res[0];
        return $countDone; // countdone
    }

    // количество переходов которые дали отказ
    public function countLinkFailed($oneDate, $twoDate)
    {
        $endTwoDate = $twoDate . ' 23:59:59';

        $sql = "SELECT COUNT(*) as countfauled
                FROM clicklogs
                WHERE done = 0
                AND click_time BETWEEN :oneDate AND :twoDate;";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":oneDate", $oneDate, \PDO::PARAM_STR);
        $stmt->bindValue(":twoDate", $endTwoDate, \PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $countfauled = $res[0];
        return $countfauled; // countfauled
    }

    // список офферов и их цен
    public function offers()
    {        
        $sql = "SELECT id AS id_offer, cost_per_click
                FROM offers;";   

        $stmt = $this->db->prepare($sql);  
        $stmt->execute();
        $res = $stmt->fetchall(\PDO::FETCH_ASSOC);
        $offers = $res;
        return $offers; // id_offer, cost_per_click
    }

    // количество кликов за время на каждый заказ
    public function offerClick($oneDate, $twoDate, $offer_id)
    {        
        $endTwoDate = $twoDate . ' 23:59:59';

        $sql = "SELECT COUNT(*) as count
                FROM clicklogs
                WHERE done = 1
                AND click_time BETWEEN :oneDate AND :twoDate
                AND offer_id = :offer_id;";

        $stmt = $this->db->prepare($sql);  
        $stmt->bindValue(":oneDate", $oneDate, \PDO::PARAM_STR);
        $stmt->bindValue(":twoDate", $endTwoDate, \PDO::PARAM_STR);
        $stmt->bindValue(":offer_id", intval($offer_id), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $offers = intval($res[0]);
        return $offers; // count
    }

    // количество денег за все заказы
    public function getTotalClickCost($oneDate, $twoDate)
    {        
        $offers = $this->offers();        
        $totalCost = 0;
        
        foreach ($offers as $offer) {

            $offerId = $offer['id_offer'];            
            $clicks = $this->offerClick($oneDate, $twoDate, $offerId);            
            $clickCost = $offer['cost_per_click'];
            $offerCost = $clickCost * $clicks * 0.2;            
            $totalCost += $offerCost;
        }

        return round($totalCost, 2);
    }

    // количество выданных ссылок веб мастерам за период
    public function countSubscription($oneDate, $twoDate)
    {
        $endTwoDate = $twoDate . ' 23:59:59';

        $sql = "SELECT COUNT(*) as countsubs
                FROM subscriptions
                WHERE sub_time BETWEEN :oneDate AND :twoDate;";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":oneDate", $oneDate, \PDO::PARAM_STR);
        $stmt->bindValue(":twoDate", $endTwoDate, \PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $countLink = intval($res[0]);
        return $countLink; // countLink
    }

    /////////////////////////
    // блок для Вебмастера //
    /////////////////////////
    // массив с заказами на которые не подписан текущий вебмастер
    public function offersFree($id_user)
    {
        $sql = "SELECT o.id, o.name, o.topic, o.cost_per_click
                FROM offers o
                LEFT JOIN subscriptions s ON o.id = s.offer_id AND s.webmaster_id = :user_id
                WHERE o.active = 1 AND s.id IS NULL;";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":user_id", intval($id_user), \PDO::PARAM_INT);       
        $stmt->execute();
        $res = $stmt->fetchall(\PDO::FETCH_ASSOC);

        foreach ($res as &$row) {
            $row['cost_per_click'] = round($row['cost_per_click'] * 0.8, 2);
        }

        $offersFree = $res;
        return $offersFree; // id, name, topic, cost_per_click
    }

    // массив с заказами на которые подписан текущий вебмастер
    public function offersInwork($id_user)
    {
        $sql = "SELECT o.id, o.name, o.topic, o.cost_per_click
                FROM offers o
                JOIN subscriptions s ON o.id = s.offer_id
                WHERE o.active = 1 AND s.webmaster_id = :user_id;";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":user_id", intval($id_user), \PDO::PARAM_INT);       
        $stmt->execute();
        $res = $stmt->fetchall(\PDO::FETCH_ASSOC);

        foreach ($res as &$row) {
            $row['cost_per_click'] = $row['cost_per_click'] * 0.8;
        }

        $offersInwork = $res;
        return $offersInwork; // id, name, topic, cost_per_click
    }

    // подписка на заказ
    public function subscribe($id_user, $offer)
    {
        $sql = "INSERT INTO subscriptions (sub_time, webmaster_id, offer_id)
                VALUES (CURRENT_TIMESTAMP, :webmaster_id, :offer_id);";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":webmaster_id", intval($id_user), \PDO::PARAM_INT);
        $stmt->bindValue(":offer_id", intval($offer), \PDO::PARAM_INT);     
        $stmt->execute();
    }

    // отписка на заказа
    public function unsubscribe($id_user, $offer)
    {
        $sql = "DELETE FROM subscriptions
                WHERE webmaster_id = :webmaster_id AND offer_id = :offer_id;";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":webmaster_id", intval($id_user), \PDO::PARAM_INT);
        $stmt->bindValue(":offer_id", intval($offer), \PDO::PARAM_INT);      
        $stmt->execute();
    }

    // получение ссылки для веб мастера
    public function offerLink($id_user, $offer)
    {
        $link = URL . 'Board/redirect?dev=' . $id_user . '&offer=' . $offer;
        return $link;        
    }

    // получение массива для вывода статистики вебмастера за заданный период
    public function statsDev($oneDate, $twoDate, $id_user)
    {
        $data = [
            'countRedirect' => $this->countRedirects($oneDate, $twoDate, $id_user),
            'income' => $this->getTotalClickCostDev($oneDate, $twoDate, $id_user)
        ];
        return $data;
    }

    // служебные методы
    // количество переходов по всем подпискам
    public function countRedirects($oneDate, $twoDate, $id_user)
    {        
        $endTwoDate = $twoDate . ' 23:59:59';

        $sql = "SELECT COUNT(*) as count
                FROM clicklogs
                WHERE done = 1
                AND click_time BETWEEN :oneDate AND :twoDate
                AND webmaster_id = :webmaster_id;";

        $stmt = $this->db->prepare($sql);  
        $stmt->bindValue(":oneDate", $oneDate, \PDO::PARAM_STR);
        $stmt->bindValue(":twoDate", $endTwoDate, \PDO::PARAM_STR);
        $stmt->bindValue(":webmaster_id", intval($id_user), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $countRedirects = $res[0];
        return $countRedirects; // countRedirects
    }

    // считаем сумму по аналогии с системой
    public function getTotalClickCostDev($oneDate, $twoDate, $id_user)
    {        
        $offers = $this->offersInwork($id_user);        
        $totalCost = 0;
        
        foreach ($offers as $offer) {

            $offerId = $offer['id'];            
            $clicks = $this->offerClickDev($oneDate, $twoDate, $offerId, $id_user);            
            $clickCost = $offer['cost_per_click'];
            $offerCost = $clickCost * $clicks;            
            $totalCost += $offerCost;
        }

        return round($totalCost, 2);
    }

    // исправление с корректным высчитыванием количество переходов для конкретного веб мастера
    public function offerClickDev($oneDate, $twoDate, $offer_id, $id_user)
    {
        $endTwoDate = $twoDate . ' 23:59:59';

        $sql = "SELECT COUNT(*) as count
                FROM clicklogs
                WHERE done = 1
                AND click_time BETWEEN :oneDate AND :twoDate
                AND offer_id = :offer_id AND webmaster_id = :webmaster_id;";

        $stmt = $this->db->prepare($sql);  
        $stmt->bindValue(":oneDate", $oneDate, \PDO::PARAM_STR);
        $stmt->bindValue(":twoDate", $endTwoDate, \PDO::PARAM_STR);
        $stmt->bindValue(":offer_id", intval($offer_id), \PDO::PARAM_INT);
        $stmt->bindValue(":webmaster_id", intval($id_user), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $offers = $res[0];
        return $offers; // count 
    }

    ////////////////////////////
    // блок для Рекламодателя //
    ////////////////////////////
    // создание заказа
    public function newOffer($name, $topic, $cost, $url, $id_user)
    {
        $sql = "INSERT INTO offers (name, topic, cost_per_click, active, original_url, advertiser_id)
                VALUES (:name, :topic, :cost_per_click, :active, :original_url, :advertiser_id);";   
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":name", $name, \PDO::PARAM_STR);        
        $stmt->bindValue(":cost_per_click", $cost, \PDO::PARAM_STR);
        $stmt->bindValue(":active", 1, \PDO::PARAM_INT);
        $stmt->bindValue(":original_url", $url, \PDO::PARAM_STR);
        $stmt->bindValue(":advertiser_id", intval($id_user), \PDO::PARAM_INT);

        if($topic == '') {
            $topic = 'не указана';
            $stmt->bindValue(":topic", $topic, \PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt->bindValue(":topic", $topic, \PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    // получение списка активных заказов
    public function activeOffers($id_user)
    {
        $sql = "SELECT offers.id, offers.name, offers.cost_per_click, COUNT(subscriptions.webmaster_id) AS subscribers_count
                FROM offers
                LEFT JOIN subscriptions ON subscriptions.offer_id = offers.id
                WHERE offers.active = 1 AND offers.advertiser_id = :advertiser_id
                GROUP BY offers.id, offers.name, offers.cost_per_click;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":advertiser_id", intval($id_user), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchall(\PDO::FETCH_ASSOC);

        $activeOffers = $res;
        return $activeOffers; // id, name, cost_per_click, subscribers_count
    }

    // получение списка неактивных заказов
    public function inactiveOffers($id_user)
    {
        $sql = "SELECT offers.id, offers.name, offers.cost_per_click, COUNT(subscriptions.webmaster_id) AS subscribers_count
                FROM offers
                LEFT JOIN subscriptions ON subscriptions.offer_id = offers.id
                WHERE offers.active = 0 AND offers.advertiser_id = :advertiser_id
                GROUP BY offers.id, offers.name, offers.cost_per_click;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":advertiser_id", intval($id_user), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchall(\PDO::FETCH_ASSOC);

        $activeOffers = $res;
        return $activeOffers; // id, name, cost_per_click, subscribers_count
    }

    // Деактивация заказа
    public function deactivationOffer($id_offer)
    {
        $sql = "UPDATE offers
                SET active = 0
                WHERE id = :id_offer;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id_offer", intval($id_offer), \PDO::PARAM_INT);
        $stmt->execute();
    }

    // Активация заказа
    public function activationOffer($id_offer)
    {
        $sql = "UPDATE offers
                SET active = 1
                WHERE id = :id_offer;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id_offer", intval($id_offer), \PDO::PARAM_INT);
        $stmt->execute();
    }

    // получение количества подписанных вебмастеров на заказ
    public function countDev($id_offer)
    {
        $sql = "SELECT COUNT(*) as count
                FROM subscriptions
                WHERE offer_id = :offer_id;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":offer_id", intval($id_offer), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $countDev = $res[0];
        return $countDev; // countDev
    }

    // получение стататистики для Рекламодателя
    public function statsAdv($oneDate, $twoDate, $id_offer)
    {
        $data = [
            'countRedirect' => $this->countRedirectsOffer($oneDate, $twoDate, $id_offer),
            'expenses' => $this->getExpenses($oneDate, $twoDate, $id_offer)
        ];
        return $data;
    }

    // служебные методы для статистики
    // получаем количеств прееходов для конкретного заказа
    public function countRedirectsOffer($oneDate, $twoDate, $id_offer)
    {
        $endTwoDate = $twoDate . ' 23:59:59';

        $sql = "SELECT COUNT(*) as count
                FROM clicklogs
                WHERE done = 1
                AND click_time BETWEEN :oneDate AND :twoDate
                AND offer_id = :offer_id;";

        $stmt = $this->db->prepare($sql);  
        $stmt->bindValue(":oneDate", $oneDate, \PDO::PARAM_STR);
        $stmt->bindValue(":twoDate", $endTwoDate, \PDO::PARAM_STR);
        $stmt->bindValue(":offer_id", intval($id_offer), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $countRedirectsOffer = $res[0];
        return $countRedirectsOffer; // countRedirects
    }

    // получение стоимости за клик с заказа
    public function getCostPerClick($id_offer)
    {
        $sql = "SELECT cost_per_click
                FROM offers
                WHERE id = :offer_id;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue("offer_id", intval($id_offer), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_NUM);
        $costPerClick = $res[0];   

        return $costPerClick; // cost_per_click
    }

    // получаем расход по офферу в заданном периоде
    public function getExpenses($oneDate, $twoDate, $id_offer)
    {  
        $clicks = $this->offerClick($oneDate, $twoDate, $id_offer);            
        $clickCost = floatval($this->getCostPerClick($id_offer));
        $offerCost = round($clickCost * $clicks, 2);            
        
        return $offerCost;
    }
}