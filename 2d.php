<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Ödeme İşlemi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container mt-4">

        <h2>2D Ödeme İşlemi</h2>

        <!-- Form -->
        <form method="post">
            <?php 
            // Fatura ID'si otomatik olarak oluşturuluyor
            $invoice_id = date('Ymd') . '-' . rand(1000, 9999);
            ?>
            <div class="form-group">
                <label for="invoice_id" class="mt-2">Fatura Numarası:</label>
                <input type="text" class="form-control" id="invoice_id" name="invoice_id" value="<?php echo $invoice_id; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="cc_holder_name">Kart Üzerindeki İsim / Soyisim:</label>
                <input type="text" class="form-control"
                    onkeypress='return ((event.charCode >= 65 && event.charCode <= 90) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode == 32))'
                    id="cc_holder_name" name="cc_holder_name" required>
            </div>
            <div class="form-group">
                <label for="cc_no">Kart Numarası:</label>
                <input type="text" placeholder="********************"
                    onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                    maxlength="16" class="form-control" id="cc_no" name="cc_no" required oninput="updateInstallments()">
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="expiry_month">Son Kullanım Ayı (AA):</label>
                    <input type="text" placeholder="**"
                        onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                        maxlength="2" class="form-control" id="expiry_month" name="expiry_month" required />
                </div>
                <div class="form-group col-md-6">
                    <label for="expiry_year">Son Kullanım Yılı (YY):</label>
                    <input type="text" placeholder="**"
                        onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                        maxlength="2" class="form-control" id="expiry_year" name="expiry_year" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="cvv">CVV:</label>
                    <input type="text" placeholder="***"
                        onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                        maxlength="3" class="form-control" id="cvv" name="cvv" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="total">Tutar:</label>
                    <input type="text"
                        onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                        class="form-control" id="total" name="total" required>
                </div>

                <div class="form-group col-md-6">
                    <label for="installments_number">Taksit Sayısını Seçiniz:</label>
                    <select class="form-control" id="installments_number" name="installments_number" required>
                        <option value="1">Tek Çekim</option>
                        <option value="2">2 Taksit</option>
                        <option value="3">3 Taksit</option>
                        <option value="4">4 Taksit</option>
                        <option value="5">5 Taksit</option>
                        <option value="6">6 Taksit</option>
                        <option value="7">7 Taksit</option>
                        <option value="8">8 Taksit</option>
                        <option value="9">9 Taksit</option>
                        <option value="10">10 Taksit</option>
                        <option value="11">11 Taksit</option>
                        <option value="12">12 Taksit</option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="transaction_type">İşlem Tipi:</label>
                    <select class="form-control" id="transaction_type" name="transaction_type" required>
                        <option value="Auth">Auth</option>
                        <option value="PreAuth">PreAuth</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="invoice_description">Fatura Açıklaması:</label>
                    <input type="text" class="form-control" onkeydown="return /[a-z]/i.test(event.key)"
                        id="invoice_description" name="invoice_description" required>
                </div>
            </div>
            <button type="submit" name="process_payment" class="btns">Ödemeyi Gönder</button>
        </form>

        <?php
        if (isset($_POST['process_payment'])) {
            // Temel değişkenleri tanımla
            $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/paySmart2D";
            $app_id = "006f074e818c52970b4212a4767181f3";
            $appSecret = "ff486ce745834e39ed38908cdd7ceaaf";
            $merchantKey = '$2y$10$tA5Q5IJJv8zpSh0sM.6bueB53HG2VmEKdWnj.HGewu9y5VUk7qvee';
            $total = $_POST['total'];
            $installments_number = $_POST['installments_number'];
            $currencyCode = "TRY";
            $invoice_id = $_POST['invoice_id'];
            $transaction_type = $_POST['transaction_type'];

            // Token isteği
            $tokenResponse = getToken($app_id, $appSecret);
            $decodedTokenResponse = json_decode($tokenResponse, true);

            if ($decodedTokenResponse['status_code'] == 100) {
                $token = $decodedTokenResponse['data']['token'];
            } else {
                echo "<p><strong>Hata:</strong> Token alınamadı. Lütfen bilgilerinizi kontrol ediniz.</p>";
                return;
            }

            // Formdan verileri topla
            $data = array(
                "cc_holder_name" => $_POST['cc_holder_name'],
                "cc_no" => $_POST['cc_no'],
                "expiry_month" => $_POST['expiry_month'],
                "expiry_year" => $_POST['expiry_year'],
                "cvv" => $_POST['cvv'],
                "currency_code" => $currencyCode,
                "installments_number" => $installments_number,
                "invoice_id" => $invoice_id,
                "invoice_description" => "FATURA TEST AÇIKLAMASI",
                "total" => $_POST['total'],
                "merchant_key" => $merchantKey,
                "transaction_type" => $transaction_type,
                "items" => array(
                    array(
                        "name" => "item",
                        "price" => $_POST['total'],
                        "quantity" => 1,
                        "description" => "ürün açıklaması"
                    )
                ),
                "name" => $_POST['cc_holder_name'],
                "surname" => "Dao",
                "payment_status" => 1,
                "hash_key" => generateHashKey($total, $installments_number, $currencyCode, $merchantKey, $invoice_id, $appSecret)
            );

            // Gönderilen verileri tablo olarak göster
            echo "<h3>Gönderilen Veriler</h3>";
            echo "<table class='table table-bordered'><thead><tr><th>Parametre</th><th>Değer</th></tr></thead><tbody>";
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                echo "<tr><td>$key</td><td>$value</td></tr>";
            }
            echo "</tbody></table>";

            // Ödeme isteğini göndermek için CURL
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
            curl_close($ch);

            // Yanıtı tablo olarak göster
            $decodedResponse = json_decode($response, true);
            echo "<h3>API Yanıtı</h3>";
            if ($decodedResponse) {
                echo "<table class='table table-bordered'><thead><tr><th>Parametre</th><th>Değer</th></tr></thead><tbody>";
                foreach ($decodedResponse as $key => $value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    echo "<tr><td>$key</td><td>$value</td></tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p><strong>Hata:</strong> Yanıt alınamadı.</p>";
            }
        }

        function generateHashKey($total, $installment, $currency_code, $merchant_key, $invoice_id, $app_secret)
        {
            $data = $total . '|' . $installment . '|' . $currency_code . '|' . $merchant_key . '|' . $invoice_id;
            $iv = substr(sha1(mt_rand()), 0, 16);
            $password = sha1($app_secret);
            $salt = substr(sha1(mt_rand()), 0, 4);
            $saltWithPassword = hash('sha256', $password . $salt);
            $encrypted = openssl_encrypt("$data", 'aes-256-cbc', "$saltWithPassword", 0, $iv);
            $msg_encrypted_bundle = "$iv:$salt:$encrypted";
            $msg_encrypted_bundle = str_replace('/', '__', $msg_encrypted_bundle);
            return $msg_encrypted_bundle;
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