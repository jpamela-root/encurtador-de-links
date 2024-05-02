<?php
if (!empty($_POST['link']) && !empty($_POST['short'])) {
    $link = $_POST["link"];
    $short = $_POST["short"];

    // Adicione aqui a lógica para encurtar o link

    echo "Link original: $link<br>";
    echo "Link encurtado: $short";
} else {
    echo "Por favor, insira um link original e um link encurtado.";
}
?>


/*um dia eu termino */
