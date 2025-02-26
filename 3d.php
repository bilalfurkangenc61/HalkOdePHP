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

        <h2>3D Ödeme İşlemi</h2>

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
                        <!-- Taksit seçenekleri burada PHP tarafından dinamik olarak eklenecek -->
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

            $installments = 1; // Varsayılan olarak 1 taksit
            $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/paySmart3D";
            include 'degisken.php';
            $currencyCode = 'TRY';
            $total = $_POST['total'];
            $invoice_id = $_POST['invoice_id'];  // Fatura ID'si artık formdan alınmayacak, otomatik oluşturuldu

            $data = array(
                "cc_holder_name" => $_POST['cc_holder_name'],
                "cc_no" => $_POST['cc_no'],
                "expiry_month" => $_POST['expiry_month'],
                "expiry_year" => $_POST['expiry_year'],
                "cvv" => $_POST['cvv'],
                "currency_code" => $currencyCode,
                "installments_number" => $installments,
                "invoice_id" => $invoice_id,
                "invoice_description" => "ewrwer",
                "total" => $total,
                "merchant_key" => $merchantKey,
                "items" => json_encode(
                    array(
                        array(
                            "name" => "Item1",
                            "price" => $total,
                            "quantity" => $installments,
                            "description" => "item1 description"
                        )
                    )
                ),
                "name" => "John",
                "surname" => "Dao",
                "hash_key" => generateHashKey($total, $installments, $currencyCode, $merchantKey, $invoice_id, $appSecret),
                "return_url" => "http://localhost/PHP/succes.php",
                "cancel_url" => "http://localhost/PHP/fail.php",
                "transaction_type" => $_POST['transaction_type']
            );

            $ch = curl_init($baseUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            echo $response;
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
        ?>
    </div>

    <?php include 'footer.php'; ?>

</body>

</html>

<script>
// Kart numarasına göre taksit seçeneklerini güncelleyen JS fonksiyonu
function updateInstallments() {
    var cardNumber = document.getElementById("cc_no").value.slice(0, 6); // Kart numarasının ilk 6 hanesini alıyoruz
    var installmentsSelect = document.getElementById("installments_number");
    installmentsSelect.innerHTML = ''; // Önceki taksit seçeneklerini temizliyoruz
    
    // Bankaya ait taksit sayısı
    var bankInstallments = {
        '415514': 3, // Halkbank
        '435678': 6, // İş Bankası örnek
        '455698': 4  // Garanti örnek
    };
    
    // Banka taksit seçeneklerine göre formu güncelliyoruz
    var maxInstallments = bankInstallments[cardNumber] || 1; // Varsayılan taksit sayısı 1
    for (var i = 1; i <= maxInstallments; i++) {
        var option = document.createElement("option");
        option.value = i;
        option.text = i + " Taksit";
        installmentsSelect.appendChild(option);
    }
}
</script>
