<!DOCTYPE html>
<html>
<head>
    <title>Descargar documentos SUNAT</title>
</head>
<body>
    <h2>Descargar documentos SUNAT</h2>
    <form action="/descargar" method="POST">
        <label>Empresa:</label>
        <select name="id_empresa" required>
            {% for e in empresas %}
            <option value="{{ e['id_empresa'] }}">{{ e['razon_social'] }}</option>
            {% endfor %}
        </select>
        <br><br>
        <label>Fecha inicio:</label>
        <input type="text" name="fecha_inicio" placeholder="dd/mm/yyyy" required>
        <br><br>
        <label>Fecha fin:</label>
        <input type="text" name="fecha_fin" placeholder="dd/mm/yyyy" required>
        <br><br>
        <button type="submit">Descargar</button>
    </form>
</body>
</html>
