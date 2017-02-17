<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Контрольная работа №4</title>
        <style>
            p, form{
                text-align: center;
            }
        </style>
    </head>
    <body>
    <?php
        include_once 'constants.php';

        $controller = new Controller();
        $controller->run();
    ?>
    </body>
</html>

<?php
    class Controller {
        private $model;
        private $view;
        private $page = 1;
        private $action = 'read';


       private function analyzeReadParameters(){
           $this->readAction();
           $this->readPage();
       }

        private function readAction(){
            if (isset($_POST['action']))
                $this->action = $_POST['action'];
        }

        private function readPage(){
            if (isset($_GET['page']))
                $this->page = $_GET['page'];
        }

        public function run(){
            $this->analyzeReadParameters();

            $this->model = new Model($this->page, $this->action);
            $this->model->work();

            $this->view = new View($this->model->getContent(), $this->model->getAction(), $this->model->getPage(), $this->model->getNumberOfPages());
            $this->view->show();
        }
    }

class Model{
    private $messageArray;
    private $currentPage;
    private $numberOfMessages;
    private $numberOfPages;
    private $action;
    private $content = array();

    public function __construct($currentPage, $action){
        $this->currentPage = $currentPage;
        $this->action = $action;
    }

    public function work(){
        $this->correctAction();
        if ($this->action == 'read'){
            $this->readData();
            $this->computeNumberOfpages();
            $this->correctPage();
            $this->setContentRead();
        }
        if($this->action == 'created'){
            $this->createdAction();
        }
    }

    private function createdAction(){
        function generateData($POST, &$set){
            if(!empty($POST)){
                $set = $POST;
                $set = strip_tags($set);
            }
            else{
                header("Location: ". $_SERVER["REQUEST_URI"]);
            }
        }

        generateData($_POST['name'], $name);
        generateData($_POST['message'], $message);
        $data = date('G:i:s, d.m.Y');

        $info = "$name\t$message\t$data\r\n";
        file_put_contents(FILENAME, $info, FILE_APPEND);
        header("Location: /KCP4/");
    }

    public function correctAction(){
        if ($this->action != 'read' && $this->action != 'create' && $this->action != 'created'){
            $this->action = 'read';
        }
    }

    public function readData(){
        $this->messageArray = file(FILENAME);
        $this->messageArray = array_reverse($this->messageArray);
        $this->numberOfMessages = count($this->messageArray);
    }

    public function computeNumberOfPages(){
        $this->numberOfPages = ceil($this->numberOfMessages/MESSAGESINPAGE);
    }

    public function correctPage(){
        if ($this->currentPage <= 0 || $this->currentPage > $this->numberOfPages)
            die('Неправильный ввод!');
    }

    public function setContentRead(){
        $this->messageArray = array_slice($this->messageArray, ($this->currentPage - 1) * MESSAGESINPAGE, MESSAGESINPAGE);
        $j = 0;
        foreach($this->messageArray as $value) {
            $content = explode(chr(9), $value);
            $i = 0;
            foreach($content as $value1) {
                if($i == 0){
                    $this->content[$j]['name'] =  $value1;
                }
                if($i == 1){
                    $this->content[$j]['message'] = $value1;
                }
                if($i == 2){
                    $this->content[$j]['time'] = $value1;
                }
                $i++;
            }
            $j++;
        }
    }

    public function getAction(){
        return $this->action;
    }

    public function getContent(){
        return $this->content;
    }

    public function getPage(){
        return $this->currentPage;
    }

    public function getNumberOfPages(){
        return $this->numberOfPages;
    }
}

class View {
    private $data;
    private $currentPage;
    private $numberOfPages;
    private $action;


    public function __construct($data, $action, $currentPage, $numberOfPages)
    {
        $this->data = $data;
        $this->currentPage = $currentPage;
        $this->numberOfPages = $numberOfPages;
        $this->action = $action;
    }

    public function showRead(){
        foreach ($this->data as $value1) {
            echo "<p>Имя пользователя:\t" . $value1['name'] . "<br>Сообщение:\t" . $value1['message'] . "<br>Время отправления:\t" .
                $value1['time'] . "</p>";
        }?>
        <form method="post" action="index.php?" enctype="multipart/form-data">
            <input type="submit" value="Добавить отзыв">
            <input type="hidden" name="action" value="create">
        </form>
        <?php
        function viewHref($hrefPage){
            echo ' <a href="index.php?page=' . $hrefPage .'">' . $hrefPage . '</a> ';
        }
        echo '<p>';
        if ($this->currentPage > 2) viewHref(1);
        if ($this->currentPage > 3) echo '...';
        if ($this->currentPage > 1) viewHref($this->currentPage - 1);
        echo ' ' . $this->currentPage;
        if ($this->currentPage < $this->numberOfPages) viewHref($this->currentPage + 1);
        if ($this->currentPage < $this->numberOfPages - 2) echo '...';
        if ($this->currentPage < $this->numberOfPages - 1) viewHref($this->numberOfPages);
        echo '</p>';
    }

    public function showCreate(){

    }

    public function show(){
        if ($this->action == 'read') {
            $this->showRead();
        }
        if ($this->action == 'create'){
            ?>
            <form method="POST" action="index.php">
                <p><label>Введите имя: </label>
                    <input type="text" name="name" id="name" required autofocus pattern="^[А-ЯA-Za-zа-я]+"></p>
                <p><label>Введите сообщение:</label>
                    <textarea id="message" name="message" required></textarea></p><br>
                <input type="submit" value="Добавить">
                <input type="hidden" name="action" value="created">
            </form>
            <?php
        }
    }
}