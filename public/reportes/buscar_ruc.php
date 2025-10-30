<!-- buscar_ruc.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Empresa por RUC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">Buscar Empresa por RUC</h2>
    
    <form action="ver_ruc.php" method="GET" class="mb-4">
        <div class="mb-3">
            <label for="ruc" class="form-label">Ingrese RUC:</label>
            <input type="text" class="form-control" id="ruc" name="ruc" required>
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
</div>
</body>
</html>
