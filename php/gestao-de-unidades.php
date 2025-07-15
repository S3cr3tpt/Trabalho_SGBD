<?php
require_once("custom/php/common.php");

// Verifica conexão com o banco de dados
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (!$link) {
    die("Erro: conexão com o banco de dados não está ativa. Verifique o arquivo common.php.");
}

function mostrarUnidades($link) {
    $query = "SELECT sut.id, sut.name AS unit_name, 
              GROUP_CONCAT(CONCAT(si.name, ' (', i.name, ')') SEPARATOR ', ') AS subitems
              FROM subitem_unit_type sut
              LEFT JOIN subitem si ON si.unit_type_id = sut.id
              LEFT JOIN item i ON si.item_id = i.id
              GROUP BY sut.id
              ORDER BY sut.id"; // ordenado pelo ID
              
    $result = mysqli_query($link, $query);

    if (!$result) {
        echo "<p>Erro na query SQL: " . mysqli_error($link) . "</p>";
        return;
    }

    if (mysqli_num_rows($result) == 0) {
        echo "<p>Não há tipos de unidades.</p>";
    } else {
        echo "<table class='mytable'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Unidade</th>";
        echo "<th>Subitem</th>";
        echo "<th>Ação</th>";
        echo "</tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['unit_name']}</td>";
            echo "<td>" . (!empty($row['subitems']) ? $row['subitems'] : "Nenhum subitem associado") . "</td>";
            echo "<td>";
            echo "<a href='edicao-de-dados?action=editar_unidade&id={$row['id']}'>[editar]</a> ";
            echo "<a href='edicao-de-dados?action=apagar_unidade&id={$row['id']}'>[apagar]</a>";
            echo "<a href='edicao-de-dados?action=historico_unidade&id={$row['id']}'>[historico]</a>";

            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
        echo "<p>Total de tipos de unidades: " . mysqli_num_rows($result) . "</p>";
    }
}



// Exibir tabela e formulário de inserção se o estado não for especificado
if (!isset($_REQUEST['estado'])) {
    echo "<h3>Gestão de unidades - introdução</h3>";
    mostrarUnidades($link);
    echo "<form method='POST' action=''>";
    echo "<label>Nome:</label>";
    echo "<input type='text' name='unit_name' required>";
    echo "<input type='hidden' name='estado' value='inserir'>";
    echo "<input type='submit' value='Inserir tipo de unidade'>";
    echo "</form>";
    echo "<p>Use o formulário acima para adicionar um novo tipo de unidade.</p>";
}

// Inserção de uma nova unidade
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] === "inserir") {
    $unit_name = mysqli_real_escape_string($link, $_POST['unit_name']);

    if (empty($unit_name)) {
        echo "<p>O campo nome não pode estar vazio.</p>";
    } else {
        $query = "INSERT INTO subitem_unit_type (name) VALUES ('$unit_name')"; // Substituir 'name' pelo nome correto da coluna, se necessário
        $result = mysqli_query($link, $query);

        if ($result) {
            echo "<p>Inseriu o novo tipo de unidade com sucesso.</p>";
            echo "<a href='gestao-de-unidades'>Continuar</a>";
        } else {
            echo "<p>Erro ao inserir dados: " . mysqli_error($link) . "</p>";
        }
    }
}

// Editar uma unidade existente
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] === "editar") {
    $id = mysqli_real_escape_string($link, $_GET['id']);
    $query = "SELECT * FROM subitem_unit_type WHERE id = '$id'";
    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo "<h3>Edição de dados</h3>";
        echo "<form method='POST' action=''>";
        echo "<label>Nome:</label>";
        echo "<input type='text' name='unit_name' value='{$row['name']}' required>"; // Substituir 'name' pelo nome correto da coluna, se necessário
        echo "<input type='hidden' name='id' value='$id'>";
        echo "<input type='hidden' name='estado' value='atualizar'>";
        echo "<input type='submit' value='Atualizar'>";
        echo "</form>";
        echo "<p>Certifique-se de que o nome da unidade está correto antes de atualizar.</p>";
    } else {
        echo "<p>Unidade não encontrada.</p>";
    }
}

// Atualizar unidade após edição
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] === "atualizar") {
    $id = mysqli_real_escape_string($link, $_POST['id']);
    $unit_name = mysqli_real_escape_string($link, $_POST['unit_name']);

    if (empty($unit_name)) {
        echo "<p>O campo nome não pode estar vazio.</p>";
    } else {
        $query = "UPDATE subitem_unit_type SET name = '$unit_name' WHERE id = '$id'"; // Substituir 'name' pelo nome correto da coluna, se necessário
        $result = mysqli_query($link, $query);

        if ($result) {
            echo "<p>Atualização realizada com sucesso.</p>";
            echo "<a href='gestao-de-unidades'>Continuar</a>";
        } else {
            echo "<p>Erro ao atualizar dados: " . mysqli_error($link) . "</p>";
        }
    }
}

// Apagar uma unidade
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] === "apagar") {
    $id = mysqli_real_escape_string($link, $_GET['id']);
    $query = "DELETE FROM subitem_unit_type WHERE id = '$id'";
    $result = mysqli_query($link, $query);

    if ($result) {
        echo "<p>Unidade apagada com sucesso.</p>";
        echo "<a href='gestao-de-unidades'>Continuar</a>";
    } else {
        echo "<p>Erro ao apagar unidade: " . mysqli_error($link) . "</p>";
    }
    echo "<p>Atenção: Esta ação é irreversível.</p>";
}
?>