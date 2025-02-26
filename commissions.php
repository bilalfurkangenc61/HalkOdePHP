<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Komisyon Tablosu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>

</head>

<body>

    <?php include 'nav.php'; ?>

    <div class="container">
        <h2 class="mt-4">Mevcut Komisyon</h2>

        <!-- HTML Form for User Input -->
        <form method="post">


            <div class="form-group">
                <label for="currency_code" class="mt-2">Fatura Numarası:</label>
                <input type="text" class="form-control" id="currency_code" name="invoice_id" value="TRY" readonly>
            </div>

            <button type="submit" name="process_payment" class="btns">Mevcut Komisyonu Göster</button>
        </form>
    </div>

    <?php
    if (isset($_POST['process_payment'])) {
        // Temel değişkenleri tanımla
        $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/commissions";
        include 'degisken.php';
        $currency_code = "TRY";  // Formdan alınan para birimi kodu
    
        // Token isteği
        $tokenResponse = getToken($app_id, $appSecret);
        $decodedTokenResponse = json_decode($tokenResponse, true);

        if ($decodedTokenResponse['status_code'] == 100) {
            $token = $decodedTokenResponse['data']['token'];
        } else {
            echo "<p><strong>Hata:</strong> Token alınamadı. Lütfen bilgilerinizi kontrol ediniz.</p>";
            return;
        }


        $data = array(
            "currency_code" => $currency_code,
        );


        echo "<h3>Gönderilen Veriler</h3>";
        echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";


        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            "Authorization: Bearer $token"
        ));
        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $responseData = json_decode($response, true);
        curl_close($ch);


        echo "<h3>Yanıt</h3><pre>" . htmlspecialchars($response) . "</pre>";

        $decodedResponse = json_decode($response, true);

        if ($decodedResponse) {
            echo "<table class='table table-bordered table-striped'><thead class='thead-light'><tr><th>Parametre</th><th>Değer</th></tr></thead><tbody>";
            foreach ($decodedResponse as $key => $value) {
                if (is_array($value)) {
                    $value = '<pre>' . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                }
                echo "<tr><td>$key</td><td>$value</td></tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<div class='alert alert-danger'>Hata: Yanıt alınamadı.</div>";
        }
    }


    function getToken($app_id, $app_secret)
    {
        $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/token";
        $data = array('app_id' => $app_id, 'app_secret' => $app_secret);
        $jsonData = json_encode($data);
        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    ?>

    </div>

    <?php include 'footer.php'; ?>

</body>

</html>