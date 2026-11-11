<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

/*
 * Esta página altera dados globais da empresa.
 * Apenas administradores podem consultar e guardar estas definições.
 */
require_admin();

$pdo = db();
$row = $pdo->query(
    'SELECT * FROM company_settings ORDER BY id ASC LIMIT 1'
)->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $pdo->exec(
        "INSERT INTO company_settings (company_name, currency)
         VALUES ('GPM', 'AOA')"
    );

    $row = $pdo->query(
        'SELECT * FROM company_settings ORDER BY id ASC LIMIT 1'
    )->fetch(PDO::FETCH_ASSOC);
}

if (!$row) {
    http_response_code(500);
    exit('Não foi possível carregar as definições da empresa.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $companyName = trim((string)($_POST['company_name'] ?? ''));
    $nif = trim((string)($_POST['nif'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $city = trim((string)($_POST['city'] ?? ''));
    $province = trim((string)($_POST['province'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $deliveryFeeRaw = trim((string)($_POST['delivery_fee'] ?? '0'));

    try {
        if ($companyName === '' || mb_strlen($companyName) > 180) {
            throw new RuntimeException('Indica um nome de empresa válido (até 180 caracteres).');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Indica um endereço de email válido.');
        }

        if (
            !is_numeric($deliveryFeeRaw)
            || !is_finite((float)$deliveryFeeRaw)
            || (float)$deliveryFeeRaw < 0
        ) {
            throw new RuntimeException('A taxa de entrega deve ser um valor igual ou superior a zero.');
        }

        $stmt = $pdo->prepare(
            'UPDATE company_settings
             SET company_name = ?, nif = ?, address = ?, city = ?,
                 province = ?, phone = ?, email = ?, delivery_fee = ?
             WHERE id = ?'
        );

        $stmt->execute([
            $companyName,
            $nif !== '' ? $nif : null,
            $address !== '' ? $address : null,
            $city !== '' ? $city : null,
            $province !== '' ? $province : null,
            $phone !== '' ? $phone : null,
            $email !== '' ? $email : null,
            (float)$deliveryFeeRaw,
            (int)$row['id'],
        ]);

        flash('Dados da empresa guardados com sucesso.');
        redirect('company.php');
    } catch (RuntimeException $e) {
        flash($e->getMessage(), 'error');
        redirect('company.php');
    } catch (Throwable $e) {
        error_log('[GPM company settings] ' . $e->getMessage());
        flash('Não foi possível guardar os dados da empresa.', 'error');
        redirect('company.php');
    }
}

layout_start('Dados da empresa');
?>
<section class="panel">
    <h2>Identificação comercial</h2>

    <form method="post">
        <?= csrf_field() ?>

        <div class="form-grid">
            <div class="field">
                <label for="company_name">Nome da empresa</label>
                <input
                    id="company_name"
                    name="company_name"
                    maxlength="180"
                    value="<?= e($row['company_name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="nif">NIF da empresa</label>
                <input id="nif" name="nif" maxlength="50" value="<?= e($row['nif'] ?? '') ?>">
            </div>

            <div class="field">
                <label for="phone">Telefone</label>
                <input id="phone" name="phone" maxlength="40" value="<?= e($row['phone'] ?? '') ?>">
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" maxlength="190" value="<?= e($row['email'] ?? '') ?>">
            </div>

            <div class="field">
                <label for="city">Cidade</label>
                <input id="city" name="city" maxlength="100" value="<?= e($row['city'] ?? '') ?>">
            </div>

            <div class="field">
                <label for="province">Província</label>
                <input id="province" name="province" maxlength="100" value="<?= e($row['province'] ?? '') ?>">
            </div>
        </div>

        <div class="field">
            <label for="address">Endereço</label>
            <textarea id="address" name="address" rows="3"><?= e($row['address'] ?? '') ?></textarea>
        </div>

        <div class="field">
            <label for="delivery_fee">Taxa de entrega padrão (Kz)</label>
            <input
                id="delivery_fee"
                type="number"
                min="0"
                step="0.01"
                name="delivery_fee"
                value="<?= e($row['delivery_fee'] ?? '0') ?>"
            >
        </div>

        <div class="form-actions">
            <button type="submit">Guardar dados</button>
        </div>
    </form>
</section>
<?php layout_end(); ?>
