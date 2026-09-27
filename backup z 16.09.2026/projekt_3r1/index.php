<!doctype html>
<html>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width,initial-scale=1'>
            <title>SKLEP INTERNETOWY</title>
            <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootswatch@5/dist/cosmo/bootstrap.min.css'>
            <link rel='stylesheet' href='css/style.css'>
            <style>
                .floating-btn {
                    min-width: 160px;
                    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
                    transition: transform 0.2s, box-shadow 0.2s;
                }
                .floating-btn:hover {
                    transform: translateY(-4px) scale(1.05);
                    box-shadow: 0 8px 24px rgba(0,0,0,0.20);
                }
                body {
                    background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
                }
                h1 {
                    font-weight: 700;
                    letter-spacing: 2px;
                    color: #2c3e50;
                }
                .lead {
                    color: #34495e;
                    margin-bottom: 2rem;
                }
                .container {
                    background: rgba(255,255,255,0.95);
                    border-radius: 18px;
                    box-shadow: 0 2px 12px rgba(44,62,80,0.08);
                    max-width: 540px;
                    margin-top: 60px;
                }
            </style>
    </head>
    <body>
        <div class='container py-5 text-center'>
            <h1>SKLEP INTERNETOWY 3R1</h1>
            <p class='lead'>Wybierz panel</p>

            <a class='btn btn-primary btn-lg me-2' href='klient/logowanie.php'>Logowanie klienta</a>
            <a class='btn btn-success btn-lg' href='admin/logowanie.php'>Logowanie pracownika</a>

            <div class="position-fixed start-50 translate-middle-x"
                style="bottom: 12px; z-index: 1030; width: 220px;">

                <a class="btn btn-outline-primary btn-sm w-100 mb-2"
                href="info/dokumentacja.html">
                Dokumentacja – instrukcja programu
                </a>

                <a class="btn btn-warning btn-sm w-100 mb-2"
                href="info/rekordy.php">
                Generator rekordów
                </a>

                <a class="btn btn-info btn-sm w-100"
                href="info/oProjekcie.html">
                O projekcie
                </a>
            </div>
        </div>

    </body>
</html>