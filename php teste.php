<?php
// Configurações de conexão com o banco de dados
$host = 'localhost';
$dbname = 'sistema_consultas';
$user = 'root';
$pass = '';

// Conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Funções auxiliares
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function getAge($birthdate) {
    $today = new DateTime();
    $birthdate = new DateTime($birthdate);
    $age = $today->diff($birthdate)->y;
    return $age;
}

// Processamento de ações do CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'create_beneficiario') {
    $nome = sanitizeInput($_POST['nome']);
    $email = sanitizeInput($_POST['email']);
    $dataNascimento = sanitizeInput($_POST['data_nascimento']);
    
    if (getAge($dataNascimento) < 18) {
        echo "Beneficiário deve ter pelo menos 18 anos.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO beneficiarios (nome, email, data_nascimento) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $email, $dataNascimento]);
        echo "Beneficiário cadastrado com sucesso!";
    }
} elseif ($action == 'delete_beneficiario') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM beneficiarios WHERE id = ?");
    $stmt->execute([$id]);
    echo "Beneficiário deletado com sucesso!";
} elseif ($action == 'update_beneficiario') {
    $id = (int)$_POST['id'];
    $nome = sanitizeInput($_POST['nome']);
    $email = sanitizeInput($_POST['email']);
    $dataNascimento = sanitizeInput($_POST['data_nascimento']);
    
    if (getAge($dataNascimento) < 18) {
        echo "Beneficiário deve ter pelo menos 18 anos.";
    } else {
        $stmt = $pdo->prepare("UPDATE beneficiarios SET nome = ?, email = ?, data_nascimento = ? WHERE id = ?");
        $stmt->execute([$nome, $email, $dataNascimento, $id]);
        echo "Beneficiário atualizado com sucesso!";
    }
} elseif ($action == 'create_medico') {
    $nome = sanitizeInput($_POST['nome']);
    $especialidade = sanitizeInput($_POST['especialidade']);
    $hospital_id = (int)$_POST['hospital_id'];
    
    $stmt = $pdo->prepare("INSERT INTO medicos (nome, especialidade, hospital_id) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $especialidade, $hospital_id]);
    echo "Médico cadastrado com sucesso!";
} elseif ($action == 'delete_medico') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM medicos WHERE id = ?");
    $stmt->execute([$id]);
    echo "Médico deletado com sucesso!";
} elseif ($action == 'update_medico') {
    $id = (int)$_POST['id'];
    $nome = sanitizeInput($_POST['nome']);
    $especialidade = sanitizeInput($_POST['especialidade']);
    $hospital_id = (int)$_POST['hospital_id'];
    
    $stmt = $pdo->prepare("UPDATE medicos SET nome = ?, especialidade = ?, hospital_id = ? WHERE id = ?");
    $stmt->execute([$nome, $especialidade, $hospital_id, $id]);
    echo "Médico atualizado com sucesso!";
} elseif ($action == 'create_hospital') {
    $nome = sanitizeInput($_POST['nome']);
    $endereco = sanitizeInput($_POST['endereco']);
    
    $stmt = $pdo->prepare("INSERT INTO hospitais (nome, endereco) VALUES (?, ?)");
    $stmt->execute([$nome, $endereco]);
    echo "Hospital cadastrado com sucesso!";
} elseif ($action == 'delete_hospital') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM hospitais WHERE id = ?");
    $stmt->execute([$id]);
    echo "Hospital deletado com sucesso!";
} elseif ($action == 'update_hospital') {
    $id = (int)$_POST['id'];
    $nome = sanitizeInput($_POST['nome']);
    $endereco = sanitizeInput($_POST['endereco']);
    
    $stmt = $pdo->prepare("UPDATE hospitais SET nome = ?, endereco = ? WHERE id = ?");
    $stmt->execute([$nome, $endereco, $id]);
    echo "Hospital atualizado com sucesso!";
} elseif ($action == 'create_consulta') {
    $data = sanitizeInput($_POST['data']);
    $status = sanitizeInput($_POST['status']);
    $beneficiario_id = (int)$_POST['beneficiario_id'];
    $medico_id = (int)$_POST['medico_id'];
    $hospital_id = (int)$_POST['hospital_id'];
    $anexo = $_FILES['anexo']['name'];
    
    move_uploaded_file($_FILES['anexo']['tmp_name'], "uploads/" . $anexo);
    
    $stmt = $pdo->prepare("INSERT INTO consultas (data, status, beneficiario_id, medico_id, hospital_id, anexo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data, $status, $beneficiario_id, $medico_id, $hospital_id, $anexo]);
    echo "Consulta cadastrada com sucesso!";
} elseif ($action == 'delete_consulta') {
    $id = (int)$_GET['id'];
    
    // Verificar se a consulta já foi concluída
    $stmt = $pdo->prepare("SELECT status FROM consultas WHERE id = ?");
    $stmt->execute([$id]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($consulta['status'] === 'concluida') {
        echo "Não é possível deletar uma consulta concluída!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM consultas WHERE id = ?");
        $stmt->execute([$id]);
        echo "Consulta deletada com sucesso!";
    }
} elseif ($action == 'update_consulta') {
    $id = (int)$_POST['id'];
    $data = sanitizeInput($_POST['data']);
    $status = sanitizeInput($_POST['status']);
    $beneficiario_id = (int)$_POST['beneficiario_id'];
    $medico_id = (int)$_POST['medico_id'];
    $hospital_id = (int)$_POST['hospital_id'];
    
    $stmt = $pdo->prepare("SELECT status FROM consultas WHERE id = ?");
    $stmt->execute([$id]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($consulta['status'] === 'concluida') {
        echo "Não é possível atualizar uma consulta concluída!";
    } else {
        $stmt = $pdo->prepare("UPDATE consultas SET data = ?, status = ?, beneficiario_id = ?, medico_id = ?, hospital_id = ? WHERE id = ?");
        $stmt->execute([$data, $status, $beneficiario_id, $medico_id, $hospital_id, $id]);
        echo "Consulta atualizada com sucesso!";
    }
}

// Fetch all records for display
$beneficiarios = $pdo->query("SELECT * FROM beneficiarios")->fetchAll(PDO::FETCH_ASSOC);
$medicos = $pdo->query("SELECT * FROM medicos")->fetchAll(PDO::FETCH_ASSOC);
$hospitais = $pdo->query("SELECT * FROM hospitais")->fetchAll(PDO::FETCH_ASSOC);
$consultas = $pdo->query("SELECT c.*, b.nome AS beneficiario_nome, m.nome AS medico_nome, h.nome AS hospital_nome FROM consultas c
    JOIN beneficiarios b ON c.beneficiario_id = b.id
    JOIN medicos m ON c.medico_id = m.id
    JOIN hospitais h ON c.hospital_id = h.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Consultas Médicas</title>
</head>
<body>
    <h1>Sistema de Consultas Médicas</h1>
    
    <h2>Beneficiários</h2>
    <form action="?action=create_beneficiario" method="post">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="date" name="data_nascimento" placeholder="Data de Nascimento" required>
        <button type="submit">Adicionar Beneficiário</button>
    </form>
    <ul>
        <?php foreach ($beneficiarios as $beneficiario): ?>
            <li><?= $beneficiario['nome'] ?> (<?= $beneficiario['email'] ?>) 
                <a href="?action=delete_beneficiario&id=<?= $beneficiario['id'] ?>">Excluir</a>
                <form action="?action=update_beneficiario" method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $beneficiario['id'] ?>">
                    <input type="text" name="nome" value="<?= $beneficiario['nome'] ?>" required>
                    <input type="email" name="email" value="<?= $beneficiario['email'] ?>" required>
                    <input type="date" name="data_nascimento" value="<?= $beneficiario['data_nascimento'] ?>" required>
                    <button type="submit">Atualizar</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Médicos</h2>
    <form action="?action=create_medico" method="post">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="text" name="especialidade" placeholder="Especialidade" required>
        <select name="hospital_id" required>
            <option value="">Selecione o Hospital</option>
            <?php foreach ($hospitais as $hospital): ?>
                <option value="<?= $hospital['id'] ?>"><?= $hospital['nome'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Adicionar Médico</button>
    </form>
    <ul>
        <?php foreach ($medicos as $medico): ?>
            <li><?= $medico['nome'] ?> (<?= $medico['especialidade'] ?> - <?= $medico['hospital_id'] ?>) 
                <a href="?action=delete_medico&id=<?= $medico['id'] ?>">Excluir</a>
                <form action="?action=update_medico" method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $medico['id'] ?>">
                    <input type="text" name="nome" value="<?= $medico['nome'] ?>" required>
                    <input type="text" name="especialidade" value="<?= $medico['especialidade'] ?>" required>
                    <select name="hospital_id" required>
                        <option value="">Selecione o Hospital</option>
                        <?php foreach ($hospitais as $hospital): ?>
                            <option value="<?= $hospital['id'] ?>" <?= $medico['hospital_id'] == $hospital['id'] ? 'selected' : '' ?>><?= $hospital['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Atualizar</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Hospitais</h2>
    <form action="?action=create_hospital" method="post">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="text" name="endereco" placeholder="Endereço" required>
        <button type="submit">Adicionar Hospital</button>
    </form>
    <ul>
        <?php foreach ($hospitais as $hospital): ?>
            <li><?= $hospital['nome'] ?> (<?= $hospital['endereco'] ?>) 
                <a href="?action=delete_hospital&id=<?= $hospital['id'] ?>">Excluir</a>
                <form action="?action=update_hospital" method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $hospital['id'] ?>">
                    <input type="text" name="nome" value="<?= $hospital['nome'] ?>" required>
                    <input type="text" name="endereco" value="<?= $hospital['endereco'] ?>" required>
                    <button type="submit">Atualizar</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Consultas</h2>
    <form action="?action=create_consulta" method="post" enctype="multipart/form-data">
        <input type="datetime-local" name="data" placeholder="Data" required>
        <input type="text" name="status" placeholder="Status" required>
        <select name="beneficiario_id" required>
            <option value="">Selecione o Beneficiário</option>
            <?php foreach ($beneficiarios as $beneficiario): ?>
                <option value="<?= $beneficiario['id'] ?>"><?= $beneficiario['nome'] ?></option>
            <?php endforeach; ?>
        </select>
        <select name="medico_id" required>
            <option value="">Selecione o Médico</option>
            <?php foreach ($medicos as $medico): ?>
                <option value="<?= $medico['id'] ?>"><?= $medico['nome'] ?></option>
            <?php endforeach; ?>
        </select>
        <select name="hospital_id" required>
            <option value="">Selecione o Hospital</option>
            <?php foreach ($hospitais as $hospital): ?>
                <option value="<?= $hospital['id'] ?>"><?= $hospital['nome'] ?></option>
            <?php endforeach; ?>
        </select>
        <input type="file" name="anexo" accept="image/*,application/pdf">
        <button type="submit">Adicionar Consulta</button>
    </form>
    <ul>
        <?php foreach ($consultas as $consulta): ?>
            <li><?= $consulta['data'] ?> (<?= $consulta['status'] ?>) - Beneficiário: <?= $consulta['beneficiario_nome'] ?>, Médico: <?= $consulta['medico_nome'] ?>, Hospital: <?= $consulta['hospital_nome'] ?> 
                <?php if ($consulta['status'] !== 'concluida'): ?>
                    <a href="?action=delete_consulta&id=<?= $consulta['id'] ?>">Excluir</a>
                    <form action="?action=update_consulta" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $consulta['id'] ?>">
                        <input type="datetime-local" name="data" value="<?= date('Y-m-d\TH:i', strtotime($consulta['data'])) ?>" required>
                        <input type="text" name="status" value="<?= $consulta['status'] ?>" required>
                        <select name="beneficiario_id" required>
                            <option value="">Selecione o Beneficiário</option>
                            <?php foreach ($beneficiarios as $beneficiario): ?>
                                <option value="<?= $beneficiario['id'] ?>" <?= $consulta['beneficiario_id'] == $beneficiario['id'] ? 'selected' : '' ?>><?= $beneficiario['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="medico_id" required>
                            <option value="">Selecione o Médico</option>
                            <?php foreach ($medicos as $medico): ?>
                                <option value="<?= $medico['id'] ?>" <?= $consulta['medico_id'] == $medico['id'] ? 'selected' : '' ?>><?= $medico['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="hospital_id" required>
                            <option value="">Selecione o Hospital</option>
                            <?php foreach ($hospitais as $hospital): ?>
                                <option value="<?= $hospital['id'] ?>" <?= $consulta['hospital_id'] == $hospital['id'] ? 'selected' : '' ?>><?= $hospital['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Atualizar</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>

