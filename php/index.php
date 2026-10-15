<?php

// Адрес n8n Webhook
$n8nUrl = 'https://deals-automation.app.n8n.cloud/webhook/api/test.task/get.deals';

// Отправляем GET-запрос в n8n
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $n8nUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);



// Переменные
$deals = [];
$errors = [];
$pageError = null;

// Проверка
if ($response === false || $curlError) {
    $pageError = 'Не удалось получить данные из n8n: ' . $curlError;
} elseif ($httpCode !== 200) {
    $pageError = 'n8n вернул HTTP-код ' . $httpCode;
} else {

    // Преобразуем JSON от n8n в PHP-массив
    $result = json_decode($response, true);

    if (!is_array($result)) {
        $pageError = 'Получен некорректный JSON от n8n.';
    } else {
        $deals = $result['data'] ?? [];
        $errors = $result['errors'] ?? [];
    }
}

// Функция для безопасного вывода текста в HTML
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Сделки</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <h1 class="mb-4">Список сделок</h1>

    <?php if ($pageError): ?>

        <div class="alert alert-danger">
            <?= e($pageError) ?>
        </div>

    <?php else: ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-striped table-hover align-middle">

                        <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Сумма</th>
                            <th>Ответственный</th>
                            <th>Компания</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php if (empty($deals)): ?>

                            <tr>
                                <td colspan="5" class="text-center">
                                    Сделки не найдены
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($deals as $deal): ?>

                                <tr>

                                    <td>
                                        <?= e($deal['id'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($deal['title'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= isset($deal['amount'])
                                            ? number_format((float)$deal['amount'], 0, '.', ' ')
                                            : ''
                                        ?>
                                    </td>

                                    <td>
                                        <?= e($deal['responsible']['name'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($deal['company']['name'] ?? '') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>
        </div>

        <?php if (!empty($errors)): ?>

            <div class="alert alert-warning mt-4">

                <h5>Некоторые сделки не удалось загрузить:</h5>

                <ul class="mb-0">

                    <?php foreach ($errors as $error): ?>

                        <li>
                            Сделка
                            <?= e($error['id'] ?? '') ?>:
                            <?= e($error['message'] ?? 'Неизвестная ошибка') ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
